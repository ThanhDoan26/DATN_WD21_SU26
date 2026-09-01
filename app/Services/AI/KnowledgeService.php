<?php   
namespace App\Services\AI;

use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Booking;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Combo;
use App\Models\Post;
use App\Models\Review;

class KnowledgeService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    private function resolveMovie(string $movieQuery, array $history = []): array
    {
        if (empty(trim($movieQuery))) {
            return [
                'status' => 'not_found',
                'movies' => collect(),
                'confidence' => 0
            ];
        }

        // 1. Normalize
        $normQuery = \Illuminate\Support\Str::slug($movieQuery, ' ');
        $queryWords = array_diff(explode(' ', $normQuery), ['phim', 'phan', 'moi', 'nhat', 'do', 'nay', 've', 'la', 'ai']);

        // Check for context words or ordinal choices (ví dụ: 'cái đầu tiên', 'cái thứ 2', 'chọn cái 1', 'phần mới nhất', etc.)
        $isOrdinalOrContext = empty($queryWords) 
            || str_contains($normQuery, 'phan moi nhat') 
            || str_contains($normQuery, 'phim do') 
            || str_contains($normQuery, 'phim nay')
            || str_contains($normQuery, 'dau tien')
            || str_contains($normQuery, 'thu nhat')
            || str_contains($normQuery, 'thu hai')
            || str_contains($normQuery, 'thu 2')
            || str_contains($normQuery, 'so 1')
            || str_contains($normQuery, 'so 2')
            || str_contains($normQuery, 'cai dau')
            || str_contains($normQuery, 'cai sau')
            || str_contains($normQuery, 'chon');

        if ($isOrdinalOrContext) {
            $resolvedName = $this->resolveContextWithGemini($movieQuery, $history);
            if (!empty($resolvedName) && $resolvedName !== $movieQuery) {
                $movieQuery = $resolvedName;
                $normQuery = \Illuminate\Support\Str::slug($movieQuery, ' ');
                $queryWords = array_diff(explode(' ', $normQuery), ['phim']);
            }
        }

        // 2. DB Candidate Search
        $candidates = Movie::where(function ($q) use ($queryWords, $movieQuery) {
            $q->where('title', 'LIKE', "%{$movieQuery}%"); // direct match
            foreach ($queryWords as $word) {
                if (strlen($word) >= 3) {
                    $q->orWhere('title', 'LIKE', "%{$word}%");
                }
            }
        })->get();

        if ($candidates->isEmpty()) {
            // Semantic fallback
            $candidates = $this->fallbackWithGemini($movieQuery);
        }

        // 3. Fuzzy Matching
        $scored = $candidates->map(function ($movie) use ($normQuery, $queryWords, $movieQuery) {
            $score = 0;
            $normTitle = \Illuminate\Support\Str::slug($movie->title, ' ');
            $titleWords = explode(' ', $normTitle);
            
            // Exact match (case insensitive title or normalized slug)
            if (mb_strtolower(trim($movieQuery), 'UTF-8') === mb_strtolower(trim($movie->title), 'UTF-8')) {
                $score += 300;
            } elseif ($normQuery === $normTitle) {
                $score += 250;
            } else {
                if (str_contains($normTitle, $normQuery) || str_contains($normQuery, $normTitle)) {
                    $score += 80;
                }
                
                $matched = array_intersect($titleWords, $queryWords);
                $score += count($matched) * 20;

                $compactTitle = str_replace(' ', '', $normTitle);
                foreach ($queryWords as $mWord) {
                    if (strlen($mWord) >= 3 && str_contains($compactTitle, $mWord)) {
                        $score += 15;
                    }
                }

                if (count($matched) == 0) {
                    foreach ($titleWords as $tWord) {
                        if (strlen($tWord) <= 3) continue;
                        foreach ($queryWords as $mWord) {
                            if (strlen($mWord) <= 3) continue;
                            
                            $lev = levenshtein($tWord, $mWord);
                            $maxLev = (strlen($mWord) >= 6) ? 2 : 1;

                            if ($lev <= $maxLev) {
                                $score += 15;
                            }
                        }
                    }
                }
            }
            $movie->relevance_score = $score;
            return $movie;
        });

        $matchedMovies = $scored->filter(function ($m) {
            return $m->relevance_score >= 15;
        })->sortByDesc('relevance_score')->values();

        if ($matchedMovies->isEmpty()) {
            return [
                'status' => 'not_found',
                'movies' => $this->findSimilarMovies($movieQuery),
                'confidence' => 0
            ];
        }

        $topScore = $matchedMovies->first()->relevance_score;
        
        // 4. Resolution Status
        // Nếu có EXACT MATCH (score >= 200) thì chắc chắn ĐÃ XÁC ĐỊNH (resolved), không coi là ambiguous
        if ($topScore >= 200) {
            return [
                'status' => 'resolved',
                'movies' => $matchedMovies->take(1),
                'confidence' => $topScore
            ];
        }

        if ($topScore >= 80) {
            // Nếu không có exact match nhưng top 2 điểm sát nhau thì là ambiguous
            if ($matchedMovies->count() > 1 && $matchedMovies[1]->relevance_score >= $topScore * 0.9) {
                return [
                    'status' => 'ambiguous',
                    'movies' => $matchedMovies->take(3),
                    'confidence' => $topScore
                ];
            }
            return [
                'status' => 'resolved',
                'movies' => $matchedMovies->take(1),
                'confidence' => $topScore
            ];
        } elseif ($topScore >= 50) {
            return [
                'status' => 'ambiguous',
                'movies' => $matchedMovies->take(3),
                'confidence' => $topScore
            ];
        } else {
             return [
                'status' => 'not_found',
                'movies' => $this->findSimilarMovies($movieQuery),
                'confidence' => $topScore
            ];
        }
    }

    private function resolveContextWithGemini(string $query, array $history): string
    {
        if (empty($history)) return $query;
        $historyText = json_encode($history, JSON_UNESCAPED_UNICODE);
        $system = "Bạn là chuyên gia phân tích ngữ cảnh phim. Dựa vào lịch sử hội thoại: {$historyText}. Người dùng vừa nói: '{$query}'.
Nhiệm vụ:
1. Nếu người dùng chọn một phim theo thứ tự (ví dụ: 'cái đầu tiên', 'phim thứ nhất', 'cái số 1', 'lấy cái sau', 'chọn phim thứ 2'...), hãy đọc danh sách các phim trong tin nhắn gần nhất của trợ lý và lấy đúng tên bộ phim tương ứng.
2. Nếu người dùng ngụ ý một phim (ví dụ: 'phần mới nhất', 'phim đó', 'phim này'), hãy tìm tên phim tương ứng trong lịch sử.
Chỉ trả về MỘT TÊN PHIM DUY NHẤT, không giải thích gì thêm.";
        $response = $this->geminiService->generate($query, $system);
        return trim($response);
    }

    private function fallbackWithGemini(string $query)
    {
        // Try asking Gemini what the official movie name is
        $system = "Bạn là trợ lý hệ thống rạp chiếu phim. Người dùng nhập tên phim là: '{$query}'. Tên chính thức hoặc phổ biến nhất của bộ phim này (tiếng Anh hoặc Việt) là gì? Chỉ trả về 1 tên phim duy nhất, không giải thích.";
        $semanticName = trim($this->geminiService->generate($query, $system));
        
        if (empty($semanticName) || $semanticName === $query) return collect();

        $normSemantic = \Illuminate\Support\Str::slug($semanticName, ' ');
        $words = explode(' ', $normSemantic);

        return Movie::where(function ($q) use ($words, $semanticName) {
            $q->where('title', 'LIKE', "%{$semanticName}%");
            foreach ($words as $w) {
                if (strlen($w) >= 3) {
                    $q->orWhere('title', 'LIKE', "%{$w}%");
                }
            }
        })->get();
    }

    private function findSimilarMovies(string $query)
    {
        $words = explode(' ', \Illuminate\Support\Str::slug($query, ' '));
        $similars = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
            ->where(function($q) use ($words) {
                foreach ($words as $w) {
                    if (strlen($w) >= 4) {
                        $q->orWhere('description', 'LIKE', "%{$w}%")
                          ->orWhere('director', 'LIKE', "%{$w}%");
                    }
                }
            })->take(3)->get();
        
        if ($similars->isEmpty()) {
            return Movie::where('status', 'NOW_SHOWING')->inRandomOrder()->take(3)->get();
        }
        return $similars;
    }

    public function getContext(string $intent, ?User $user, string $message = '', array $history = [], ?string $movieQuery = null): string
    {
        $movieResolution = null;
        $movieIntents = [
            'ask_movie_information', 'ask_movie_status', 'ask_movie_compare', 
            'ask_movie_recommendation', 'ask_movie_review', 'ask_showtimes'
        ];

        if (in_array($intent, $movieIntents) && $movieQuery) {
            $movieResolution = $this->resolveMovie($movieQuery, $history);
            
            // Nếu không tìm thấy hoặc nhập nhằng và người dùng đang hỏi riêng về một phim
            if ($movieResolution['status'] === 'ambiguous') {
                $titles = $movieResolution['movies']->pluck('title')->implode(', ');
                return "Hệ thống tìm thấy nhiều phim khớp với '{$movieQuery}': {$titles}. CHỈ THỊ BẮT BUỘC: Hãy yêu cầu người dùng xác nhận chính xác tên phim họ muốn xem.";
            } elseif ($movieResolution['status'] === 'not_found') {
                $titles = $movieResolution['movies']->pluck('title')->implode(', ');
                return "Hệ thống KHÔNG TÌM THẤY bộ phim nào khớp với '{$movieQuery}'. CHỈ THỊ BẮT BUỘC: Hãy thông báo cho người dùng là rạp không có phim này, và gợi ý họ các phim có liên quan đang chiếu sau: {$titles}. Tuyệt đối không tự bịa thông tin phim.";
            }
        }

        switch ($intent) {
            case 'ask_movies':
                Showtime::syncAllStatuses();
                $nowShowing = Movie::where('status', 'NOW_SHOWING')
                    ->with('categories:name')
                    ->get();
                $comingSoon = Movie::where('status', 'COMING_SOON')
                    ->with('categories:name')
                    ->get();
                $todayUpcomingShowtimes = Showtime::where('status', Showtime::STATUS_SCHEDULED)
                    ->whereDate('start_time', today())
                    ->where('start_time', '>', now())
                    ->with(['movie:id,title', 'room.cinema:id,name'])
                    ->orderBy('start_time', 'asc')
                    ->get();

                if ($nowShowing->isEmpty() && $comingSoon->isEmpty()) {
                    return "Hiện tại không có phim nào đang chiếu hoặc sắp chiếu.";
                }

                $data = [
                    'thoi_gian_hien_tai' => now()->format('d/m/Y H:i'),
                    'phim_dang_chieu' => $nowShowing->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'dao_dien' => $m->director,
                            'the_loai' => $m->categories->pluck('name')->implode(', '),
                            'thoi_luong' => $m->duration . ' phút',
                            'gioi_han_tuoi' => $m->age_rating,
                            'trang_thai' => 'Đang chiếu'
                        ];
                    }),
                    'suat_chieu_con_lai_hom_nay' => $todayUpcomingShowtimes->map(function ($s) {
                        return [
                            'phim' => $s->movie->title ?? '',
                            'rap' => $s->room->cinema->name ?? '',
                            'gio_chieu' => optional($s->start_time)->format('H:i')
                        ];
                    }),
                    'phim_sap_chieu' => $comingSoon->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'dao_dien' => $m->director,
                            'the_loai' => $m->categories->pluck('name')->implode(', '),
                            'thoi_luong' => $m->duration . ' phút',
                            'gioi_han_tuoi' => $m->age_rating,
                            'trang_thai' => 'Sắp chiếu'
                        ];
                    })
                ];
                return "Danh sách phim và lịch chiếu: " . json_encode($data, JSON_UNESCAPED_UNICODE);

            case 'ask_movie_status':
                Showtime::syncAllStatuses();
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $matchedMovies = $movieResolution['movies'];
                    $data = $matchedMovies->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'trang_thai' => $m->status === 'NOW_SHOWING' ? 'Đang chiếu' : ($m->status === 'COMING_SOON' ? 'Sắp chiếu' : $m->status),
                            'thoi_luong' => $m->duration . ' phút',
                            'gioi_han_tuoi' => $m->age_rating,
                            'mo_ta' => $m->description
                        ];
                    });
                    return "Trạng thái phim: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
                }

                $nowShowing = Movie::where('status', 'NOW_SHOWING')->with('categories:name')->get();
                $comingSoon = Movie::where('status', 'COMING_SOON')->with('categories:name')->get();
                $todayUpcomingShowtimes = Showtime::where('status', Showtime::STATUS_SCHEDULED)
                    ->whereDate('start_time', today())
                    ->where('start_time', '>', now())
                    ->with(['movie:id,title', 'room.cinema:id,name'])
                    ->orderBy('start_time', 'asc')
                    ->get();

                $data = [
                    'thoi_gian_hien_tai' => now()->format('d/m/Y H:i'),
                    'phim_dang_chieu' => $nowShowing->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'the_loai' => $m->categories->pluck('name')->implode(', '),
                            'thoi_luong' => $m->duration . ' phút',
                            'trang_thai' => 'Đang chiếu'
                        ];
                    }),
                    'suat_chieu_con_lai_hom_nay' => $todayUpcomingShowtimes->map(function ($s) {
                        return [
                            'phim' => $s->movie->title ?? '',
                            'rap' => $s->room->cinema->name ?? '',
                            'gio_chieu' => optional($s->start_time)->format('H:i')
                        ];
                    }),
                    'phim_sap_chieu' => $comingSoon->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'the_loai' => $m->categories->pluck('name')->implode(', '),
                            'thoi_luong' => $m->duration . ' phút',
                            'trang_thai' => 'Sắp chiếu'
                        ];
                    })
                ];
                return "Thông tin trạng thái các phim và suất chiếu hôm nay: " . json_encode($data, JSON_UNESCAPED_UNICODE);

            case 'ask_movie_information':
            case 'ask_movie_compare':
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $matchedMovies = $movieResolution['movies'];
                } else {
                    $matchedMovies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])->take(6)->get();
                }
                
                $data = $matchedMovies->map(function ($m) {
                    return [
                        'ten_phim' => $m->title,
                        'dao_dien' => $m->director,
                        'dien_vien' => $m->cast,
                        'thoi_luong' => $m->duration . ' phút',
                        'gioi_han_tuoi' => $m->age_rating,
                        'mo_ta' => $m->description,
                        'trang_thai' => $m->status === 'NOW_SHOWING' ? 'Đang chiếu' : ($m->status === 'COMING_SOON' ? 'Sắp chiếu' : $m->status)
                    ];
                });
                return "Thông tin chi tiết các phim: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_review':
                $query = Movie::where('status', 'NOW_SHOWING')->withAvg('reviews', 'rating')->withCount('reviews');
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $query->whereIn('id', $movieResolution['movies']->pluck('id'));
                }
                $movies = $query->get(['id', 'title']);
                return "Đánh giá các phim: " . json_encode($movies->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_recommendation':
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $movies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
                        ->where('id', '!=', $movieResolution['movies']->first()->id)
                        ->take(5)->get(['id', 'title', 'age_rating', 'status']);
                } else {
                    $movies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
                        ->take(5)->get(['id', 'title', 'age_rating', 'status']);
                }
                
                $data = $movies->map(function ($m) {
                    return [
                        'ten_phim' => $m->title,
                        'gioi_han_tuoi' => $m->age_rating,
                        'trang_thai' => $m->status === 'NOW_SHOWING' ? 'Đang chiếu' : ($m->status === 'COMING_SOON' ? 'Sắp chiếu' : $m->status)
                    ];
                });
                return "Gợi ý phim: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_movie_post':
                $posts = Post::where('status', 'published')->latest()->take(3)->get(['title', 'summary']);
                return "Các bài viết/review mới nhất: " . json_encode($posts->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_cinemas':
                $cinemas = Cinema::select('name', 'address', 'phone', 'city')->get();
                return "Danh sách hệ thống rạp phim: " . json_encode($cinemas->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_showtimes':
                Showtime::syncAllStatuses();

                $movieIdFilter = ($movieResolution && $movieResolution['status'] === 'resolved')
                    ? $movieResolution['movies']->pluck('id')->toArray()
                    : [];

                // 1. Suất chiếu sắp tới (start_time > now)
                $upcomingQuery = Showtime::where('status', Showtime::STATUS_SCHEDULED)
                    ->where('start_time', '>', now())
                    ->with(['movie:id,title', 'room:id,name,cinema_id', 'room.cinema:id,name,address'])
                    ->orderBy('start_time', 'asc');

                if (!empty($movieIdFilter)) {
                    $upcomingQuery->whereIn('movie_id', $movieIdFilter);
                }

                $upcomingShowtimes = $upcomingQuery->take(15)->get();

                // 2. Suất chiếu hôm nay đã qua giờ / đã kết thúc (start_time <= now)
                $pastQuery = Showtime::whereDate('start_time', today())
                    ->where('start_time', '<=', now())
                    ->with(['movie:id,title', 'room.cinema:id,name'])
                    ->orderBy('start_time', 'desc');

                if (!empty($movieIdFilter)) {
                    $pastQuery->whereIn('movie_id', $movieIdFilter);
                }

                $pastShowtimesToday = $pastQuery->take(10)->get();

                $movieName = (!empty($movieIdFilter) && $movieResolution['movies']->isNotEmpty()) 
                    ? $movieResolution['movies']->first()->title 
                    : null;

                if ($upcomingShowtimes->isEmpty()) {
                    if ($pastShowtimesToday->isNotEmpty()) {
                        $pastList = $pastShowtimesToday->map(function ($s) {
                            return ($s->movie->title ?? '') . ' tại ' . ($s->room->cinema->name ?? '') . ' lúc ' . optional($s->start_time)->format('H:i d/m/Y');
                        })->implode(', ');

                        return "CẢNH BÁO THỜI GIAN VÀ SUẤT CHIẾU: Hiện tại là " . now()->format('H:i d/m/Y') . ". " .
                            ($movieName ? "Suất chiếu hôm nay của phim '{$movieName}' ({$pastList}) ĐÃ QUA GIỜ CHIẾU / ĐÃ KẾT THÚC." : "Các suất chiếu hôm nay ({$pastList}) ĐÃ QUA GIỜ CHIẾU / ĐÃ KẾT THÚC.") .
                            " Hiện tại không còn suất chiếu nào sắp diễn ra trong ngày hôm nay. CHỈ THỊ BẮT BUỘC: Hãy thông báo rõ ràng cho khách rằng suất chiếu hôm nay đã qua giờ/đã kết thúc và không thể đặt vé cho suất đó nữa. Hãy gợi ý khách xem lịch chiếu của ngày tiếp theo hoặc chọn phim khác.";
                    }

                    return ($movieName ? "Phim '{$movieName}'" : "Hệ thống") . " hiện tại không có suất chiếu sắp tới nào.";
                }

                $data = [
                    'thoi_gian_hien_tai' => now()->format('d/m/Y H:i'),
                    'suat_chieu_sap_toi_co_the_dat_ve' => $upcomingShowtimes->map(function ($s) {
                        return [
                            'phim' => $s->movie->title ?? '',
                            'rap' => $s->room->cinema->name ?? '',
                            'dia_chi' => $s->room->cinema->address ?? '',
                            'phong' => $s->room->name ?? '',
                            'thoi_gian_bat_dau' => optional($s->start_time)->format('d/m/Y H:i'),
                            'tinh_trang' => 'Sắp chiếu / Đang mở bán'
                        ];
                    })
                ];

                if ($pastShowtimesToday->isNotEmpty()) {
                    $data['suat_chieu_da_qua_gio_hom_nay'] = $pastShowtimesToday->map(function ($s) {
                        return [
                            'phim' => $s->movie->title ?? '',
                            'rap' => $s->room->cinema->name ?? '',
                            'thoi_gian' => optional($s->start_time)->format('d/m/Y H:i'),
                            'tinh_trang' => 'ĐÃ QUA GIỜ CHIẾU / ĐÃ KẾT THÚC (KHÔNG THỂ ĐẶT VÉ)'
                        ];
                    });
                }

                return "Lịch chiếu suất chiếu: " . json_encode($data, JSON_UNESCAPED_UNICODE);

            case 'ask_my_tickets':
            case 'ask_booking_status':
                if (!$user) return "Bạn chưa đăng nhập. Vui lòng đăng nhập để xem thông tin vé.";
                $bookings = Booking::where('user_id', $user->id)
                    ->with(['showtime', 'showtime.movie:id,title', 'showtime.room:id,name,cinema_id', 'showtime.room.cinema:id,name'])
                    ->latest()
                    ->take(5)
                    ->get();
                if ($bookings->isEmpty()) return "Bạn chưa đặt bất kỳ vé nào trong hệ thống.";
                $data = $bookings->map(function ($b) {
                    $seatsInfo = collect($b->getSeatsInfo())->pluck('code')->implode(', ');
                    return [
                        'ma_ve' => $b->booking_code,
                        'phim' => $b->showtime->movie->title ?? '',
                        'rap' => $b->showtime->room->cinema->name ?? '',
                        'phong' => $b->showtime->room->name ?? '',
                        'ghe_ngoi' => $seatsInfo ?: 'Chưa chọn ghế',
                        'thoi_gian_chieu' => optional($b->showtime->start_time)->format('d/m/Y H:i'),
                        'trang_thai_ve' => $b->status,
                        'tong_tien' => $b->total_price,
                        'thoi_gian_dat_ve' => optional($b->booking_time)->format('d/m/Y H:i')
                    ];
                });
                return "Thông tin 5 vé gần nhất của bạn: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_coupon':
                $coupons = Coupon::where('status', 'ACTIVE')->get(['code', 'value', 'type', 'min_order_value', 'max_discount_amount', 'end_date']);
                return "Mã giảm giá hiện có: " . json_encode($coupons->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_combo':
                $combos = Combo::where('status', 'ACTIVE')->get(['name', 'price', 'description']);
                $data = [
                    'quy_dinh_do_an_ngoai' => 'Rạp KHÔNG CHO PHÉP mang đồ ăn, thức uống từ bên ngoài vào rạp chiếu phim để đảm bảo vệ sinh chung và trải nghiệm của mọi khán giả. Rạp đã có sẵn các combo bắp nước đa dạng, thơm ngon hấp dẫn (bắp rang bơ, nước ngọt, snack...) xin mời quý khách có thể mua và thưởng thức trực tiếp tại quầy hoặc đặt cùng vé trên hệ thống.',
                    'danh_sach_combo' => $combos->toArray()
                ];
                return "Thông tin combo bắp nước và quy định đồ ăn: " . json_encode($data, JSON_UNESCAPED_UNICODE);

            case 'ask_user_profile':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $profile = [
                    'ten' => $user->name,
                    'email' => $user->email,
                    'so_dien_thoai' => $user->phone,
                    'diem_tich_luy' => $user->loyalty_points
                ];
                return "Thông tin người dùng: " . json_encode($profile, JSON_UNESCAPED_UNICODE);

            case 'ask_booking_history':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $bookings = Booking::where('user_id', $user->id)
                    ->with('showtime.movie:id,title')
                    ->latest()
                    ->take(10)
                    ->get(['id', 'booking_code', 'showtime_id', 'total_price', 'status', 'booking_time']);
                return "Lịch sử 10 lần mua vé gần nhất: " . json_encode($bookings->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_review_history':
                if (!$user) return "Người dùng chưa đăng nhập.";
                $reviews = Review::where('user_id', $user->id)
                    ->with('movie:id,title')
                    ->latest()
                    ->take(5)
                    ->get(['movie_id', 'rating', 'comment', 'created_at']);
                return "Lịch sử đánh giá phim của bạn: " . json_encode($reviews->toArray(), JSON_UNESCAPED_UNICODE);

            case 'ask_conversation_summary':
                return "Lịch sử cuộc trò chuyện từ trước đến giờ: " . json_encode($history, JSON_UNESCAPED_UNICODE);

            case 'ask_booking_guide':
                $movieInfo = "";
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $m = $movieResolution['movies']->first();
                    $movieInfo = " cho phim " . ($m->title ?? '');
                }
                return "Quy trình khách hàng tự đặt vé trực tuyến trên website MovieGo{$movieInfo}:
LƯU Ý: Trợ lý AI KHÔNG trực tiếp đặt vé, giữ chỗ ghế hay thanh toán hộ trong khung chat. Để đảm bảo an toàn bảo mật và tự tay chọn vị trí ghế đẹp ưng ý theo sơ đồ trực quan, quý khách tự thao tác đặt vé trên website theo 4 bước:
- Bước 1: Chọn phim và suất chiếu bạn muốn xem trên website.
- Bước 2: Chọn rạp và chọn vị trí ghế ngồi trực tiếp trên sơ đồ phòng chiếu.
- Bước 3: Chọn thêm combo bắp nước và áp dụng mã giảm giá (nếu có).
- Bước 4: Thanh toán trực tuyến an toàn qua VNPay hoặc Thẻ ATM / Visa. Vé điện tử sẽ được hiển thị ngay sau khi thanh toán thành công và lưu trong mục 'Vé Của Tôi'.";

            case 'ask_payment':
                return "Các cổng thanh toán hỗ trợ trên website MovieGo: VNPay và Thẻ ATM / Visa quốc tế (Stripe). Quý khách thực hiện thanh toán trực tiếp tại bước 4 của quy trình đặt vé trên website. Trợ lý AI không nhận tiền hay cung cấp mã QR chuyển khoản riêng trong khung chat.";

            case 'ask_payment_error':
                return "Quy định thanh toán lỗi: Nếu bị trừ tiền mà chưa có vé, hệ thống sẽ tự động hoàn tiền hoặc bạn có thể liên hệ tổng đài để được hỗ trợ.";

            case 'ask_ticket_price':
                return "Giá vé: Giá vé được quy định theo từng loại suất chiếu và từng loại ghế (Thường, VIP, Đôi). Vui lòng chọn phim cụ thể để biết giá chi tiết.";

            case 'ask_policy':
                return "Chính sách và quy định của rạp chiếu phim MovieGo:
1. Quy định về Độ tuổi xem phim và Kiểm tra Căn cước công dân (CCCD):
- Phân loại độ tuổi phim:
  + P: Phim dành cho mọi lứa tuổi khán giả.
  + K: Khán giả dưới 13 tuổi có thể xem khi có người giám hộ/người lớn đi cùng.
  + T13: Phim dành cho khán giả từ đủ 13 tuổi trở lên.
  + T16: Phim dành cho khán giả từ đủ 16 tuổi trở lên.
  + T18: Phim dành cho khán giả từ đủ 18 tuổi trở lên (ví dụ: học sinh cấp 3 chưa đủ 18 tuổi sẽ không được xem).
- Kiểm tra CCCD / Giấy tờ tùy thân: Khi vào xem các phim có giới hạn độ tuổi (như T13, T16, T18), khách hàng vui lòng mang theo Căn cước công dân (CCCD), CMND hoặc giấy tờ tùy thân có ảnh/ngày sinh để nhân viên soát vé kiểm tra độ tuổi.
- Xử lý đối với khách hàng chưa đủ tuổi: Nếu khách hàng dưới độ tuổi cho phép (hoặc không xuất trình được giấy tờ hợp lệ chứng minh đủ tuổi), nhân viên rạp sẽ kiểm tra căn cước và rạp sẽ kiên quyết hạn chế/từ chối không cho phép người dưới độ tuổi quy định vào xem những phim đó theo đúng quy định pháp luật.

2. Quy định về Đồ ăn, Thức uống từ bên ngoài:
- Rạp KHÔNG CHO PHÉP mang đồ ăn, thức uống từ bên ngoài vào rạp chiếu phim nhằm đảm bảo vệ sinh chung, an toàn thực phẩm và giữ gìn không gian xem phim cho tất cả mọi người.
- Rạp đã có sẵn quầy Bắp Nước với nhiều gói Combo phong phú, thơm ngon và hấp dẫn (bắp rang bơ các vị phô mai, caramel, nước ngọt, snack...), xin mời quý khách có thể mua trực tiếp tại quầy hoặc đặt mua kèm vé trực tuyến trên website để thưởng thức.

3. Chính sách Vé và Hoàn/Đổi vé:
- Vé đã mua và thanh toán thành công KHÔNG hỗ trợ hoàn tiền, hủy hoặc đổi sang suất chiếu khác. Quý khách vui lòng kiểm tra kỹ thông tin suất chiếu và ghế ngồi trước khi thanh toán.";

            case 'ask_website':
                return "Thông tin website: Bạn có thể đặt vé phim, xem lịch chiếu, đánh giá phim, mua combo bắp nước, xem bài viết giới thiệu phim trên trang web của chúng tôi.";

            case 'general':
            default:
                return "Không có dữ liệu đặc biệt nào cần lấy. Hãy trả lời bình thường và lịch sự.
Lưu ý các quy định cơ bản của rạp nếu người dùng đề cập:
- Độ tuổi & CCCD: Các phim giới hạn tuổi (T13, T16, T18) yêu cầu khách hàng đủ tuổi. Nhân viên rạp sẽ check Căn cước công dân/giấy tờ tùy thân và rạp sẽ hạn chế/từ chối người dưới độ tuổi cho phép xem những phim đó.
- Đồ ăn ngoài & Combo: Rạp không cho phép mang đồ ăn, thức uống từ bên ngoài vào. Rạp đã có sẵn các combo bắp nước đa dạng, thơm ngon để quý khách mua và thưởng thức.";
        }
    }
}