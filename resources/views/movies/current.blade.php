@extends('layouts.frontend')

@section('title', 'Phim Đang Chiếu - movieGo')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
<style>
    .font-bebas { font-family: 'Bebas Neue', sans-serif; }

    /* === PAGE HERO === */
    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #060b14 0%, #0d0f1a 50%, #0a0612 100%);
        padding-top: 120px;
        padding-bottom: 80px;
    }
    .page-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 10% 50%, rgba(229,9,20,0.12) 0%, transparent 60%),
            radial-gradient(ellipse 50% 80% at 90% 20%, rgba(229,9,20,0.06) 0%, transparent 60%);
        pointer-events: none;
    }
    .hero-grid-lines {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.6) 40%, transparent 100%);
        pointer-events: none;
    }
    .hero-glow {
        position: absolute;
        top: -80px; left: -80px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(229,9,20,0.15) 0%, transparent 65%);
        pointer-events: none;
        animation: glow-pulse 4s ease-in-out infinite;
    }

    /* === ANIMATIONS === */
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes glow-pulse {
        0%, 100% { opacity: 0.6; transform: scale(1); }
        50%       { opacity: 1;   transform: scale(1.1); }
    }
    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    .anim-1 { animation: fade-up 0.8s 0.05s ease-out both; }
    .anim-2 { animation: fade-up 0.8s 0.15s ease-out both; }
    .anim-3 { animation: fade-up 0.8s 0.25s ease-out both; }
    .anim-4 { animation: fade-up 0.8s 0.35s ease-out both; }

    .shimmer-text {
        background: linear-gradient(90deg, #fff 0%, #e50914 40%, #fff 60%, #e50914 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 4s linear infinite;
    }

    /* === SECTION DIVIDER === */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(229,9,20,0.4), rgba(255,255,255,0.1), rgba(229,9,20,0.4), transparent);
    }

    /* === BADGE === */
    .cinema-badge {
        background: linear-gradient(135deg, rgba(229,9,20,0.15), rgba(229,9,20,0.05));
        border: 1px solid rgba(229,9,20,0.35);
        backdrop-filter: blur(8px);
    }

    /* === EMPTY STATE === */
    .empty-state {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
    }

    /* === STAT PILL === */
    .stat-pill {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .stat-pill:hover {
        background: rgba(229,9,20,0.1);
        border-color: rgba(229,9,20,0.3);
    }
</style>
@endpush

@section('content')

{{-- ===================== PAGE HERO ===================== --}}
<section class="page-hero">
    <div class="hero-grid-lines"></div>
    <div class="hero-glow"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        {{-- Badge --}}
        <div class="anim-1 mb-5">
            <span class="cinema-badge inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs uppercase tracking-[0.25em] text-red-300 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                Đang chiếu tại rạp
            </span>
        </div>

        {{-- Heading --}}
        <div class="anim-2 mb-4">
            <h1 class="font-bebas uppercase leading-[0.92] tracking-wide text-white" style="font-size: clamp(3rem, 9vw, 7rem);">
                Phim <span class="shimmer-text">Đang Chiếu</span>
            </h1>
        </div>

        {{-- Sub --}}
        <div class="anim-3 mb-8">
            <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                Những bom tấn đang được chiếu tại các rạp <strong class="text-white">movieGo</strong> — đặt vé ngay hôm nay.
            </p>
        </div>

        {{-- Stats --}}
        <div class="anim-4 flex flex-wrap gap-3">
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-film text-red-400 text-sm"></i>
                <span class="text-white font-semibold text-sm">{{ $movies->total() }}</span>
                <span class="text-slate-400 text-xs uppercase tracking-wider">Phim đang chiếu</span>
            </div>
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-ticket-alt text-red-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">Đặt vé nhanh · Giá tốt nhất</span>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

{{-- ===================== MOVIES GRID ===================== --}}
<section class="py-16 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(180deg, #060b14 0%, #0d1525 100%);">
    <div class="max-w-7xl mx-auto">

        @if($movies->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                @foreach($movies as $movie)
                    <x-movie-list-card :movie="$movie" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center mt-8">
                {{ $movies->links('pagination::tailwind') }}
            </div>
        @else
            <div class="empty-state text-center py-28 rounded-3xl">
                <i class="fas fa-film text-slate-700 text-7xl mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-3">Chưa có phim đang chiếu</h3>
                <p class="text-slate-400 text-base">Vui lòng quay lại sau để cập nhật lịch chiếu mới nhất.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-6 text-red-400 hover:text-red-300 font-medium text-sm transition-colors border border-red-500/30 hover:border-red-400/60 px-6 py-3 rounded-full hover:bg-red-500/10">
                    <i class="fas fa-arrow-left text-xs"></i> Về Trang Chủ
                </a>
            </div>
        @endif

    </div>
</section>

@endsection
