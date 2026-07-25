@extends('layouts.frontend')

@section('title', 'Tin Tức & Sự Kiện - movieGo')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
<style>
    .font-bebas { font-family: 'Bebas Neue', sans-serif; }

    /* === PAGE HERO === */
    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #060b14 0%, #0f0d0a 50%, #06080f 100%);
        padding-top: 120px;
        padding-bottom: 80px;
    }
    .page-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 50%, rgba(234,179,8,0.08) 0%, transparent 65%),
            radial-gradient(ellipse 50% 70% at 85% 30%, rgba(229,9,20,0.06) 0%, transparent 60%);
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
    .hero-glow-amber {
        position: absolute;
        top: -60px; left: -60px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(234,179,8,0.1) 0%, transparent 65%);
        pointer-events: none;
        animation: glow-pulse 5s ease-in-out infinite;
    }

    @keyframes fade-up {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes glow-pulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50%       { opacity: 1;   transform: scale(1.1); }
    }
    @keyframes shimmer-amber {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }

    .anim-1 { animation: fade-up 0.8s 0.05s ease-out both; }
    .anim-2 { animation: fade-up 0.8s 0.15s ease-out both; }
    .anim-3 { animation: fade-up 0.8s 0.25s ease-out both; }
    .anim-4 { animation: fade-up 0.8s 0.35s ease-out both; }
    .anim-5 { animation: fade-up 0.8s 0.45s ease-out both; }

    .shimmer-amber {
        background: linear-gradient(90deg, #fff 0%, #fbbf24 40%, #fff 60%, #fbbf24 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer-amber 4s linear infinite;
    }

    /* === SECTION DIVIDER === */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(234,179,8,0.4), rgba(255,255,255,0.1), rgba(234,179,8,0.4), transparent);
    }

    /* === CINEMA BADGE === */
    .cinema-badge-amber {
        background: linear-gradient(135deg, rgba(234,179,8,0.12), rgba(234,179,8,0.04));
        border: 1px solid rgba(234,179,8,0.3);
        backdrop-filter: blur(8px);
    }

    /* === SEARCH FORM === */
    .search-input-dark {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .search-input-dark:focus {
        background: rgba(255,255,255,0.08);
        border-color: rgba(234,179,8,0.5);
        box-shadow: 0 0 0 3px rgba(234,179,8,0.08);
        outline: none;
    }
    .search-input-dark::placeholder { color: rgba(255,255,255,0.3); }

    .btn-search-amber {
        background: linear-gradient(135deg, rgba(234,179,8,0.25), rgba(234,179,8,0.12));
        border: 1px solid rgba(234,179,8,0.4);
        color: #fbbf24;
        transition: all 0.3s ease;
    }
    .btn-search-amber:hover {
        background: linear-gradient(135deg, rgba(234,179,8,0.4), rgba(234,179,8,0.2));
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(234,179,8,0.2);
    }

    /* === LIVE SEARCH OVERLAY === */
    .live-search-wrap { position: relative; }
    #live-search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0; right: 0;
        background: #0f172a;
        border: 1px solid rgba(234,179,8,0.25);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        z-index: 999;
        overflow: hidden;
        display: none;
        max-height: 380px;
        overflow-y: auto;
    }
    #live-search-dropdown::-webkit-scrollbar { width: 4px; }
    #live-search-dropdown::-webkit-scrollbar-track { background: transparent; }
    #live-search-dropdown::-webkit-scrollbar-thumb { background: rgba(234,179,8,0.3); border-radius: 2px; }
    .ls-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        text-decoration: none;
        transition: background 0.2s;
    }
    .ls-item:last-child { border-bottom: none; }
    .ls-item:hover { background: rgba(234,179,8,0.06); }
    .ls-item-img {
        width: 48px; height: 48px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: #1e293b;
    }
    .ls-item-title { font-size: 13px; font-weight: 600; color: white; line-height: 1.4; }
    .ls-item-cat { font-size: 10px; color: #fbbf24; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .ls-no-result { padding: 20px; text-align: center; color: #475569; font-size: 13px; }

    /* === STAT PILL === */
    .stat-pill {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .stat-pill:hover {
        background: rgba(234,179,8,0.08);
        border-color: rgba(234,179,8,0.3);
    }

    /* === FEATURED SPOTLIGHT === */
    .featured-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.07);
        background: #0f172a;
        transition: all 0.35s ease;
    }
    .featured-card:hover { border-color: rgba(234,179,8,0.25); }
    .featured-card img {
        transition: transform 0.5s ease;
    }
    .featured-card:hover img { transform: scale(1.05); }

    /* === CATEGORY TABS === */
    .cat-tab {
        padding: 8px 18px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.25s ease;
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
    }
    .cat-tab.active {
        background: linear-gradient(135deg, #e50914, #b0060f);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 16px rgba(229,9,20,0.25);
    }
    .cat-tab.inactive {
        background: rgba(255,255,255,0.04);
        color: #94a3b8;
        border-color: rgba(255,255,255,0.08);
    }
    .cat-tab.inactive:hover {
        background: rgba(255,255,255,0.08);
        color: white;
        border-color: rgba(255,255,255,0.15);
    }

    /* === ARTICLE CARDS === */
    .article-card {
        background: rgba(255,255,255,0.025);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
    }
    .article-card:hover {
        border-color: rgba(234,179,8,0.2);
        background: rgba(234,179,8,0.02);
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .article-card.hidden-by-filter { display: none; }
    .article-img-wrap {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: #0f172a;
    }
    .article-img-wrap img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .article-card:hover .article-img-wrap img { transform: scale(1.08); }

    .article-cat-badge {
        position: absolute;
        top: 14px; left: 14px;
        background: rgba(0,0,0,0.75);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(234,179,8,0.3);
        color: #fbbf24;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 9999px;
    }
    .article-body {
        padding: 22px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .article-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11px;
        color: #475569;
        margin-bottom: 12px;
    }
    .article-meta i { color: #64748b; }
    .article-title {
        font-weight: 700;
        font-size: 1rem;
        color: white;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 10px;
        transition: color 0.25s;
    }
    .article-card:hover .article-title { color: #fbbf24; }
    .article-summary {
        font-size: 13px;
        color: #475569;
        line-height: 1.65;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 16px;
        flex: 1;
    }
    .article-footer {
        padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .article-author { font-size: 11px; color: #334155; font-weight: 500; }
    .read-more-link {
        font-size: 12px;
        font-weight: 600;
        color: #fbbf24;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .read-more-link:hover {
        color: white;
        gap: 8px;
    }

    /* === SIDEBAR === */
    .sidebar-card {
        background: rgba(255,255,255,0.025);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .sidebar-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #fbbf24;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sidebar-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(234,179,8,0.3), transparent);
    }

    /* Popular post item */
    .pop-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        text-decoration: none;
        transition: all 0.2s;
    }
    .pop-item:last-child { border-bottom: none; padding-bottom: 0; }
    .pop-item:first-child { padding-top: 0; }
    .pop-item:hover .pop-title { color: #fbbf24; }
    .pop-rank {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 22px;
        color: rgba(234,179,8,0.2);
        line-height: 1;
        flex-shrink: 0;
        width: 24px;
        text-align: center;
        transition: color 0.2s;
    }
    .pop-item:hover .pop-rank { color: rgba(234,179,8,0.6); }
    .pop-img {
        width: 64px; height: 64px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: #1e293b;
    }
    .pop-title {
        font-size: 13px;
        font-weight: 600;
        color: #cbd5e1;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s;
        margin-bottom: 5px;
    }
    .pop-views { font-size: 10px; color: #475569; }

    /* Tag cloud */
    .tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        color: #94a3b8;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .tag-pill:hover {
        background: rgba(234,179,8,0.08);
        border-color: rgba(234,179,8,0.3);
        color: #fbbf24;
    }
    .tag-pill.active-tag {
        background: rgba(229,9,20,0.12);
        border-color: rgba(229,9,20,0.3);
        color: #f87171;
    }

    /* Newsletter */
    .newsletter-wrap {
        background: linear-gradient(135deg, rgba(229,9,20,0.08), rgba(234,179,8,0.06));
        border: 1px solid rgba(229,9,20,0.2);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .newsletter-input {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        padding: 10px 14px;
        font-size: 13px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
    }
    .newsletter-input:focus { border-color: rgba(229,9,20,0.5); }
    .newsletter-input::placeholder { color: #475569; }
    .btn-subscribe {
        background: linear-gradient(135deg, #e50914, #b0060f);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        transition: all 0.25s;
    }
    .btn-subscribe:hover {
        background: linear-gradient(135deg, #ff1a24, #cc0710);
        box-shadow: 0 6px 20px rgba(229,9,20,0.3);
        transform: translateY(-1px);
    }

    /* === EMPTY STATE === */
    .empty-state {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
    }

    /* === NO LIVE RESULT === */
    #no-live-result {
        display: none;
        text-align: center;
        padding: 48px 20px;
        color: #475569;
    }

    /* Reading time badge */
    .reading-time-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        color: #64748b;
    }
</style>
@endpush

@section('content')

{{-- ===================== PAGE HERO ===================== --}}
<section class="page-hero">
    <div class="hero-grid-lines"></div>
    <div class="hero-glow-amber"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        {{-- Badge --}}
        <div class="anim-1 mb-5">
            <span class="cinema-badge-amber inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs uppercase tracking-[0.25em] text-amber-300 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                Bản tin điện ảnh
            </span>
        </div>

        {{-- Heading --}}
        <div class="anim-2 mb-4">
            <h1 class="font-bebas uppercase leading-[0.92] tracking-wide text-white" style="font-size: clamp(3rem, 9vw, 7rem);">
                Tin Tức <span class="shimmer-amber">& Sự Kiện</span>
            </h1>
        </div>

        {{-- Sub --}}
        <div class="anim-3 mb-8">
            <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                Cập nhật nhanh nhất tin tức điện ảnh, sự kiện rạp chiếu phim và chương trình khuyến mãi hấp dẫn từ <strong class="text-white">movieGo</strong>.
            </p>
        </div>

        {{-- Live Search --}}
        <div class="anim-4 max-w-lg">
            <div class="live-search-wrap">
                <form action="{{ route('posts.index') }}" method="GET" id="search-form" class="flex gap-2">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" name="search" id="live-search-input"
                               value="{{ request('search') }}"
                               placeholder="Tìm kiếm bài viết..."
                               autocomplete="off"
                               class="search-input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm">
                        {{-- Clear button --}}
                        <button type="button" id="clear-search" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors" style="display:none;">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <button type="submit" class="btn-search-amber px-6 py-3 rounded-xl font-semibold text-sm flex items-center gap-2">
                        Tìm
                    </button>
                </form>
                {{-- Dropdown live search --}}
                <div id="live-search-dropdown"></div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="anim-5 flex flex-wrap gap-3 mt-6">
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-newspaper text-amber-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">{{ $posts->total() }} bài viết</span>
            </div>
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-tags text-amber-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">{{ $categories->count() }} chủ đề</span>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

{{-- ===================== CONTENT ===================== --}}
<div class="py-16 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(180deg, #060b14 0%, #0d0f0a 100%);">
    <div class="max-w-7xl mx-auto">

        {{-- ===== FEATURED SPOTLIGHT ===== --}}
        @if($featuredPosts->count() > 0 && !request()->filled('search') && !request()->filled('category'))
        <div class="mb-16">
            <div class="flex items-center gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div style="width:48px;height:2px;background:linear-gradient(90deg,#fbbf24,transparent);border-radius:2px;"></div>
                    <span class="text-amber-400 text-xs uppercase tracking-widest font-medium">Tiêu điểm</span>
                </div>
            </div>
            <h2 class="font-bebas text-3xl sm:text-4xl text-white uppercase tracking-wide mb-8">
                Tin Tức Nổi Bật
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Big featured --}}
                @if(isset($featuredPosts[0]))
                @php $first = $featuredPosts[0]; @endphp
                <div class="featured-card lg:col-span-2 flex flex-col" style="height:460px;">
                    <img src="{{ asset('storage/' . $first->image) }}"
                         alt="{{ $first->title }}"
                         class="absolute inset-0 w-full h-full object-cover"
                         style="position:absolute;inset:0;">
                    <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(6,11,20,0.97) 0%, rgba(6,11,20,0.4) 50%, transparent 100%);"></div>
                    <div class="relative z-10 mt-auto p-7 md:p-9">
                        <span class="inline-block mb-3 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full"
                              style="background:rgba(234,179,8,0.15);border:1px solid rgba(234,179,8,0.35);color:#fbbf24;">
                            {{ $first->category?->name }}
                        </span>
                        <h3 class="text-2xl md:text-3xl font-bold text-white mb-3 leading-snug hover:text-amber-400 transition-colors">
                            <a href="{{ route('posts.show', $first->slug) }}">{{ $first->title }}</a>
                        </h3>
                        <p class="text-slate-300 text-sm mb-4 line-clamp-2 leading-relaxed">{{ $first->summary }}</p>
                        <div class="flex items-center gap-4 text-slate-500 text-xs">
                            <span class="flex items-center gap-1.5"><i class="fas fa-user-circle"></i> {{ $first->author?->name }}</span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-calendar-alt"></i> {{ $first->published_at?->format('d/m/Y') }}</span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-eye"></i> {{ number_format($first->views) }}</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Sub featured --}}
                <div class="flex flex-col gap-5">
                    @foreach($featuredPosts->skip(1) as $sub)
                    <div class="featured-card flex-1" style="position:relative;min-height:212px;">
                        <img src="{{ asset('storage/' . $sub->image) }}" alt="{{ $sub->title }}"
                             class="absolute inset-0 w-full h-full object-cover" style="position:absolute;inset:0;">
                        <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(6,11,20,0.95) 0%, rgba(6,11,20,0.35) 55%, transparent 100%);"></div>
                        <div class="relative z-10 mt-auto p-5" style="display:flex;flex-direction:column;justify-content:flex-end;height:100%;">
                            <span class="inline-block mb-2 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full"
                                  style="background:rgba(234,179,8,0.12);border:1px solid rgba(234,179,8,0.3);color:#fbbf24;">
                                {{ $sub->category?->name }}
                            </span>
                            <h4 class="text-base font-bold text-white mb-2 line-clamp-2 hover:text-amber-400 transition-colors leading-snug">
                                <a href="{{ route('posts.show', $sub->slug) }}">{{ $sub->title }}</a>
                            </h4>
                            <div class="flex items-center gap-3 text-slate-500 text-xs">
                                <span>{{ $sub->published_at?->format('d/m/Y') }}</span>
                                <span class="flex items-center gap-1"><i class="fas fa-eye"></i> {{ number_format($sub->views) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ===== CATEGORY TABS ===== --}}
        <div class="mb-4">
            <div style="height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.07),transparent);margin-bottom:24px;"></div>
            <div class="flex flex-wrap items-center gap-2 mb-8 overflow-x-auto pb-1" style="-webkit-overflow-scrolling:touch;">
                <a href="{{ route('posts.index', request()->only('search')) }}"
                   class="cat-tab {{ !request('category') ? 'active' : 'inactive' }}">
                    Tất cả
                </a>
                @foreach($categories as $cat)
                    @if($cat->posts_count > 0 || request('category') === $cat->slug)
                    <a href="{{ route('posts.index', array_merge(request()->only('search'), ['category' => $cat->slug])) }}"
                       class="cat-tab {{ request('category') === $cat->slug ? 'active' : 'inactive' }}">
                        {{ $cat->name }}
                        <span class="ml-1.5 inline-flex items-center justify-center text-[10px] w-5 h-5 rounded-full"
                              style="{{ request('category') === $cat->slug ? 'background:rgba(255,255,255,0.2);color:white;' : 'background:rgba(255,255,255,0.06);color:#475569;' }}">
                            {{ $cat->posts_count }}
                        </span>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ===== MAIN 2-COLUMN LAYOUT: Articles + Sidebar ===== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">

            {{-- ===== COLUMN A: ARTICLES (2/3) ===== --}}
            <div class="xl:col-span-2">

                {{-- Feed header --}}
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div style="width:40px;height:2px;background:linear-gradient(90deg,#fbbf24,transparent);border-radius:2px;"></div>
                        <h3 class="text-lg md:text-xl font-bold text-white" id="feed-heading">
                            @if(request('category'))
                                {{ $categories->firstWhere('slug', request('category'))?->name ?? 'Danh mục' }}
                            @elseif(request('search'))
                                Kết quả: "<span class="text-amber-400">{{ request('search') }}</span>"
                            @else
                                Tin Mới Cập Nhật
                            @endif
                        </h3>
                    </div>
                    @if(request('search') || request('category'))
                    <a href="{{ route('posts.index') }}"
                       class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors border border-white/10 hover:border-white/20 px-4 py-2 rounded-full hover:bg-white/5">
                        <i class="fas fa-times text-xs"></i> Xoá bộ lọc
                    </a>
                    @endif
                </div>

                {{-- Articles Grid --}}
                @if($posts->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" id="articles-grid">
                        @foreach($posts as $post)
                        @php
                            $wordCount = str_word_count(strip_tags($post->content ?? $post->summary ?? ''));
                            $readingMin = max(1, ceil($wordCount / 200));
                        @endphp
                        <article class="article-card news-item"
                                 data-title="{{ strtolower($post->title) }}"
                                 data-summary="{{ strtolower($post->summary ?? '') }}"
                                 data-cat="{{ $post->category?->slug }}">
                            <div class="article-img-wrap">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-700">
                                        <i class="fas fa-newspaper text-4xl"></i>
                                    </div>
                                @endif
                                <span class="article-cat-badge">{{ $post->category?->name }}</span>
                            </div>
                            <div class="article-body">
                                <div class="article-meta">
                                    <span><i class="fas fa-calendar-alt me-1"></i>
                                        {{ $post->published_at ? $post->published_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}
                                    </span>
                                    <span>·</span>
                                    <span class="reading-time-badge"><i class="fas fa-clock me-1"></i>~{{ $readingMin }} phút</span>
                                    <span>·</span>
                                    <span><i class="fas fa-eye me-1"></i>{{ number_format($post->views) }}</span>
                                </div>
                                <h4 class="article-title">
                                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                                </h4>
                                <p class="article-summary">{{ $post->summary }}</p>
                                <div class="article-footer">
                                    <span class="article-author"><i class="fas fa-user-circle me-1 text-slate-600"></i>{{ $post->author?->name }}</span>
                                    <a href="{{ route('posts.show', $post->slug) }}" class="read-more-link">
                                        Đọc tiếp <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>

                    {{-- No live search result --}}
                    <div id="no-live-result">
                        <i class="fas fa-search text-5xl mb-4" style="color:rgba(255,255,255,0.1);"></i>
                        <p class="text-sm">Không tìm thấy bài viết phù hợp</p>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-12" id="pagination-wrap">
                        {{ $posts->links('pagination::tailwind') }}
                    </div>
                @else
                    <div class="empty-state text-center py-24 rounded-3xl">
                        <i class="fas fa-newspaper text-slate-700 text-7xl mb-6"></i>
                        <h4 class="text-2xl font-bold text-white mb-3">Không tìm thấy bài viết!</h4>
                        <p class="text-slate-400 text-base">Thử từ khóa khác hoặc quay lại sau để cập nhật tin mới.</p>
                        @if(request('search') || request('category'))
                        <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 mt-6 text-amber-400 hover:text-amber-300 font-medium text-sm transition-colors border border-amber-500/30 hover:border-amber-400/60 px-6 py-3 rounded-full hover:bg-amber-500/10">
                            <i class="fas fa-times text-xs"></i> Xoá bộ lọc
                        </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ===== COLUMN B: SIDEBAR (1/3) ===== --}}
            <div class="xl:col-span-1 xl:sticky xl:top-24 space-y-6">

                {{-- Popular Posts --}}
                @if($popularPosts->count() > 0)
                <div class="sidebar-card">
                    <div class="sidebar-title">
                        <i class="fas fa-fire text-orange-400"></i> Tin xem nhiều
                    </div>
                    <div>
                        @foreach($popularPosts as $i => $pop)
                        <a href="{{ route('posts.show', $pop->slug) }}" class="pop-item">
                            <span class="pop-rank">{{ $i + 1 }}</span>
                            @if($pop->image)
                                <img src="{{ asset('storage/' . $pop->image) }}" alt="{{ $pop->title }}" class="pop-img" loading="lazy">
                            @else
                                <div class="pop-img flex items-center justify-center text-slate-600">
                                    <i class="fas fa-newspaper text-xl"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="pop-title">{{ $pop->title }}</div>
                                <div class="pop-views flex items-center gap-1.5">
                                    <i class="fas fa-eye"></i>
                                    {{ number_format($pop->views) }} lượt xem
                                    @if($pop->category)
                                        <span>·</span>
                                        <span style="color:#fbbf24;font-weight:600;">{{ $pop->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Newsletter --}}
                <div class="newsletter-wrap">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:rgba(229,9,20,0.15);">
                            <i class="fas fa-envelope text-red-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="text-white font-bold text-sm">Nhận tin mới</div>
                            <div class="text-slate-500 text-xs">Đăng ký để không bỏ lỡ</div>
                        </div>
                    </div>
                    <p class="text-slate-400 text-xs mb-4 leading-relaxed">Cập nhật sự kiện & khuyến mãi từ movieGo ngay vào hộp thư của bạn.</p>
                    <input type="email" class="newsletter-input" placeholder="email@example.com">
                    <button class="btn-subscribe">
                        <i class="fas fa-paper-plane me-2"></i> Đăng ký ngay
                    </button>
                </div>

                {{-- Tags / Categories --}}
                @if($popularCategories->count() > 0)
                <div class="sidebar-card">
                    <div class="sidebar-title">
                        <i class="fas fa-hashtag text-blue-400"></i> Chủ đề
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($popularCategories as $pcat)
                        <a href="{{ route('posts.index', ['category' => $pcat->slug]) }}"
                           class="tag-pill {{ request('category') === $pcat->slug ? 'active-tag' : '' }}">
                            {{ $pcat->name }}
                            <span style="background:rgba(255,255,255,0.06);border-radius:9999px;padding:1px 7px;font-size:9px;color:#64748b;">
                                {{ $pcat->posts_count }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Back to top shortcut --}}
                <button onclick="window.scrollTo({top:0,behavior:'smooth'})"
                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-slate-400 hover:text-white text-sm font-medium transition-all"
                        style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                    <i class="fas fa-arrow-up text-xs"></i> Lên đầu trang
                </button>

            </div>{{-- end sidebar --}}
        </div>{{-- end 2-col grid --}}

    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    // =====================================================================
    // LIVE SEARCH (client-side real-time filtering)
    // =====================================================================
    const searchInput  = document.getElementById('live-search-input');
    const dropdown     = document.getElementById('live-search-dropdown');
    const clearBtn     = document.getElementById('clear-search');
    const articlesGrid = document.getElementById('articles-grid');
    const noResult     = document.getElementById('no-live-result');
    const paginWrap    = document.getElementById('pagination-wrap');

    // All posts data passed cleanly from controller
    const allPostsData = @json($allPostsData ?? []);

    let debounceTimer = null;

    function toggleClearBtn(val) {
        if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';
    }

    if (searchInput) {
        toggleClearBtn(searchInput.value);

        searchInput.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            toggleClearBtn(q);
            clearTimeout(debounceTimer);

            // Client-side filter of articles on current page
            if (articlesGrid) {
                let anyVisible = false;
                articlesGrid.querySelectorAll('.news-item').forEach(card => {
                    const title   = card.dataset.title || '';
                    const summary = card.dataset.summary || '';
                    const match   = !q || title.includes(q) || summary.includes(q);
                    card.classList.toggle('hidden-by-filter', !match);
                    if (match) anyVisible = true;
                });
                if (noResult) noResult.style.display = (q && !anyVisible) ? 'block' : 'none';
                if (paginWrap) paginWrap.style.display = q ? 'none' : '';
            }

            // Live dropdown search
            if (!q || q.length < 2) {
                if (dropdown) dropdown.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(() => renderDropdown(q), 180);
        });
    }

    function renderDropdown(q) {
        if (!dropdown) return;
        const matches = allPostsData.filter(p => p.title && p.title.toLowerCase().includes(q)).slice(0, 6);
        if (matches.length === 0) {
            dropdown.innerHTML = `<div class="ls-no-result"><i class="fas fa-search me-2"></i>Không tìm thấy bài viết phù hợp</div>`;
        } else {
            dropdown.innerHTML = matches.map(p => `
                <a href="${p.url}" class="ls-item">
                    ${p.image
                        ? `<img src="${p.image}" class="ls-item-img" alt="${p.title}">`
                        : `<div class="ls-item-img flex items-center justify-center text-slate-600"><i class="fas fa-newspaper"></i></div>`
                    }
                    <div>
                        <div class="ls-item-cat">${p.category ?? ''}</div>
                        <div class="ls-item-title">${p.title}</div>
                        <div style="font-size:10px;color:#475569;margin-top:3px;"><i class="fas fa-eye me-1"></i>${Number(p.views || 0).toLocaleString('vi-VN')} lượt xem</div>
                    </div>
                </a>
            `).join('');
        }
        dropdown.style.display = 'block';
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (dropdown && !e.target.closest('.live-search-wrap')) {
            dropdown.style.display = 'none';
        }
    });

    // Clear button
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (dropdown) dropdown.style.display = 'none';
            toggleClearBtn(false);
            if (articlesGrid) {
                articlesGrid.querySelectorAll('.news-item').forEach(c => c.classList.remove('hidden-by-filter'));
            }
            if (noResult) noResult.style.display = 'none';
            if (paginWrap) paginWrap.style.display = '';
            if (searchInput) searchInput.focus();
        });
    }

    // Close dropdown on ESC
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && dropdown) dropdown.style.display = 'none';
        });
    }

    // =====================================================================
    // NEWSLETTER SUBSCRIBE (mock)
    // =====================================================================
    const subsBtn = document.querySelector('.btn-subscribe');
    const subsInput = document.querySelector('.newsletter-input');
    if (subsBtn && subsInput) {
        subsBtn.addEventListener('click', function() {
            const email = subsInput.value.trim();
            if (!email || !/\S+@\S+\.\S+/.test(email)) {
                subsInput.style.borderColor = 'rgba(239,68,68,0.6)';
                setTimeout(() => subsInput.style.borderColor = '', 1500);
                return;
            }
            subsBtn.innerHTML = '<i class="fas fa-check me-2"></i> Đăng ký thành công!';
            subsBtn.style.background = 'linear-gradient(135deg,#16a34a,#15803d)';
            subsBtn.disabled = true;
            subsInput.value = '';
        });
    }
})();
</script>
@endpush
