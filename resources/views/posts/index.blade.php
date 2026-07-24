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

        {{-- Search --}}
        <div class="anim-4 max-w-md">
            <form action="{{ route('posts.index') }}" method="GET" class="flex gap-2">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Tìm kiếm bài viết..."
                           class="search-input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm">
                </div>
                <button type="submit" class="btn-search-amber px-6 py-3 rounded-xl font-semibold text-sm flex items-center gap-2">
                    Tìm
                </button>
            </form>
        </div>

        {{-- Stats --}}
        <div class="anim-5 flex flex-wrap gap-3 mt-6">
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-newspaper text-amber-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">Tin tức mới mỗi ngày</span>
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
            {{-- Section Header --}}
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
                            <span class="flex items-center gap-1.5"><i class="fas fa-eye"></i> {{ $first->views }}</span>
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
                                <span class="flex items-center gap-1"><i class="fas fa-eye"></i> {{ $sub->views }}</span>
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
            <div class="flex flex-wrap items-center gap-2 mb-8">
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

        {{-- ===== FEED HEADER ===== --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div style="width:40px;height:2px;background:linear-gradient(90deg,#fbbf24,transparent);border-radius:2px;"></div>
                <h3 class="text-lg md:text-xl font-bold text-white">
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

        {{-- ===== ARTICLES GRID ===== --}}
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                <article class="article-card">
                    <div class="article-img-wrap">
                        @if($post->image)
                            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}">
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
                            <span><i class="fas fa-eye me-1"></i>{{ $post->views }} lượt xem</span>
                        </div>
                        <h4 class="article-title">
                            <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                        </h4>
                        <p class="article-summary">{{ $post->summary }}</p>
                        <div class="article-footer">
                            <span class="article-author">{{ $post->author?->name }}</span>
                            <a href="{{ route('posts.show', $post->slug) }}" class="read-more-link">
                                Đọc tiếp <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12">
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
</div>

@endsection
