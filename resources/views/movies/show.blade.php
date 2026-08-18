@extends('layouts.frontend')

@section('title', $movie->title . ' - movieGo')

@push('scripts')
    <!-- AlpineJS for Tab State -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
@endpush

@push('styles')
    <style>
        .hero-gradient {
            background: linear-gradient(to top, #0f172a 0%, rgba(15, 23, 42, 0.6) 50%, rgba(15, 23, 42, 0.9) 100%);
        }
        .hero-gradient-side {
            background: linear-gradient(to right, #0f172a 0%, rgba(15, 23, 42, 0.8) 50%, rgba(15, 23, 42, 0.2) 100%);
        }
    </style>
@endpush

@section('content')

    <!-- HERO SECTION -->
    <div class="relative min-h-[80vh] flex items-center pt-20">
        <!-- Background Banner -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="{{ $movie->poster_url ? (str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url)) : 'https://images.unsplash.com/photo-1542204165-65bf26472b9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80' }}" 
                 alt="Background" 
                 class="w-full h-full object-cover blur-sm scale-105 opacity-40" />
            <div class="absolute inset-0 hero-gradient"></div>
            <div class="absolute inset-0 hero-gradient-side hidden md:block"></div>
        </div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 py-12">
            <div class="flex flex-col md:flex-row gap-8 lg:gap-16 items-start">
                
                <!-- Poster Left -->
                <div class="w-full md:w-1/3 lg:w-1/4 flex-shrink-0">
                    <div class="rounded-xl overflow-hidden shadow-2xl shadow-primary/20 bg-slate-800 border border-slate-700/50 relative group">
                        <img src="{{ $movie->poster_url ? (str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url)) : 'https://via.placeholder.com/600x900?text=No+Poster' }}" 
                            alt="{{ $movie->title }}" 
                            class="w-full h-auto object-cover aspect-[2/3]"
                            onerror="this.src='https://via.placeholder.com/600x900?text={{ urlencode($movie->title) }}'">
                    </div>
                </div>

                <!-- Info Right -->
                <div class="w-full md:w-2/3 lg:w-3/4 flex flex-col gap-6">
                    <div>
                        <!-- Status Badge -->
                        @if($movie->status === 'NOW_SHOWING')
                            <span class="inline-block py-1 px-3 rounded-md bg-green-500/20 text-green-400 border border-green-500/30 text-xs font-bold uppercase tracking-wider mb-4 animate-pulse">
                                Đang Chiếu
                            </span>
                        @elseif($movie->status === 'COMING_SOON')
                            <span class="inline-block py-1 px-3 rounded-md bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-bold uppercase tracking-wider mb-4 animate-pulse">
                                Sắp Chiếu
                            </span>
                        @endif

                        <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-4 drop-shadow-md">
                            {{ $movie->title }}
                        </h1>

                        <!-- Tags/Metadatas -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-300 font-medium">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-clock text-primary"></i> 
                                {{ $movie->duration ? $movie->getDurationFormatted() : 'Đang cập nhật' }}
                            </span>
                            <span class="w-1.5 h-1.5 bg-slate-600 rounded-full"></span>
                            <span class="flex items-center gap-2 rounded bg-slate-800 border border-slate-700 px-2 py-0.5 text-xs font-bold text-white">
                                {{ $movie->age_rating ?? 'P' }}
                            </span>
                            @if($movie->created_at)
                            <span class="w-1.5 h-1.5 bg-slate-600 rounded-full"></span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-calendar text-primary"></i>
                                Khởi chiếu: {{ $movie->created_at->format('d/m/Y') }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Categories list -->
                    @if($movie->categories && $movie->categories->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($movie->categories as $category)
                                <span class="bg-white/10 hover:bg-white/20 transition backdrop-blur text-white text-xs px-3 py-1.5 rounded-full border border-white/20">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4 mt-4">
                        <a href="{{ route('booking.select-cinema', $movie->id) }}" class="bg-primary hover:bg-red-700 text-white px-8 py-3.5 rounded-full font-bold text-lg transition-all transform hover:-translate-y-1 shadow-lg shadow-red-500/30 flex items-center gap-2">
                            <i class="fas fa-ticket-alt"></i> Đặt Vé Ngay
                        </a>
                        <a href="#trailer-section" class="bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 hover:border-slate-500 px-8 py-3.5 rounded-full font-bold text-lg transition-all flex items-center gap-2">
                            <i class="fas fa-play"></i> Xem Trailer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 border-t border-slate-800 pb-20 pt-10">
        <div class="max-w-7xl mx-auto px-4">
            
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Left Content: Description & Detail -->
                <div class="w-full lg:w-2/3 flex flex-col gap-12">
                    
                    <!-- Section 3: MÔ TẢ PHIM -->
                    <section>
                        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 border-b border-slate-800 pb-4">
                            <span class="w-1.5 h-6 bg-primary rounded"></span> Nội Dung Phim
                        </h2>
                        
                        <div class="text-slate-300 leading-relaxed space-y-4 text-lg">
                            @if($movie->description)
                                {!! nl2br(e($movie->description)) !!}
                            @else
                                <p class="text-slate-500 italic">Đang cập nhật nội dung...</p>
                            @endif
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8 p-6 bg-slate-800/50 rounded-2xl border border-slate-700/50">
                            <div>
                                <h3 class="text-slate-500 text-sm mb-1 uppercase font-bold tracking-wider">Đạo Diễn</h3>
                                <p class="text-white font-medium">{{ $movie->director ?? 'Đang cập nhật' }}</p>
                            </div>
                            <div>
                                <h3 class="text-slate-500 text-sm mb-1 uppercase font-bold tracking-wider">Quốc Gia</h3>
                                <p class="text-white font-medium">{{ $movie->country ?? 'Đang cập nhật' }}</p>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <h3 class="text-slate-500 text-sm mb-1 uppercase font-bold tracking-wider">Diễn Viên</h3>
                                <p class="text-white font-medium">{{ $movie->cast ?? 'Đang cập nhật' }}</p>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <h3 class="text-slate-500 text-sm mb-1 uppercase font-bold tracking-wider">Ngôn Ngữ</h3>
                                <p class="text-white font-medium">{{ $movie->language ?? 'Đang cập nhật' }}</p>
                            </div>
                        </div>
                    </section>

                @push('scripts')
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('movie-review-form');
                    if (!form) return;

                    form.addEventListener('submit', async function (e) {
                        e.preventDefault();
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn) btn.disabled = true;
                        const fd = new FormData(form);

                        try {
                            const res = await fetch(form.action, {
                                method: 'POST',
                                body: fd,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });

                            let data;
                            if (res.ok) {
                                // try parse json, fallback to text
                                try { data = await res.json(); } catch (e) { data = { success: false, message: await res.text() }; }
                            } else if (res.status === 422) {
                                // validation errors
                                data = await res.json().catch(() => ({ success: false, message: 'Validation failed' }));
                            } else {
                                const text = await res.text();
                                console.error('Server error', res.status, text);
                                alert('Lỗi máy chủ khi gửi đánh giá. Vui lòng thử lại sau.');
                                return;
                            }

                            if (data && data.success) {
                                // Insert/replace movie review
                                if (data.review_html) {
                                    const container = document.getElementById('movie-reviews-list');
                                    // remove placeholder if exists
                                    const placeholder = document.getElementById('no-reviews-placeholder');
                                    if (placeholder) placeholder.remove();

                                    // parse returned HTML
                                    const temp = document.createElement('div');
                                    temp.innerHTML = data.review_html.trim();
                                    const newItem = temp.firstElementChild;
                                    if (newItem) {
                                        const userId = newItem.getAttribute('data-user-id');
                                        const existing = container.querySelector('.review-item[data-user-id="'+userId+'"]');
                                        if (existing) existing.remove();
                                        container.prepend(newItem);
                                    }
                                }

                                // Insert cinema review if provided
                                if (data.cinema_review_html && data.cinema_name_slug) {
                                    const slug = data.cinema_name_slug;
                                    const cinemaContainer = document.getElementById('cinema-reviews-' + slug);
                                    if (cinemaContainer) {
                                        const temp2 = document.createElement('div');
                                        temp2.innerHTML = data.cinema_review_html.trim();
                                        const newC = temp2.firstElementChild;
                                        if (newC) {
                                            const uid = newC.getAttribute('data-user-id');
                                            const existC = cinemaContainer.querySelector('.cinema-review-item[data-user-id="'+uid+'"]');
                                            if (existC) existC.remove();
                                            cinemaContainer.prepend(newC);
                                        }
                                    }
                                }

                                // optional success feedback
                                if (data.cinema_feedback_message) {
                                    // show to user so they know why cinema feedback wasn't saved
                                    alert(data.cinema_feedback_message);
                                }
                            } else {
                                console.error('Failed to submit review', data);
                                const msg = (data && (data.message || data.errors)) ? (data.message || JSON.stringify(data.errors)) : 'Không thể gửi đánh giá.';
                                alert(msg);
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Lỗi khi gửi yêu cầu. Vui lòng thử lại.');
                        } finally {
                            if (btn) btn.disabled = false;
                        }
                    });
                });
                </script>
                @endpush

                    <!-- Section 2: TRAILER -->
                    <section id="trailer-section" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 border-b border-slate-800 pb-4">
                            <span class="w-1.5 h-6 bg-primary rounded"></span> Trailer
                        </h2>
                        
                        @php
                            $embedUrl = null;
                            if ($movie->trailer_url) {
                                $parsedUrl = parse_url($movie->trailer_url);
                                if(isset($parsedUrl['host']) && str_contains($parsedUrl['host'], 'youtube.com') && isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryVars);
                                    if(isset($queryVars['v'])) {
                                        $embedUrl = 'https://www.youtube.com/embed/' . $queryVars['v'];
                                    }
                                } elseif(isset($parsedUrl['host']) && str_contains($parsedUrl['host'], 'youtu.be')) {
                                    $path = trim($parsedUrl['path'], '/');
                                    $embedUrl = 'https://www.youtube.com/embed/' . $path;
                                }
                                if(!$embedUrl) {
                                    $embedUrl = $movie->trailer_url;
                                }
                            }
                        @endphp

                        @if($embedUrl)
                            <div class="relative w-full overflow-hidden rounded-2xl pt-[56.25%] shadow-xl shadow-black/50 border border-slate-800">
                                <iframe src="{{ $embedUrl }}" 
                                        class="absolute top-0 left-0 bottom-0 right-0 w-full h-full"
                                        title="YouTube video player" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @else
                            <div class="w-full aspect-video rounded-2xl bg-slate-800 flex flex-col items-center justify-center border border-slate-700/50">
                                <i class="fas fa-video-slash text-slate-500 text-5xl mb-4"></i>
                                <p class="text-slate-400 font-medium">Trailer đang được cập nhật</p>
                            </div>
                        @endif
                    </section>

                </div>

                <!-- Right Content: Showtimes -->
                <div class="w-full lg:w-1/3">
                    <!-- Section 4: LỊCH CHIẾU -->
                    <section id="showtimes-section" class="scroll-mt-24 sticky top-24">
                        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden shadow-2xl shadow-black/20">
                            <div class="p-6 bg-slate-800 border-b border-slate-700">
                                <h2 class="text-xl font-bold flex items-center gap-3">
                                    <i class="fas fa-calendar-check text-primary"></i> Lịch Chiếu
                                </h2>
                            </div>

                            <div class="p-2 max-h-[600px] overflow-y-auto custom-scrollbar" x-data="{ activeCinema: '{{ $showtimesByCinema->keys()->first() }}' }">
                                
                                @if($showtimesByCinema->isEmpty())
                                    <div class="p-8 text-center">
                                        <div class="w-16 h-16 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-calendar-times text-slate-400 text-2xl"></i>
                                        </div>
                                        <p class="text-slate-300 font-medium">Chưa có lịch chiếu cho phim này.</p>
                                        <p class="text-slate-500 text-sm mt-2">Vui lòng quay lại sau.</p>
                                    </div>
                                @else
                                    <!-- Cinema Tabs Horizon Scroll -->
                                    <div class="flex overflow-x-auto gap-2 p-2 mb-2 custom-scrollbar">
                                        @foreach($showtimesByCinema as $cinemaName => $dates)
                                            <button @click="activeCinema = '{{ $cinemaName }}'"
                                                    class="flex-shrink-0 px-5 py-3 rounded-xl font-bold border transition-all flex items-center gap-2"
                                                    :class="activeCinema === '{{ $cinemaName }}' ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-800 border-slate-700 text-slate-400 hover:text-white'">
                                                <i class="fas fa-building" :class="activeCinema === '{{ $cinemaName }}' ? 'text-white' : 'text-slate-500'"></i>
                                                <div class="text-[15px] whitespace-nowrap">{{ $cinemaName }}</div>
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Dates & Showtimes for active Cinema -->
                                    @foreach($showtimesByCinema as $cinemaName => $dates)
                                        <div x-show="activeCinema === '{{ $cinemaName }}'" style="display: none;" class="flex flex-col gap-4 p-2 mt-2">
                                            @foreach($dates as $date => $showtimes)
                                                <div class="bg-slate-900/50 rounded-xl border border-slate-700/50 overflow-hidden">
                                                    <!-- Date Header -->
                                                    <div class="px-5 py-3 bg-slate-800/80 border-b border-slate-700/50 flex flex-col gap-1">
                                                        <span class="font-bold text-white flex items-center gap-2 text-[15px]">
                                                            <i class="fas fa-calendar-alt text-primary"></i> 
                                                            {{ \Carbon\Carbon::parse($date)->locale('vi')->translatedFormat('l') }}, {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Showtimes list -->
                                                    <div class="p-4 bg-slate-800/30">
                                                        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                                                            @foreach($showtimes as $showtime)
                                                                <a href="{{ route('booking.select-seats', ['showtime' => $showtime->id]) }}" 
                                                                   class="flex flex-col items-center justify-center py-3 px-2 bg-slate-700 hover:bg-primary border-2 border-slate-600 hover:border-primary text-slate-200 hover:text-white rounded-xl transition-all shadow-md group transform hover:-translate-y-1">
                                                                    <span class="font-bold text-lg">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}</span>
                                                                    <span class="text-[11px] text-slate-400 group-hover:text-red-100 uppercase tracking-widest mt-1 font-semibold">
                                                                        {{ $showtime->room->name ?? 'Phòng' }}
                                                                    </span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @php
                                                $cReviews = $cinemaReviewsByName[$cinemaName] ?? collect();
                                                $cinemaSlug = \Illuminate\Support\Str::slug($cinemaName);
                                            @endphp
                                            <div id="cinema-reviews-{{ $cinemaSlug }}" class="mt-4 p-4 bg-slate-900/30 rounded-xl border border-slate-700/30">
                                                <h5 class="text-white font-bold mb-3">Phản hồi tại {{ $cinemaName }}</h5>
                                                <div class="space-y-4">
                                                    @foreach($cReviews as $cReview)
                                                        @include('movies.partials.cinema_review_item', ['cReview' => $cReview])
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Section 4.5: ĐÁNH GIÁ & BÌNH LUẬN -->
            <section class="mt-20 border-t border-slate-800 pt-16" id="reviews-section">
                <div class="flex flex-col lg:flex-row gap-12">
                    <div class="w-full lg:w-2/3">
                        <h2 class="text-3xl font-bold text-white flex items-center gap-3 mb-8">
                            <span class="w-1.5 h-8 bg-primary rounded"></span> Đánh Giá & Bình Luận
                        </h2>

                        <!-- Review Form -->
                        <!-- Review Form -->
                        @auth
                            @if($userReview || $canReview)
                                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 mb-8 shadow-xl" x-data="{ isEditing: {{ $userReview ? 'false' : 'true' }}, rating: {{ $userReview ? $userReview->rating : 5 }}, hoverRating: 0, cinemaFeedbackEnabled: false, cinemaRating: 5, cinemaComment: '' }">
                                    <form id="movie-review-form" action="{{ route('movies.reviews.store', $movie->id) }}" method="POST">
                                        @csrf
                                        
                                        <div class="border-b border-slate-700 pb-4 mb-6">
                                            <h3 class="text-lg font-bold text-slate-300 tracking-wider">ĐÁNH GIÁ PHIM</h3>
                                        </div>

                                        @if($userReview)
                                            <!-- Static Movie Review if already reviewed -->
                                            <div class="mb-8" x-show="!isEditing">
                                                <div class="flex items-center gap-2 mb-2 text-yellow-400 text-lg">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $userReview->rating ? '' : 'text-slate-600' }}"></i>
                                                    @endfor
                                                </div>
                                                <p class="text-slate-300">{{ $userReview->comment }}</p>
                                                @if($userReview->status === 'HIDDEN')
                                                    <p class="text-red-400 text-sm mt-4"><i class="fas fa-eye-slash"></i> Đánh giá của bạn đã bị ẩn bởi quản trị viên.</p>
                                                @endif
                                                <div class="mt-4">
                                                    @if($canEditReview)
                                                        <button type="button" @click="isEditing = true" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white font-bold py-2 px-6 rounded-full transition-all text-sm">
                                                            <i class="fas fa-edit"></i> Chỉnh sửa đánh giá
                                                        </button>
                                                    @else
                                                        <span class="text-slate-500 text-sm italic">
                                                            <i class="fas fa-info-circle"></i> Đã quá thời hạn 5 phút chỉnh sửa đánh giá.
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Movie Review Input -->
                                        <div class="mb-8" x-show="isEditing">
                                            <div class="mb-4">
                                                <label class="block text-slate-400 mb-2 font-medium">Chất lượng phim (1-5 sao)</label>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="rating = 1" @mouseenter="hoverRating = 1" @mouseleave="hoverRating = 0" class="text-3xl focus:outline-none transition-colors" :style="(hoverRating >= 1 || (hoverRating === 0 && rating >= 1)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                    <button type="button" @click="rating = 2" @mouseenter="hoverRating = 2" @mouseleave="hoverRating = 0" class="text-3xl focus:outline-none transition-colors" :style="(hoverRating >= 2 || (hoverRating === 0 && rating >= 2)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                    <button type="button" @click="rating = 3" @mouseenter="hoverRating = 3" @mouseleave="hoverRating = 0" class="text-3xl focus:outline-none transition-colors" :style="(hoverRating >= 3 || (hoverRating === 0 && rating >= 3)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                    <button type="button" @click="rating = 4" @mouseenter="hoverRating = 4" @mouseleave="hoverRating = 0" class="text-3xl focus:outline-none transition-colors" :style="(hoverRating >= 4 || (hoverRating === 0 && rating >= 4)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                    <button type="button" @click="rating = 5" @mouseenter="hoverRating = 5" @mouseleave="hoverRating = 0" class="text-3xl focus:outline-none transition-colors" :style="(hoverRating >= 5 || (hoverRating === 0 && rating >= 5)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                    <input type="hidden" name="rating" :value="rating">
                                                </div>
                                                @error('rating')
                                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="mb-4">
                                                <label for="comment" class="block text-slate-400 mb-2 font-medium">Bình luận phim</label>
                                                <textarea id="comment" name="comment" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-white placeholder-slate-500 focus:ring-primary focus:border-primary" placeholder="Nhập cảm nhận của bạn về bộ phim này...">{{ $userReview ? $userReview->comment : '' }}</textarea>
                                                @error('comment')
                                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <!-- Cinema Feedback (optional) -->
                                            <div class="mb-4 border-t border-slate-700 pt-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="text-slate-400 font-medium">Gửi phản hồi cho rạp (tùy chọn)</label>
                                                    <div>
                                                        <label class="inline-flex items-center gap-2 text-sm text-slate-400">
                                                            <input type="checkbox" name="cinema_feedback_enabled" x-model="cinemaFeedbackEnabled" class="form-check-input">
                                                            <span>Kích hoạt</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div x-show="cinemaFeedbackEnabled" x-cloak>
                                                    <div class="mb-3">
                                                        <label class="block text-slate-400 mb-2">Đánh giá rạp (1-5 sao)</label>
                                                        <div class="flex gap-2">
                                                            <button type="button" @click="cinemaRating = 1" class="text-2xl">★</button>
                                                            <button type="button" @click="cinemaRating = 2" class="text-2xl">★</button>
                                                            <button type="button" @click="cinemaRating = 3" class="text-2xl">★</button>
                                                            <button type="button" @click="cinemaRating = 4" class="text-2xl">★</button>
                                                            <button type="button" @click="cinemaRating = 5" class="text-2xl">★</button>
                                                            <input type="hidden" name="cinema_rating" :value="cinemaRating">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-slate-400 mb-2">Phản hồi về rạp</label>
                                                        <textarea name="cinema_comment" rows="3" x-model="cinemaComment" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-white placeholder-slate-500 focus:ring-primary focus:border-primary" placeholder="Chia sẻ trải nghiệm tại rạp (phục vụ, vệ sinh, âm thanh...)..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- COMBO REVIEW -->
                                        @if(isset($purchasedCombos) && $purchasedCombos->count() > 0)
                                            <div class="border-b border-slate-700 pb-4 mb-6">
                                                <h3 class="text-lg font-bold text-slate-300 tracking-wider">ĐÁNH GIÁ COMBO</h3>
                                            </div>

                                            <div class="space-y-6 mb-8">
                                                @foreach($purchasedCombos as $combo)
                                                    @php
                                                        $cReview = $combo->comboReviews->where('user_id', auth()->id())->first();
                                                        $cRating = $cReview ? $cReview->rating : 5;
                                                        $cComment = $cReview ? $cReview->comment : '';
                                                    @endphp
                                                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/50 flex flex-col md:flex-row gap-4">
                                                        <div class="w-24 h-24 shrink-0 rounded-lg overflow-hidden bg-slate-800">
                                                            @if($combo->image)
                                                                <img src="{{ Storage::url($combo->image) }}" alt="{{ $combo->name }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex items-center justify-center text-slate-600"><i class="fas fa-popcorn text-3xl"></i></div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow">
                                                            <h4 class="text-white font-bold mb-2">{{ $combo->name }}</h4>
                                                            <input type="hidden" name="combos[{{ $combo->id }}][booking_id]" value="{{ $combo->booking_id_for_review }}">
                                                            
                                                            <div class="mb-3">
                                                                <div class="flex gap-1" x-data="{ rating: {{ $cRating }}, hoverRating: 0 }">
                                                                    <button type="button" @click="rating = 1" @mouseenter="hoverRating = 1" @mouseleave="hoverRating = 0" class="text-2xl focus:outline-none transition-colors" :style="(hoverRating >= 1 || (hoverRating === 0 && rating >= 1)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                                    <button type="button" @click="rating = 2" @mouseenter="hoverRating = 2" @mouseleave="hoverRating = 0" class="text-2xl focus:outline-none transition-colors" :style="(hoverRating >= 2 || (hoverRating === 0 && rating >= 2)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                                    <button type="button" @click="rating = 3" @mouseenter="hoverRating = 3" @mouseleave="hoverRating = 0" class="text-2xl focus:outline-none transition-colors" :style="(hoverRating >= 3 || (hoverRating === 0 && rating >= 3)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                                    <button type="button" @click="rating = 4" @mouseenter="hoverRating = 4" @mouseleave="hoverRating = 0" class="text-2xl focus:outline-none transition-colors" :style="(hoverRating >= 4 || (hoverRating === 0 && rating >= 4)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                                    <button type="button" @click="rating = 5" @mouseenter="hoverRating = 5" @mouseleave="hoverRating = 0" class="text-2xl focus:outline-none transition-colors" :style="(hoverRating >= 5 || (hoverRating === 0 && rating >= 5)) ? 'color: #ffc107;' : 'color: #475569;'"><i class="fas fa-star"></i></button>
                                                                    <input type="hidden" name="combos[{{ $combo->id }}][rating]" :value="rating">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <textarea name="combos[{{ $combo->id }}][comment]" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-white placeholder-slate-500 text-sm focus:ring-primary focus:border-primary" placeholder="Bình luận Combo...">{{ $cComment }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="text-center pt-4 border-t border-slate-700/50">
                                            <div class="flex justify-center gap-4">
                                                @if($userReview)
                                                    <button type="button" x-show="isEditing" @click="isEditing = false; rating = {{ $userReview->rating }}; document.getElementById('comment').value = `{{ addslashes($userReview->comment) }}`" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-8 rounded-full transition-all text-lg tracking-wider">
                                                        HỦY
                                                    </button>
                                                @endif
                                                <button type="submit" x-show="isEditing" class="bg-primary hover:bg-red-700 text-white font-bold py-3 px-12 rounded-full transition-all text-lg tracking-wider">
                                                    {{ $userReview ? 'CẬP NHẬT ĐÁNH GIÁ' : 'GỬI ĐÁNH GIÁ' }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50 mb-8 text-center">
                                    <i class="fas fa-ticket-alt text-4xl text-slate-500 mb-3"></i>
                                    <p class="text-slate-300">Bạn chỉ có thể đánh giá sau khi đã mua vé hoặc xem phim này.</p>
                                </div>
                            @endif
                        @else
                            <div class="bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50 mb-8 text-center flex flex-col items-center justify-center">
                                <i class="fas fa-lock text-4xl text-slate-500 mb-3"></i>
                                <p class="text-slate-300 mb-4">Vui lòng đăng nhập để gửi đánh giá phim.</p>
                                <a href="{{ route('login') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded-full font-bold transition">Đăng nhập ngay</a>
                            </div>
                        @endauth

                        <!-- Reviews List -->
                        <div class="space-y-6" id="movie-reviews-list">
                            @forelse($reviews as $review)
                                @include('movies.partials.review_item', ['review' => $review])
                            @empty
                                <div class="text-center py-10" id="no-reviews-placeholder">
                                    <i class="far fa-comments text-5xl text-slate-600 mb-4"></i>
                                    <p class="text-slate-400 text-lg">Chưa có đánh giá nào cho phim này.</p>
                                    <p class="text-slate-500 text-sm mt-1">Hãy là người đầu tiên để lại nhận xét!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 5: PHIM LIÊN QUAN -->
            @if(isset($relatedMovies) && $relatedMovies->count() > 0)
                <section class="mt-20 border-t border-slate-800 pt-16">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                            <span class="w-1.5 h-8 bg-primary rounded"></span> Có Thể Bạn Cũng Thích
                        </h2>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($relatedMovies as $rmovie)
                            <x-movie-card :movie="$rmovie" />
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    </div>

@endsection
