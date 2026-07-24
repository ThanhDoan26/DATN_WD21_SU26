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
        padding-top: 110px;
        padding-bottom: 60px;
    }
    .page-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 50%, rgba(99,102,241,0.10) 0%, transparent 65%),
            radial-gradient(ellipse 50% 70% at 85% 30%, rgba(229,9,20,0.06) 0%, transparent 60%);
        pointer-events: none;
    }
    .hero-grid {
        position: absolute; inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.5) 40%, transparent 100%);
        pointer-events: none;
    }
    .hero-glow-blue {
        position: absolute; top: -60px; left: -60px;
        width: 480px; height: 480px;
        background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 65%);
        pointer-events: none;
        animation: glow-pulse 5s ease-in-out infinite;
    }

    /* === FILTER BAR === */
    .filter-bar-wrap {
        background: rgba(6,11,20,0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255,255,255,0.07);
        position: sticky;
        top: 80px;
        z-index: 40;
    }
    .filter-bar {
        max-width: 1280px;
        margin: 0 auto;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-search {
        position: relative;
        flex: 1; min-width: 180px;
    }
    .filter-search i {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: #475569; font-size: 13px; pointer-events: none;
    }
    .filter-search input {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        padding: 9px 14px 9px 36px;
        font-size: 13px; outline: none;
        transition: all 0.3s;
    }
    .filter-search input:focus {
        border-color: rgba(99,102,241,0.5);
        background: rgba(255,255,255,0.07);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.08);
    }
    .filter-search input::placeholder { color: rgba(255,255,255,0.3); }

    .filter-select {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        padding: 9px 32px 9px 12px;
        font-size: 13px; -webkit-appearance: none;
        cursor: pointer; outline: none;
        transition: all 0.3s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }
    .filter-select:focus {
        border-color: rgba(99,102,241,0.5);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.08);
    }
    .filter-select option { background: #0f172a; }

    .filter-divider { width: 1px; height: 24px; background: rgba(255,255,255,0.1); }

    .results-count { font-size: 12px; color: #475569; white-space: nowrap; margin-left: auto; }
    .results-count span { color: #818cf8; font-weight: 700; }

    /* === SECTION DIVIDER === */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(99,102,241,0.4), rgba(255,255,255,0.1), rgba(99,102,241,0.4), transparent);
    }

    .cinema-badge-blue {
        background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05));
        border: 1px solid rgba(99,102,241,0.35);
        backdrop-filter: blur(8px);
    }
    .stat-pill {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .stat-pill:hover { background: rgba(99,102,241,0.1); border-color: rgba(99,102,241,0.3); }

    /* === MOVIES GRID === */
    .movies-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 20px;
    }
    @media (min-width: 640px)  { .movies-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .movies-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 1280px) { .movies-grid { grid-template-columns: repeat(4, 1fr); } }

    .cinema-movie-card.hidden-by-filter { display: none !important; }

    /* Override card colors for indigo theme on upcoming */
    .page-upcoming .cinema-movie-card:hover {
        border-color: rgba(99,102,241,0.25) !important;
        box-shadow: 0 24px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(99,102,241,0.1) !important;
    }

    /* === EMPTY STATE === */
    .empty-state {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 24px;
        text-align: center;
        padding: 80px 24px;
        grid-column: 1 / -1;
    }
    #empty-state-msg { display: none; }
    #empty-state-msg.visible { display: block; }

    /* === TRAILER MODAL === */
    .trailer-modal-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.92);
        backdrop-filter: blur(12px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none;
        transition: opacity 0.35s ease;
        padding: 20px;
    }
    .trailer-modal-overlay.open { opacity: 1; pointer-events: auto; }
    .trailer-modal {
        background: #0a0f1e;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; overflow: hidden;
        width: 100%; max-width: 860px;
        transform: scale(0.92);
        transition: transform 0.35s ease;
        box-shadow: 0 40px 100px rgba(0,0,0,0.8);
    }
    .trailer-modal-overlay.open .trailer-modal { transform: scale(1); }
    .trailer-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .trailer-modal-title {
        font-size: 15px; font-weight: 700; color: white;
        display: flex; align-items: center; gap: 8px;
    }
    .trailer-close-btn {
        width: 36px; height: 36px; border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.05);
        color: white; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s;
    }
    .trailer-close-btn:hover { background: rgba(229,9,20,0.2); border-color: rgba(229,9,20,0.4); }
    .trailer-video-wrap {
        position: relative; width: 100%;
        padding-top: 56.25%; background: #000;
    }
    .trailer-video-wrap iframe {
        position: absolute; inset: 0;
        width: 100%; height: 100%; border: none;
    }

    /* === ANIMATIONS === */
    @keyframes fade-up { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    @keyframes glow-pulse { 0%,100% { opacity:0.5; transform:scale(1); } 50% { opacity:1; transform:scale(1.12); } }
    @keyframes shimmer-blue { 0% { background-position:-200% center; } 100% { background-position:200% center; } }

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
</style>
@endpush

@section('content')

<div class="page-upcoming">

{{-- ===================== HERO ===================== --}}
<section class="page-hero">
    <div class="hero-grid"></div>
    <div class="hero-glow-blue"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        <div class="anim-1 mb-5">
            <span class="cinema-badge-blue inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs uppercase tracking-[0.25em] text-indigo-300 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                Sắp ra mắt
            </span>
        </div>
        <div class="anim-2 mb-4">
            <h1 class="font-bebas uppercase leading-[0.92] tracking-wide text-white" style="font-size: clamp(3rem, 9vw, 7rem);">
                Phim <span class="shimmer-blue">Sắp Chiếu</span>
            </h1>
        </div>
        <div class="anim-3 mb-8">
            <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                Những tựa phim được mong đợi nhất sắp ra mắt tại <strong class="text-white">movieGo</strong> — đừng bỏ lỡ!
            </p>
        </div>
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

{{-- ===================== FILTER BAR ===================== --}}
<div class="filter-bar-wrap">
    <div class="filter-bar">
        {{-- Search --}}
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="search-input"
                   placeholder="Tìm tên phim sắp chiếu..."
                   value="{{ request('keyword') }}"
                   autocomplete="off">
        </div>

        <div class="filter-divider hidden sm:block"></div>

        {{-- Genre --}}
        <select id="genre-filter" class="filter-select">
            <option value="">🎭 Thể loại</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('genre_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>

        <div class="filter-divider hidden lg:block"></div>

        {{-- Sort --}}
        <select id="sort-select" class="filter-select" style="min-width:140px;">
            <option value="latest" {{ request('sort','latest') === 'latest' ? 'selected':'' }}>Mới nhất</option>
            <option value="rating" {{ request('sort','latest') === 'rating' ? 'selected':'' }}>Đánh giá cao</option>
            <option value="alpha"  {{ request('sort','latest') === 'alpha'  ? 'selected':'' }}>Tên A–Z</option>
        </select>

        {{-- Results counter --}}
        <div class="results-count hidden sm:block">
            Hiển thị <span id="visible-count">{{ $movies->count() }}</span> / {{ $movies->total() }} phim
        </div>
    </div>
</div>

{{-- ===================== MOVIES GRID ===================== --}}
<section class="py-12 px-4 sm:px-6 lg:px-8"
         style="background: linear-gradient(180deg, #060b14 0%, #0a0d1f 100%); min-height: 50vh;">
    <div class="max-w-7xl mx-auto">

        @if($movies->count() > 0)
        <div class="movies-grid" id="movies-grid">
            @foreach($movies as $movie)
            <x-movie-cinema-card :movie="$movie" type="upcoming" />
            @endforeach

            <div id="empty-state-msg">
                <i class="fas fa-search-minus text-slate-700 text-6xl mb-5"></i>
                <h3 class="text-xl font-bold text-white mb-2">Không tìm thấy phim phù hợp</h3>
                <p class="text-slate-400 mb-6">Hãy thử điều chỉnh bộ lọc của bạn.</p>
                <button onclick="resetAllFilters()"
                        class="inline-flex items-center gap-2 text-indigo-400 border border-indigo-500/30 hover:border-indigo-400 hover:bg-indigo-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                    <i class="fas fa-times"></i> Xoá tất cả bộ lọc
                </button>
            </div>
        </div>

        <div class="flex justify-center mt-12">
            {{ $movies->links('pagination::tailwind') }}
        </div>

        @else
        <div class="empty-state">
            <i class="fas fa-calendar-times text-slate-700 text-7xl mb-6"></i>
            <h3 class="text-2xl font-bold text-white mb-3">
                @if(request('keyword') || request('genre_id'))
                    Không tìm thấy phim phù hợp
                @else
                    Chưa có phim sắp chiếu
                @endif
            </h3>
            <p class="text-slate-400 text-base mb-6">Chúng tôi sẽ cập nhật lịch chiếu sớm nhất!</p>
            @if(request('keyword') || request('genre_id'))
            <a href="{{ route('movies.upcoming') }}"
               class="inline-flex items-center gap-2 text-indigo-400 border border-indigo-500/30 hover:border-indigo-400 hover:bg-indigo-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                <i class="fas fa-times"></i> Xoá bộ lọc
            </a>
            @else
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-indigo-400 border border-indigo-500/30 hover:border-indigo-400 hover:bg-indigo-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                <i class="fas fa-arrow-left text-xs"></i> Về Trang Chủ
            </a>
            @endif
        </div>
        @endif

    </div>
</section>

</div>{{-- .page-upcoming --}}

{{-- ===================== TRAILER MODAL ===================== --}}
<div id="trailer-modal" class="trailer-modal-overlay" role="dialog" aria-modal="true">
    <div class="trailer-modal">
        <div class="trailer-modal-header">
            <div class="trailer-modal-title">
                <i class="fas fa-play-circle text-indigo-400"></i>
                <span id="trailer-modal-title-text">Trailer</span>
            </div>
            <button class="trailer-close-btn" id="trailer-close" aria-label="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="trailer-video-wrap">
            <iframe id="trailer-iframe" src=""
                    allow="autoplay; encrypted-media; fullscreen"
                    allowfullscreen title="Movie Trailer">
            </iframe>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    let searchDebounce = null;

    function getActiveFilters() {
        return {
            search : document.getElementById('search-input').value.toLowerCase().trim(),
            genre  : document.getElementById('genre-filter').value,
        };
    }

    function applyFilters() {
        const f = getActiveFilters();
        const cards = document.querySelectorAll('.cinema-movie-card');
        let visible = 0;

        cards.forEach(card => {
            const title  = card.dataset.title  ?? '';
            const genres = card.dataset.genres ?? '';
            let show = true;
            if (f.search && !title.includes(f.search)) show = false;
            if (f.genre  && !genres.split(',').includes(f.genre)) show = false;
            card.classList.toggle('hidden-by-filter', !show);
            if (show) visible++;
        });

        const countEl = document.getElementById('visible-count');
        if (countEl) countEl.textContent = visible;
        const emptyMsg = document.getElementById('empty-state-msg');
        if (emptyMsg) emptyMsg.classList.toggle('visible', visible === 0);
    }

    function navigateWithParams(params) {
        const url = new URL(window.location.href);
        Object.entries(params).forEach(([key, val]) => {
            if (val) url.searchParams.set(key, val);
            else url.searchParams.delete(key);
        });
        window.location.href = url.toString();
    }

    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchDebounce);
        applyFilters();
        searchDebounce = setTimeout(() => {
            if (this.value.trim().length > 1 || this.value.trim().length === 0) {
                navigateWithParams({ keyword: this.value.trim(), sort: document.getElementById('sort-select').value });
            }
        }, 700);
    });

    document.getElementById('genre-filter').addEventListener('change', function() {
        navigateWithParams({ genre_id: this.value, sort: document.getElementById('sort-select').value });
    });

    document.getElementById('sort-select').addEventListener('change', function() {
        navigateWithParams({ sort: this.value, keyword: document.getElementById('search-input').value.trim() });
    });

    window.resetAllFilters = function() {
        document.getElementById('search-input').value = '';
        document.getElementById('genre-filter').value = '';
        document.getElementById('sort-select').value = 'latest';
        applyFilters();
    };

    // Trailer modal
    const modal    = document.getElementById('trailer-modal');
    const iframe   = document.getElementById('trailer-iframe');
    const titleEl  = document.getElementById('trailer-modal-title-text');
    const closeBtn = document.getElementById('trailer-close');

    function extractYoutubeId(url) {
        if (!url) return null;
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/shorts\/([^&\n?#]+)/
        ];
        for (const p of patterns) { const m = url.match(p); if (m) return m[1]; }
        return null;
    }

    function openTrailer(trailerUrl, movieTitle) {
        const ytId = extractYoutubeId(trailerUrl);
        iframe.src = ytId
            ? `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0&modestbranding=1`
            : (trailerUrl || '');
        if (!iframe.src) return;
        titleEl.textContent = movieTitle || 'Trailer';
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeTrailer() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        setTimeout(() => { iframe.src = ''; }, 350);
    }

    closeBtn.addEventListener('click', closeTrailer);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeTrailer(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeTrailer(); });
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-trailer');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        openTrailer(btn.dataset.trailer, btn.dataset.title);
    });

    applyFilters();
})();
</script>
@endpush
