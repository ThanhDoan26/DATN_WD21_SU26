@extends('layouts.frontend')

@section('title', 'Phim Sắp Chiếu - movieGo')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
<style>
    .font-bebas { font-family: 'Bebas Neue', sans-serif; }

    /* === PAGE HERO === */
    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #060b14 0%, #0a0d1f 50%, #06080f 100%);
        padding-top: 120px;
        padding-bottom: 80px;
    }
    .page-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 50%, rgba(99,102,241,0.10) 0%, transparent 65%),
            radial-gradient(ellipse 50% 70% at 85% 30%, rgba(229,9,20,0.08) 0%, transparent 60%);
        pointer-events: none;
    }
    .hero-grid-lines {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.5) 40%, transparent 100%);
        pointer-events: none;
    }
    .hero-glow-blue {
        position: absolute;
        top: -60px; left: -60px;
        width: 480px; height: 480px;
        background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 65%);
        pointer-events: none;
        animation: glow-pulse 5s ease-in-out infinite;
    }

    /* === ANIMATIONS === */
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes glow-pulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50%       { opacity: 1;   transform: scale(1.12); }
    }
    @keyframes shimmer-blue {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    .anim-1 { animation: fade-up 0.8s 0.05s ease-out both; }
    .anim-2 { animation: fade-up 0.8s 0.15s ease-out both; }
    .anim-3 { animation: fade-up 0.8s 0.25s ease-out both; }
    .anim-4 { animation: fade-up 0.8s 0.35s ease-out both; }

    .shimmer-blue {
        background: linear-gradient(90deg, #fff 0%, #818cf8 40%, #fff 60%, #818cf8 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer-blue 4s linear infinite;
    }

    /* === SECTION DIVIDER === */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(99,102,241,0.4), rgba(255,255,255,0.1), rgba(99,102,241,0.4), transparent);
    }

    /* === CINEMA BADGE === */
    .cinema-badge-blue {
        background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05));
        border: 1px solid rgba(99,102,241,0.35);
        backdrop-filter: blur(8px);
    }

    /* === UPCOMING CARD === */
    .upcoming-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
    }
    .upcoming-card:hover {
        border-color: rgba(99,102,241,0.35);
        background: rgba(99,102,241,0.04);
        transform: translateY(-6px);
        box-shadow: 0 24px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(99,102,241,0.15);
    }
    .poster-wrap {
        position: relative;
        height: 288px;
        overflow: hidden;
        background: linear-gradient(135deg, #1e1b4b, #0f172a);
    }
    .poster-wrap img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .upcoming-card:hover .poster-wrap img { transform: scale(1.07); }

    .age-badge {
        position: absolute;
        top: 12px; right: 12px;
        background: rgba(229,9,20,0.9);
        backdrop-filter: blur(6px);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
    }
    .coming-soon-badge {
        position: absolute;
        top: 12px; left: 12px;
        background: rgba(99,102,241,0.85);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(165,180,252,0.3);
        color: white;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 9999px;
    }

    .card-body { padding: 20px; }

    .card-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: white;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.25s;
        line-height: 1.35;
    }
    .upcoming-card:hover .card-title { color: #a5b4fc; }

    .card-meta {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-bottom: 14px;
        font-size: 13px;
        color: #94a3b8;
    }
    .card-meta span { display: flex; align-items: center; gap: 8px; }
    .card-meta i { color: #818cf8; width: 14px; flex-shrink: 0; }

    .card-desc {
        font-size: 13px;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .showtime-box {
        background: rgba(99,102,241,0.08);
        border: 1px solid rgba(99,102,241,0.15);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 16px;
    }
    .showtime-box .label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #818cf8;
        font-weight: 600;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .showtime-box .value {
        font-size: 13px;
        color: #e2e8f0;
        font-weight: 500;
    }

    .btn-notify {
        width: 100%;
        background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(99,102,241,0.1));
        border: 1px solid rgba(99,102,241,0.35);
        color: #a5b4fc;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-notify:hover {
        background: linear-gradient(135deg, rgba(99,102,241,0.35), rgba(99,102,241,0.2));
        border-color: rgba(165,180,252,0.5);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(99,102,241,0.25);
    }

    /* === STAT PILL === */
    .stat-pill {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .stat-pill:hover {
        background: rgba(99,102,241,0.1);
        border-color: rgba(99,102,241,0.3);
    }

    /* === EMPTY STATE === */
    .empty-state {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
    }
</style>
@endpush

@section('content')

{{-- ===================== PAGE HERO ===================== --}}
<section class="page-hero">
    <div class="hero-grid-lines"></div>
    <div class="hero-glow-blue"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        {{-- Badge --}}
        <div class="anim-1 mb-5">
            <span class="cinema-badge-blue inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs uppercase tracking-[0.25em] text-indigo-300 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                Sắp ra mắt
            </span>
        </div>

        {{-- Heading --}}
        <div class="anim-2 mb-4">
            <h1 class="font-bebas uppercase leading-[0.92] tracking-wide text-white" style="font-size: clamp(3rem, 9vw, 7rem);">
                Phim <span class="shimmer-blue">Sắp Chiếu</span>
            </h1>
        </div>

        {{-- Sub --}}
        <div class="anim-3 mb-8">
            <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                Những tựa phim được mong đợi nhất sắp ra mắt tại <strong class="text-white">movieGo</strong> — đừng bỏ lỡ!
            </p>
        </div>

        {{-- Stats --}}
        <div class="anim-4 flex flex-wrap gap-3">
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-400 text-sm"></i>
                <span class="text-white font-semibold text-sm">{{ $movies->total() }}</span>
                <span class="text-slate-400 text-xs uppercase tracking-wider">Phim sắp chiếu</span>
            </div>
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-bell text-indigo-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">Nhận thông báo sớm nhất</span>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

{{-- ===================== MOVIES GRID ===================== --}}
<section class="py-16 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(180deg, #060b14 0%, #0a0d1f 100%);">
    <div class="max-w-7xl mx-auto">

        @if($movies->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                @foreach($movies as $movie)
                <div class="upcoming-card">
                    {{-- Poster --}}
                    <div class="poster-wrap">
                        @if($movie->poster_url)
                            <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}"
                                 alt="{{ $movie->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-film text-slate-600 text-5xl"></i>
                            </div>
                        @endif

                        @if($movie->age_rating)
                            <div class="age-badge">{{ $movie->age_rating }}</div>
                        @endif
                        <div class="coming-soon-badge">Sắp Chiếu</div>

                        {{-- Gradient overlay bottom --}}
                        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body">
                        <h3 class="card-title">{{ $movie->title }}</h3>

                        <div class="card-meta">
                            @if($movie->director)
                                <span><i class="fas fa-user-tie"></i>{{ $movie->director }}</span>
                            @endif
                            @if($movie->duration)
                                <span><i class="fas fa-clock"></i>{{ $movie->getDurationFormatted() }}</span>
                            @endif
                            @if($movie->language)
                                <span><i class="fas fa-globe"></i>{{ $movie->language }}</span>
                            @endif
                        </div>

                        @if($movie->description)
                            <p class="card-desc">{{ $movie->description }}</p>
                        @endif

                        @if($movie->showtimes && $movie->showtimes->count() > 0)
                            <div class="showtime-box">
                                <div class="label">
                                    <i class="fas fa-calendar-check"></i>
                                    Suất chiếu đầu tiên
                                </div>
                                <div class="value">{{ $movie->showtimes->first()->start_time->format('d/m/Y H:i') }}</div>
                            </div>
                        @endif

                        <button onclick="alert('Tính năng đặt vé cho phim sắp chiếu sẽ được kích hoạt khi phim có lịch chiếu')"
                                class="btn-notify">
                            <i class="fas fa-bell"></i> Nhận Thông Báo
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center mt-8">
                {{ $movies->links('pagination::tailwind') }}
            </div>
        @else
            <div class="empty-state text-center py-28 rounded-3xl">
                <i class="fas fa-calendar-times text-slate-700 text-7xl mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-3">Chưa có phim sắp chiếu</h3>
                <p class="text-slate-400 text-base">Chúng tôi sẽ cập nhật lịch chiếu sớm nhất. Hãy quay lại sau!</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-6 text-indigo-400 hover:text-indigo-300 font-medium text-sm transition-colors border border-indigo-500/30 hover:border-indigo-400/60 px-6 py-3 rounded-full hover:bg-indigo-500/10">
                    <i class="fas fa-arrow-left text-xs"></i> Về Trang Chủ
                </a>
            </div>
        @endif

    </div>
</section>

@endsection
