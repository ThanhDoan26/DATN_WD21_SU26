@extends('layouts.frontend')

@push('styles')
<style>
    .hero-gradient {
        background: linear-gradient(to top, #0f172a 0%, rgba(15, 23, 42, 0.4) 100%);
    }
</style>
@endpush

@section('content')

    <!-- Hero Section -->
    <div class="relative min-h-screen flex items-center justify-center pt-20">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1925&q=80" alt="Cinema Background" class="w-full h-full object-cover" />
            <div class="absolute inset-0 hero-gradient"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto mt-10">
            <span class="inline-block py-1 px-4 rounded-full bg-primary/20 text-red-400 border border-primary/30 font-semibold text-sm mb-6 animate-pulse">
                Trải nghiệm điện ảnh đỉnh cao
            </span>
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 drop-shadow-lg leading-tight">
                Bom Tấn Mới Nhất <br/> Đang Chờ Bạn
            </h1>
            <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl mx-auto drop-shadow-md">
                Đặt vé xem phim trực tuyến nhanh chóng, nhận ưu đãi hấp dẫn và tận hưởng những phút giây thư giãn tuyệt vời tại cụm rạp movieGo.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('movies.current') }}" class="bg-primary hover:bg-red-700 text-white px-8 py-4 rounded-full font-semibold text-lg transition-all transform hover:scale-105 shadow-xl shadow-red-500/40 flex items-center justify-center gap-2">
                    <i class="fas fa-ticket-alt"></i> Mua Vé Ngay
                </a>
                <a href="#" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/20 px-8 py-4 rounded-full font-semibold text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-play-circle"></i> Xem Trailer
                </a>
            </div>

            <!-- Enhanced Search Form Banner -->
            @include('partials.movie-search-form')
        </div>
    </div>

    @if(isset($hasSearch) && $hasSearch)
        <!-- Search Results Section -->
        <section id="search-results" class="relative bg-slate-900 py-20 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-4xl md:text-5xl font-bold text-white mb-2">
                            <i class="fas fa-search text-primary mr-3"></i>Kết Quả Tìm Kiếm
                        </h2>
<p class="text-slate-400">Tìm thấy <span class="text-primary font-bold">{{ $searchResults->total() }}</span> phim phù hợp.</p>
                    </div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-slate-400 hover:text-white font-semibold transition-colors bg-slate-800 px-4 py-2 rounded-lg">
                        Xoá bộ lọc <i class="fas fa-times"></i>
                    </a>
                </div>

                @if($searchResults->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($searchResults as $movie)
                            <x-movie-card :movie="$movie" />
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="flex justify-center mt-12 w-full overflow-hidden">
                        {{ $searchResults->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                @else
                    <div class="text-center py-20 bg-slate-800/50 rounded-2xl border border-slate-700/50">
                        <i class="fas fa-search-minus text-slate-500 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-white mb-2">Không tìm thấy kết quả nào!</h3>
                        <p class="text-slate-400 text-lg">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Auto Scroll to Results -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const resultsEl = document.getElementById('search-results');
                if(resultsEl) {
                    // Small offset to not hide behind sticky navbar if any
                    const y = resultsEl.getBoundingClientRect().top + window.scrollY - 80;
                    window.scrollTo({top: y, behavior: 'smooth'});
                }
            });
        </script>
    @else
        <!-- Current Movies Section -->
    @if($currentMovies->count() > 0)
    <section class="relative bg-slate-900 py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-2">
                        <i class="fas fa-film text-primary mr-3"></i>Phim Đang Chiếu
                    </h2>
                    <p class="text-slate-400">Những bom tấn đang được chiếu tại các rạp</p>
                </div>
                <a href="{{ route('movies.current') }}" class="hidden md:flex items-center gap-2 text-primary hover:text-red-400 font-semibold transition-colors">
                    Xem thêm <i class="fas fa-arrow-right"></i>
                </a>
</div>

            <!-- Movies Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($currentMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Upcoming Movies Section -->
    @if($upcomingMovies->count() > 0)
    <section class="relative bg-slate-800 py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-2">
                        <i class="fas fa-calendar-alt text-primary mr-3"></i>Phim Sắp Chiếu
                    </h2>
                    <p class="text-slate-400">Những phim được mong đợi sắp ra mắt</p>
                </div>
                <a href="{{ route('movies.upcoming') }}" class="hidden md:flex items-center gap-2 text-primary hover:text-red-400 font-semibold transition-colors">
                    Xem thêm <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Movies Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($upcomingMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Movies Section -->
    @if($featuredMovies->count() > 0)
    <section class="relative bg-slate-900 py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-2">
                        <i class="fas fa-star text-primary mr-3"></i>Phim Nổi Bật
                    </h2>
                    <p class="text-slate-400">Những phim được yêu thích nhất</p>
                </div>
                <a href="#" class="hidden md:flex items-center gap-2 text-primary hover:text-red-400 font-semibold transition-colors">
                    Xem thêm <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Movies Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif
@endsection
