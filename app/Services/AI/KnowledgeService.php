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

        // Check for context words
        if (empty($queryWords) || str_contains($normQuery, 'phan moi nhat') || str_contains($normQuery, 'phim do') || str_contains($normQuery, 'phim nay')) {
            $movieQuery = $this->resolveContextWithGemini($movieQuery, $history);
            $normQuery = \Illuminate\Support\Str::slug($movieQuery, ' ');
            $queryWords = array_diff(explode(' ', $normQuery), ['phim']);
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
            
            if (strtolower(trim($movieQuery)) === strtolower(trim($movie->title))) {
                $score += 200;
            } elseif ($normQuery === $normTitle) {
                $score += 150;
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
                            $maxLev = (strlen($mWord) >= 6) ? 2 : 1; // Khắt khe hơn với từ ngắn

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
        if ($topScore >= 80) {
            // If top 2 are very close, it's ambiguous
            if ($matchedMovies->count() > 1 && $matchedMovies[1]->relevance_score >= $topScore * 0.8) {
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
        $system = "Bạn là chuyên gia phân tích ngữ cảnh phim. Dựa vào lịch sử hội thoại: {$historyText}. Người dùng vừa nói: '{$query}'. Hãy cho biết tên phim cụ thể mà họ đang ám chỉ. Chỉ trả về một tên phim duy nhất, không giải thích.";
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
                $nowShowing = Movie::where('status', 'NOW_SHOWING')
                    ->with('categories:name')
                    ->get();
                $comingSoon = Movie::where('status', 'COMING_SOON')
                    ->with('categories:name')
                    ->get();
                $todayShowtimes = Showtime::upcoming()
                    ->whereDate('start_time', today())
                    ->with(['movie:id,title', 'room.cinema:id,name'])
                    ->get();

                if ($nowShowing->isEmpty() && $comingSoon->isEmpty()) {
                    return "Hiện tại không có phim nào đang chiếu hoặc sắp chiếu.";
                }

                $data = [
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
                    'suat_chieu_hom_nay' => $todayShowtimes->map(function ($s) {
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
                $todayShowtimes = Showtime::upcoming()
                    ->whereDate('start_time', today())
                    ->with(['movie:id,title', 'room.cinema:id,name'])
                    ->get();

                $data = [
                    'phim_dang_chieu' => $nowShowing->map(function ($m) {
                        return [
                            'ten_phim' => $m->title,
                            'the_loai' => $m->categories->pluck('name')->implode(', '),
                            'thoi_luong' => $m->duration . ' phút',
                            'trang_thai' => 'Đang chiếu'
                        ];
                    }),
                    'suat_chieu_hom_nay' => $todayShowtimes->map(function ($s) {
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
                $showtimesQuery = Showtime::upcoming()
                    ->with(['movie:id,title', 'room:id,name,cinema_id', 'room.cinema:id,name,address']);
                
                if ($movieResolution && $movieResolution['status'] === 'resolved') {
                    $showtimesQuery->whereIn('movie_id', $movieResolution['movies']->pluck('id'));
                }
                
                $showtimes = $showtimesQuery->take(15)->get();
                
                if ($showtimes->isEmpty()) return "Hiện tại không có suất chiếu nào sắp diễn ra.";
                $data = $showtimes->map(function ($s) {
                    return [
                        'phim' => $s->movie->title ?? '',
                        'rap' => $s->room->cinema->name ?? '',
                        'dia_chi' => $s->room->cinema->address ?? '',
                        'phong' => $s->room->name ?? '',
                        'thoi_gian_bat_dau' => optional($s->start_time)->format('d/m/Y H:i'),
                        'tinh_trang' => $s->status === 'SCHEDULED' ? 'Sắp chiếu / Đang mở bán' : ($s->status === 'ONGOING' ? 'Đang chiếu' : $s->status)
                    ];
                });
                return "Lịch chiếu suất chiếu sắp tới: " . json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

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
                return "Thông tin combo bắp nước: " . json_encode($combos->toArray(), JSON_UNESCAPED_UNICODE);

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
                return "Hướng dẫn đặt vé: Bước 1: Chọn phim và suất chiếu. Bước 2: Chọn ghế ngồi. Bước 3: Chọn combo (nếu có). Bước 4: Nhập mã giảm giá và thanh toán. Vé của bạn sẽ có trong mục Vé Của Tôi.";

            case 'ask_payment':
                return "Các cổng thanh toán hiện tại hệ thống hỗ trợ: VNPay và Thẻ ATM / Visa (Stripe).";

            case 'ask_payment_error':
                return "Quy định thanh toán lỗi: Nếu bị trừ tiền mà chưa có vé, hệ thống sẽ tự động hoàn tiền hoặc bạn có thể liên hệ tổng đài để được hỗ trợ.";

            case 'ask_ticket_price':
                return "Giá vé: Giá vé được quy định theo từng loại suất chiếu và từng loại ghế (Thường, VIP, Đôi). Vui lòng chọn phim cụ thể để biết giá chi tiết.";

            case 'ask_policy':
                return "Chính sách: Vé đã mua không thể hoàn hoặc đổi. Vui lòng kiểm tra kỹ thông tin trước khi thanh toán.";

            case 'ask_website':
                return "Thông tin website: Bạn có thể đặt vé phim, xem lịch chiếu, đánh giá phim, mua combo bắp nước, xem bài viết giới thiệu phim trên trang web của chúng tôi.";

            case 'general':
            default:
                return "Không có dữ liệu đặc biệt nào cần lấy. Hãy trả lời bình thường.";
        }
    }
}