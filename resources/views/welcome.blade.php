@extends('layouts.frontend')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* ===== FONTS ===== */
    .font-bebas { font-family: 'Bebas Neue', sans-serif; }

    /* ===== HERO VIDEO BG ===== */
    .hero-video-wrap {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
    }
    .hero-video-wrap video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.45) saturate(1.2);
    }
    /* Multi-layer gradient overlay */
    .hero-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(to right, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 60%, transparent 100%),
            linear-gradient(to top,   rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.2) 40%, transparent 100%);
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(35px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fade-in {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(229,9,20,0.4); }
        50%       { box-shadow: 0 0 40px rgba(229,9,20,0.8), 0 0 60px rgba(229,9,20,0.3); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-6px); }
    }
    @keyframes scan-line {
        0%   { top: -10%; }
        100% { top: 110%; }
    }

    .anim-fade-up   { animation: fade-up 0.9s ease-out forwards; opacity: 0; }
    .anim-delay-1   { animation: fade-up 0.9s 0.15s ease-out forwards; opacity: 0; }
    .anim-delay-2   { animation: fade-up 0.9s 0.30s ease-out forwards; opacity: 0; }
    .anim-delay-3   { animation: fade-up 0.9s 0.45s ease-out forwards; opacity: 0; }
    .anim-delay-4   { animation: fade-up 0.9s 0.60s ease-out forwards; opacity: 0; }
    .anim-delay-5   { animation: fade-up 0.9s 0.75s ease-out forwards; opacity: 0; }
    .anim-delay-6   { animation: fade-up 0.9s 0.90s ease-out forwards; opacity: 0; }

    /* ===== GLOWING BADGE ===== */
    .glow-badge {
        background: linear-gradient(135deg, rgba(229,9,20,0.15), rgba(229,9,20,0.05));
        border: 1px solid rgba(229,9,20,0.4);
        backdrop-filter: blur(8px);
    }
    .glow-badge::before {
        content: '';
        position: absolute;
        inset: -1px;
        border-radius: 9999px;
        background: linear-gradient(135deg, rgba(229,9,20,0.5), transparent);
        z-index: -1;
        opacity: 0.5;
    }

    /* ===== SHIMMER TEXT ===== */
    .shimmer-text {
        background: linear-gradient(90deg, #fff 0%, #e50914 40%, #fff 60%, #e50914 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 4s linear infinite;
    }

    /* ===== CTA BUTTONS ===== */
    .btn-primary-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #e50914, #b0060f);
        transition: all 0.3s ease;
        animation: pulse-glow 3s ease-in-out infinite;
    }
    .btn-primary-hero::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.5s ease;
    }
    .btn-primary-hero:hover::before { left: 100%; }
    .btn-primary-hero:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 20px 40px rgba(229,9,20,0.5);
    }

    .btn-ghost-hero {
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .btn-ghost-hero:hover {
        background: rgba(255,255,255,0.15);
        border-color: rgba(255,255,255,0.4);
        transform: translateY(-2px);
    }

    /* ===== STATS ===== */
    .stat-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        backdrop-filter: blur(12px);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        background: rgba(229,9,20,0.08);
        border-color: rgba(229,9,20,0.25);
        transform: translateY(-3px);
    }
    .stat-num {
        font-family: 'Bebas Neue', sans-serif;
        background: linear-gradient(135deg, #fff, #e50914);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* ===== SEARCH FORM ===== */
    .search-container {
        background: rgba(10, 15, 30, 0.75);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(24px);
        transition: all 0.3s ease;
    }
    .search-container:hover {
        border-color: rgba(229,9,20,0.3);
        background: rgba(10, 15, 30, 0.85);
    }
    .search-input {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .search-input:focus {
        background: rgba(255,255,255,0.08);
        border-color: rgba(229,9,20,0.6);
        box-shadow: 0 0 0 3px rgba(229,9,20,0.1);
        outline: none;
    }
    .search-input::placeholder { color: rgba(255,255,255,0.35); }
    .search-select {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        -webkit-appearance: none;
        transition: all 0.3s ease;
    }
    .search-select:focus {
        border-color: rgba(229,9,20,0.6);
        box-shadow: 0 0 0 3px rgba(229,9,20,0.1);
        outline: none;
    }
    .search-select option { background: #0f172a; color: white; }

    /* ===== SECTION HEADERS ===== */
    .section-header-line {
        width: 60px; height: 3px;
        background: linear-gradient(90deg, #e50914, transparent);
        border-radius: 2px;
    }

    /* ===== MOVIE SECTIONS ===== */
    .movies-section-dark  { background: linear-gradient(180deg, #060b14 0%, #0d1525 100%); }
    .movies-section-mid   { background: linear-gradient(180deg, #0d1525 0%, #111827 100%); }

    /* ===== SCAN LINE EFFECT ON HERO ===== */
    .scan-line {
        position: absolute;
        left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(229,9,20,0.3), transparent);
        animation: scan-line 8s linear infinite;
        pointer-events: none;
        z-index: 2;
    }

    /* ===== FLOATING ELEMENT ===== */
    .float-anim { animation: float 4s ease-in-out infinite; }

    /* ===== SECTION DIVIDER ===== */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(229,9,20,0.4), rgba(255,255,255,0.1), rgba(229,9,20,0.4), transparent);
    }

    /* ===== SEARCH RESULTS empty state ===== */
    .empty-state {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
    }
</style>
@endpush

@section('content')

<!-- ===================== HERO SECTION ===================== -->
<section class="relative min-h-screen flex items-center" style="min-height: 100svh;">

    <!-- Video Background -->
    <div class="hero-video-wrap">
        <video autoplay muted loop playsinline>
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260606_154941_df1a96e1-a06f-450c-bd02-d863414cc1a0.mp4" type="video/mp4">
            <!-- Fallback image if video doesn't load -->
            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1925&q=80" alt="Cinema" class="w-full h-full object-cover">
        </video>
        <div class="hero-overlay"></div>
        <div class="scan-line"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 w-full px-6 sm:px-10 lg:px-16 pt-24 pb-16 max-w-7xl mx-auto">
        <div class="max-w-3xl">

            <!-- Badge -->
            <div class="anim-fade-up mb-6 sm:mb-8">
                <span class="glow-badge relative inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs sm:text-sm uppercase tracking-[0.25em] text-red-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Trải nghiệm điện ảnh đỉnh cao
                </span>
            </div>

            <!-- Main Heading -->
            <div class="anim-delay-1 mb-6 sm:mb-8">
                <h1 class="font-bebas uppercase leading-[0.9] tracking-wide text-white" style="font-size: clamp(3.5rem, 10vw, 8rem);">
                    Bom Tấn<br>
                    <span class="shimmer-text">Mới Nhất</span><br>
                    Đang Chờ Bạn
                </h1>
            </div>

            <!-- Subtext -->
            <div class="anim-delay-2 mb-8 sm:mb-10">
                <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-xl">
                    Đặt vé xem phim trực tuyến nhanh chóng, nhận ưu đãi hấp dẫn và<br class="hidden sm:block">
                    tận hưởng những phút giây thư giãn tuyệt vời tại cụm rạp
                    <strong class="text-white">movieGo</strong>.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="anim-delay-3 flex flex-col sm:flex-row gap-4 mb-10 sm:mb-14">
                <a href="{{ route('movies.current') }}" class="btn-primary-hero inline-flex items-center justify-center gap-3 text-white px-8 py-4 rounded-full font-semibold text-base tracking-wide group">
                    <i class="fas fa-ticket-alt text-sm"></i>
                    Mua Vé Ngay
                    <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-200"></i>
                </a>
                <a href="#" class="btn-ghost-hero inline-flex items-center justify-center gap-3 text-white px-8 py-4 rounded-full font-semibold text-base tracking-wide group">
                    <span class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center group-hover:bg-red-600/60 transition-colors duration-300">
                        <i class="fas fa-play text-xs ml-0.5"></i>
                    </span>
                    Xem Trailer
                </a>
            </div>

            <!-- Stats Row -->
            <div class="anim-delay-4 flex flex-wrap gap-3 sm:gap-4 mb-12 sm:mb-16">
                <div class="stat-card rounded-2xl px-5 py-4 text-center min-w-[100px]">
                    <div class="stat-num text-3xl sm:text-4xl font-bold">100+</div>
                    <div class="text-slate-400 text-[10px] sm:text-xs uppercase tracking-widest mt-1">Phim chiếu</div>
                </div>
                <div class="stat-card rounded-2xl px-5 py-4 text-center min-w-[100px]">
                    <div class="stat-num text-3xl sm:text-4xl font-bold">50K+</div>
                    <div class="text-slate-400 text-[10px] sm:text-xs uppercase tracking-widest mt-1">Khách hàng</div>
                </div>
                <div class="stat-card rounded-2xl px-5 py-4 text-center min-w-[100px]">
                    <div class="stat-num text-3xl sm:text-4xl font-bold">4.9★</div>
                    <div class="text-slate-400 text-[10px] sm:text-xs uppercase tracking-widest mt-1">Đánh giá</div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="anim-delay-5">
                <div class="search-container rounded-2xl p-5 sm:p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-search text-red-400 text-sm"></i>
                        <span class="text-white/60 text-xs uppercase tracking-widest font-medium">Tìm kiếm phim</span>
                    </div>
                    <form action="{{ route('home') }}" method="GET">
                        <div class="flex flex-col gap-3">
                            <!-- Keyword -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                                    <i class="fas fa-film text-sm"></i>
                                </span>
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    placeholder="Nhập tên phim bạn muốn tìm..."
                                    class="search-input w-full rounded-xl py-3 pl-12 pr-4 text-sm">
                            </div>

                            <!-- Filters + Button -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Status -->
                                <div class="relative">
                                    <select name="status" class="search-select w-full rounded-xl py-3 px-4 pr-10 text-sm cursor-pointer">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="NOW_SHOWING" {{ request('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                                        <option value="COMING_SOON" {{ request('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>

                                <!-- Genre -->
                                <div class="relative">
                                    <select name="genre_id" class="search-select w-full rounded-xl py-3 px-4 pr-10 text-sm cursor-pointer">
                                        <option value="">Tất cả thể loại</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('genre_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <button type="submit" class="btn-primary-hero flex items-center justify-center gap-2 text-white px-6 py-3 rounded-xl font-semibold text-sm tracking-wide">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2 opacity-50 float-anim">
        <span class="text-white/50 text-[10px] uppercase tracking-widest">Cuộn xuống</span>
        <div class="w-5 h-8 rounded-full border border-white/30 flex items-start justify-center p-1">
            <div class="w-1 h-2 rounded-full bg-white/60 animate-bounce"></div>
        </div>
    </div>
</section>

<!-- ===================== SEARCH RESULTS ===================== -->
@if(isset($hasSearch) && $hasSearch)
<div class="section-divider"></div>
<section id="search-results" class="movies-section-dark py-20 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-12">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="section-header-line"></div>
                    <span class="text-red-400 text-xs uppercase tracking-widest font-medium">Kết quả</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-2 font-bebas tracking-wide">
                    Kết Quả Tìm Kiếm
                </h2>
                <p class="text-slate-400">Tìm thấy <span class="text-red-400 font-bold">{{ $searchResults->total() }}</span> phim phù hợp</p>
            </div>
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-slate-400 hover:text-white font-medium transition-colors bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 px-5 py-2.5 rounded-full text-sm">
                <i class="fas fa-times text-xs"></i> Xoá bộ lọc
            </a>
        </div>

        @if($searchResults->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($searchResults as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
            <div class="flex justify-center mt-12">
                {{ $searchResults->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        @else
            <div class="empty-state text-center py-24 rounded-3xl">
                <i class="fas fa-search-minus text-slate-600 text-7xl mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-3">Không tìm thấy kết quả!</h3>
                <p class="text-slate-400 text-lg">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
            </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('search-results');
        if (el) {
            window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
        }
    });
</script>

@else

    <!-- ===================== PHIM ĐANG CHIẾU ===================== -->
    @if($currentMovies->count() > 0)
    <div class="section-divider"></div>
    <section class="movies-section-dark py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="section-header-line"></div>
                        <span class="text-red-400 text-xs uppercase tracking-widest font-medium">Đang chiếu</span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white font-bebas tracking-wide">
                        Phim Đang Chiếu
                    </h2>
                    <p class="text-slate-400 mt-1">Những bom tấn đang được chiếu tại các rạp</p>
                </div>
                <a href="{{ route('movies.current') }}" class="hidden md:inline-flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-medium transition-colors border border-red-500/30 hover:border-red-400/60 px-5 py-2.5 rounded-full hover:bg-red-500/10">
                    Xem thêm <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($currentMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ===================== PHIM SẮP CHIẾU ===================== -->
    @if($upcomingMovies->count() > 0)
    <div class="section-divider"></div>
    <section class="movies-section-mid py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="section-header-line"></div>
                        <span class="text-red-400 text-xs uppercase tracking-widest font-medium">Sắp ra mắt</span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white font-bebas tracking-wide">
                        Phim Sắp Chiếu
                    </h2>
                    <p class="text-slate-400 mt-1">Những phim được mong đợi sắp ra mắt</p>
                </div>
                <a href="{{ route('movies.upcoming') }}" class="hidden md:inline-flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-medium transition-colors border border-red-500/30 hover:border-red-400/60 px-5 py-2.5 rounded-full hover:bg-red-500/10">
                    Xem thêm <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($upcomingMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ===================== PHIM NỔI BẬT ===================== -->
    @if($featuredMovies->count() > 0)
    <div class="section-divider"></div>
    <section class="movies-section-dark py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="section-header-line"></div>
                        <span class="text-red-400 text-xs uppercase tracking-widest font-medium">Nổi bật</span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white font-bebas tracking-wide">
                        Phim Nổi Bật
                    </h2>
                    <p class="text-slate-400 mt-1">Những phim được yêu thích nhất</p>
                </div>
                <a href="#" class="hidden md:inline-flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-medium transition-colors border border-red-500/30 hover:border-red-400/60 px-5 py-2.5 rounded-full hover:bg-red-500/10">
                    Xem thêm <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
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
