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
                        @auth
                            @if($userReview)
                                <div class="bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50 mb-8">
                                    <h3 class="text-xl font-bold mb-4">Đánh giá của bạn</h3>
                                    <div class="flex items-center gap-2 mb-2 text-yellow-400 text-lg">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $userReview->rating ? '' : 'text-slate-600' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-slate-300">{{ $userReview->comment }}</p>
                                    @if($userReview->status === 'HIDDEN')
                                        <p class="text-red-400 text-sm mt-4"><i class="fas fa-eye-slash"></i> Đánh giá của bạn đã bị ẩn bởi quản trị viên.</p>
                                    @endif
                                </div>
                            @elseif($canReview)
                                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 mb-8 shadow-xl">
                                    <h3 class="text-xl font-bold mb-4">Gửi đánh giá của bạn</h3>
                                    <form action="{{ route('movies.reviews.store', $movie->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-slate-400 mb-2 font-medium">Chất lượng phim (1-5 sao)</label>
                                            <div class="flex gap-2" x-data="{ rating: 5, hoverRating: 0 }">
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
                                            <label for="comment" class="block text-slate-400 mb-2 font-medium">Bình luận của bạn</label>
                                            <textarea id="comment" name="comment" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-white placeholder-slate-500 focus:ring-primary focus:border-primary" placeholder="Nhập cảm nhận của bạn về bộ phim này..."></textarea>
                                            @error('comment')
                                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="bg-primary hover:bg-red-700 text-white font-bold py-3 px-8 rounded-full transition-all">
                                            Gửi Đánh Giá
                                        </button>
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
                        <div class="space-y-6">
                            @forelse($reviews as $review)
                                <div class="bg-slate-800/30 p-6 rounded-2xl border border-slate-700/30 flex gap-4">
                                    <div class="w-12 h-12 bg-slate-700 rounded-full flex items-center justify-center text-xl font-bold text-slate-300 flex-shrink-0">
                                        {{ substr($review->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-bold text-white">{{ $review->user->name }}</h4>
                                            <span class="text-xs text-slate-500">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex text-yellow-400 text-sm mb-3">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-slate-600' }}"></i>
                                            @endfor
                                        </div>
                                        <p class="text-slate-300 leading-relaxed">{{ $review->comment }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
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
