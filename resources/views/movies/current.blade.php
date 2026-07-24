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
        padding-top: 110px;
        padding-bottom: 60px;
    }
    .page-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 10% 50%, rgba(229,9,20,0.12) 0%, transparent 60%),
            radial-gradient(ellipse 50% 80% at 90% 20%, rgba(229,9,20,0.06) 0%, transparent 60%);
        pointer-events: none;
    }
    .hero-grid {
        position: absolute; inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.6) 40%, transparent 100%);
        pointer-events: none;
    }
    .hero-glow {
        position: absolute; top: -80px; left: -80px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(229,9,20,0.15) 0%, transparent 65%);
        pointer-events: none;
        animation: glow-pulse 4s ease-in-out infinite;
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

    /* Search input */
    .filter-search {
        position: relative;
        flex: 1;
        min-width: 180px;
    }
    .filter-search i {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: #475569; font-size: 13px;
        pointer-events: none;
    }
    .filter-search input {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        padding: 9px 14px 9px 36px;
        font-size: 13px;
        outline: none;
        transition: all 0.3s;
    }
    .filter-search input:focus {
        border-color: rgba(229,9,20,0.5);
        background: rgba(255,255,255,0.07);
        box-shadow: 0 0 0 3px rgba(229,9,20,0.08);
    }
    .filter-search input::placeholder { color: rgba(255,255,255,0.3); }

    /* Select dropdowns */
    .filter-select {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        padding: 9px 32px 9px 12px;
        font-size: 13px;
        -webkit-appearance: none;
        cursor: pointer;
        outline: none;
        transition: all 0.3s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }
    .filter-select:focus {
        border-color: rgba(229,9,20,0.5);
        box-shadow: 0 0 0 3px rgba(229,9,20,0.08);
    }
    .filter-select option { background: #0f172a; }

    /* Filter pills (format) */
    .filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
    .filter-pill {
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.04);
        color: #94a3b8;
        transition: all 0.25s;
        white-space: nowrap;
    }
    .filter-pill.active, .filter-pill:hover {
        background: rgba(229,9,20,0.15);
        border-color: rgba(229,9,20,0.4);
        color: white;
    }
    .filter-pill.active {
        box-shadow: 0 0 0 1px rgba(229,9,20,0.3);
    }

    /* Divider */
    .filter-divider {
        width: 1px; height: 24px;
        background: rgba(255,255,255,0.1);
    }

    /* Results count */
    .results-count {
        font-size: 12px; color: #475569;
        white-space: nowrap;
        margin-left: auto;
    }
    .results-count span { color: #e50914; font-weight: 700; }

    /* === DATE TABS === */
    .date-tabs-wrap {
        background: rgba(6,11,20,0.9);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .date-tabs {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        gap: 4px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .date-tabs::-webkit-scrollbar { display: none; }
    .date-tab {
        padding: 10px 18px;
        border-bottom: 2px solid transparent;
        font-size: 13px; font-weight: 500;
        color: #475569;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.25s;
        display: flex; flex-direction: column; align-items: center; gap: 2px;
        flex-shrink: 0;
    }
    .date-tab:hover { color: #94a3b8; }
    .date-tab.active {
        color: #e50914;
        border-bottom-color: #e50914;
        font-weight: 700;
    }
    .date-tab .tab-day { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; }
    .date-tab .tab-date { font-size: 14px; font-weight: 700; }

    /* === SECTION DIVIDER === */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(229,9,20,0.4), rgba(255,255,255,0.1), rgba(229,9,20,0.4), transparent);
    }

    /* === CINEMA BADGE === */
    .cinema-badge {
        background: linear-gradient(135deg, rgba(229,9,20,0.15), rgba(229,9,20,0.05));
        border: 1px solid rgba(229,9,20,0.35);
        backdrop-filter: blur(8px);
    }

    /* === STAT PILL === */
    .stat-pill {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .stat-pill:hover { background: rgba(229,9,20,0.1); border-color: rgba(229,9,20,0.3); }

    /* === MOVIE GRID === */
    .movies-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 20px;
    }
    @media (min-width: 640px)  { .movies-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .movies-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 1280px) { .movies-grid { grid-template-columns: repeat(4, 1fr); } }

    .cinema-movie-card.hidden-by-filter {
        display: none !important;
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
    .trailer-modal-overlay.open {
        opacity: 1; pointer-events: auto;
    }
    .trailer-modal {
        background: #0a0f1e;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        overflow: hidden;
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
        cursor: pointer;
        transition: all 0.2s;
    }
    .trailer-close-btn:hover { background: rgba(229,9,20,0.2); border-color: rgba(229,9,20,0.4); }
    .trailer-video-wrap {
        position: relative; width: 100%;
        padding-top: 56.25%; /* 16:9 */
        background: #000;
    }
    .trailer-video-wrap iframe {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        border: none;
    }

    /* === ANIMATIONS === */
    @keyframes fade-up { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    @keyframes glow-pulse { 0%,100% { opacity:0.6; transform:scale(1); } 50% { opacity:1; transform:scale(1.1); } }
    @keyframes shimmer { 0% { background-position:-200% center; } 100% { background-position:200% center; } }

    .anim-1 { animation: fade-up 0.8s 0.05s ease-out both; }
    .anim-2 { animation: fade-up 0.8s 0.15s ease-out both; }
    .anim-3 { animation: fade-up 0.8s 0.25s ease-out both; }
    .anim-4 { animation: fade-up 0.8s 0.35s ease-out both; }

    .shimmer-red {
        background: linear-gradient(90deg, #fff 0%, #e50914 40%, #fff 60%, #e50914 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 4s linear infinite;
    }
</style>
@endpush

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="page-hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        <div class="anim-1 mb-5">
            <span class="cinema-badge inline-flex items-center gap-2 py-2 px-5 rounded-full text-xs uppercase tracking-[0.25em] text-red-300 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                Đang chiếu tại rạp
            </span>
        </div>
        <div class="anim-2 mb-4">
            <h1 class="font-bebas uppercase leading-[0.92] tracking-wide text-white" style="font-size: clamp(3rem, 9vw, 7rem);">
                Phim <span class="shimmer-red">Đang Chiếu</span>
            </h1>
        </div>
        <div class="anim-3 mb-8">
            <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                Những bom tấn đang được chiếu tại <strong class="text-white">movieGo</strong> — chọn suất chiếu và đặt vé ngay.
            </p>
        </div>
        <div class="anim-4 flex flex-wrap gap-3">
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-film text-red-400 text-sm"></i>
                <span class="text-white font-semibold text-sm">{{ $movies->total() }}</span>
                <span class="text-slate-400 text-xs uppercase tracking-wider">Phim đang chiếu</span>
            </div>
            <div class="stat-pill rounded-full px-5 py-2.5 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-red-400 text-sm"></i>
                <span class="text-slate-300 text-xs uppercase tracking-wider">{{ $cinemas->count() }} cụm rạp</span>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

{{-- ===================== DATE TABS ===================== --}}
<div class="date-tabs-wrap">
    <div class="date-tabs" id="date-tabs">
        <div class="date-tab active" data-date="all">
            <span class="tab-day">Tất cả</span>
            <span class="tab-date">ngày</span>
        </div>
        @php
            $locale = app()->getLocale();
            $days = ['CN','T2','T3','T4','T5','T6','T7'];
        @endphp
        @for($i = 0; $i < 7; $i++)
        @php
            $d = now()->addDays($i);
            $dayName = $i === 0 ? 'Hôm nay' : $days[$d->dayOfWeek];
        @endphp
        <div class="date-tab" data-date="{{ $d->format('Y-m-d') }}">
            <span class="tab-day">{{ $dayName }}</span>
            <span class="tab-date">{{ $d->format('d/m') }}</span>
        </div>
        @endfor
    </div>
</div>

{{-- ===================== FILTER BAR ===================== --}}
<div class="filter-bar-wrap" style="top: 80px;">
    <div class="filter-bar">
        {{-- Search --}}
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" id="search-input"
                   placeholder="Tìm tên phim..."
                   value="{{ request('keyword') }}"
                   autocomplete="off">
        </div>

        <div class="filter-divider hidden sm:block"></div>

        {{-- Cinema --}}
        <select id="cinema-filter" class="filter-select">
            <option value="">🎬 Tất cả rạp</option>
            @foreach($cinemas as $cinema)
            <option value="{{ $cinema->id }}">{{ $cinema->name }}</option>
            @endforeach
        </select>

        {{-- Genre --}}
        <select id="genre-filter" class="filter-select">
            <option value="">🎭 Thể loại</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('genre_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>

        <div class="filter-divider hidden md:block"></div>

        {{-- Format pills --}}
        <div class="filter-pills hidden md:flex" id="format-pills">
            <span class="filter-pill active" data-format="all">Tất cả</span>
            <span class="filter-pill" data-format="2D">2D</span>
            <span class="filter-pill" data-format="3D">3D</span>
            <span class="filter-pill" data-format="IMAX">IMAX</span>
        </div>

        <div class="filter-divider hidden lg:block"></div>

        {{-- Sort --}}
        <select id="sort-select" class="filter-select" style="min-width:140px;">
            <option value="latest"  {{ request('sort','latest')  === 'latest'  ? 'selected':'' }}>Mới nhất</option>
            <option value="rating"  {{ request('sort','latest')  === 'rating'  ? 'selected':'' }}>Đánh giá cao</option>
            <option value="alpha"   {{ request('sort','latest')  === 'alpha'   ? 'selected':'' }}>Tên A–Z</option>
        </select>

        {{-- Results counter --}}
        <div class="results-count hidden sm:block">
            Hiển thị <span id="visible-count">{{ $movies->count() }}</span> / {{ $movies->total() }} phim
        </div>
    </div>
</div>

{{-- ===================== MOVIES GRID ===================== --}}
<section id="movies-section"
         class="py-12 px-4 sm:px-6 lg:px-8"
         style="background: linear-gradient(180deg, #060b14 0%, #0d1525 100%); min-height: 50vh;">
    <div class="max-w-7xl mx-auto">

        @if($movies->count() > 0)
        <div class="movies-grid" id="movies-grid">
            @foreach($movies as $movie)
            <x-movie-cinema-card :movie="$movie" type="current" />
            @endforeach

            {{-- Empty state (shown by JS when all cards filtered out) --}}
            <div id="empty-state-msg">
                <i class="fas fa-search-minus text-slate-700 text-6xl mb-5"></i>
                <h3 class="text-xl font-bold text-white mb-2">Không tìm thấy phim phù hợp</h3>
                <p class="text-slate-400 mb-6">Hãy thử điều chỉnh bộ lọc của bạn.</p>
                <button onclick="resetAllFilters()"
                        class="inline-flex items-center gap-2 text-red-400 border border-red-500/30 hover:border-red-400 hover:bg-red-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                    <i class="fas fa-times"></i> Xoá tất cả bộ lọc
                </button>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center mt-12" id="pagination-wrap">
            {{ $movies->links('pagination::tailwind') }}
        </div>

        @else
        {{-- Server-side empty --}}
        <div class="empty-state">
            <i class="fas fa-film text-slate-700 text-7xl mb-6"></i>
            <h3 class="text-2xl font-bold text-white mb-3">
                @if(request('keyword') || request('genre_id'))
                    Không tìm thấy phim phù hợp
                @else
                    Chưa có phim đang chiếu
                @endif
            </h3>
            <p class="text-slate-400 text-base mb-6">
                @if(request('keyword') || request('genre_id'))
                    Hãy thử từ khóa khác hoặc xoá bộ lọc.
                @else
                    Vui lòng quay lại sau.
                @endif
            </p>
            @if(request('keyword') || request('genre_id'))
            <a href="{{ route('movies.current') }}"
               class="inline-flex items-center gap-2 text-red-400 border border-red-500/30 hover:border-red-400 hover:bg-red-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                <i class="fas fa-times"></i> Xoá bộ lọc
            </a>
            @else
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-red-400 border border-red-500/30 hover:border-red-400 hover:bg-red-500/10 px-6 py-3 rounded-full text-sm font-medium transition-all">
                <i class="fas fa-arrow-left text-xs"></i> Về Trang Chủ
            </a>
            @endif
        </div>
        @endif

    </div>
</section>

{{-- ===================== TRAILER MODAL ===================== --}}
<div id="trailer-modal" class="trailer-modal-overlay" role="dialog" aria-modal="true">
    <div class="trailer-modal" id="trailer-modal-box">
        <div class="trailer-modal-header">
            <div class="trailer-modal-title">
                <i class="fas fa-play-circle text-red-500"></i>
                <span id="trailer-modal-title-text">Trailer</span>
            </div>
            <button class="trailer-close-btn" id="trailer-close" aria-label="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="trailer-video-wrap">
            <iframe id="trailer-iframe"
                    src=""
                    allow="autoplay; encrypted-media; fullscreen"
                    allowfullscreen
                    title="Movie Trailer">
            </iframe>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    // =====================================================================
    // UTILITY
    // =====================================================================
    let searchDebounce = null;

    function getActiveFilters() {
        return {
            search : document.getElementById('search-input').value.toLowerCase().trim(),
            cinema : document.getElementById('cinema-filter').value,
            genre  : document.getElementById('genre-filter').value,
            format : document.querySelector('#format-pills .filter-pill.active')?.dataset?.format ?? 'all',
            date   : document.querySelector('#date-tabs .date-tab.active')?.dataset?.date ?? 'all',
        };
    }

    // =====================================================================
    // CARD FILTERING
    // =====================================================================
    function applyFilters() {
        const f = getActiveFilters();
        const cards = document.querySelectorAll('.cinema-movie-card');
        let visible = 0;

        cards.forEach(card => {
            const title     = card.dataset.title   ?? '';
            const genres    = card.dataset.genres   ?? '';
            const dates     = card.dataset.dates    ?? '';
            const cinemas   = card.dataset.cinemas  ?? '';
            const formats   = card.dataset.formats  ?? '';

            let show = true;

            // Search
            if (f.search && !title.includes(f.search)) show = false;

            // Genre
            if (f.genre && !genres.split(',').includes(f.genre)) show = false;

            // Cinema (client-side — filter showtime buttons, card only hidden if 0 buttons left)
            // Date
            if (f.date !== 'all' && !dates.split(',').includes(f.date)) show = false;

            // Format
            if (f.format !== 'all') {
                const formatList = formats.split(',').map(x => x.trim().toUpperCase());
                if (!formatList.includes(f.format.toUpperCase())) show = false;
            }

            card.classList.toggle('hidden-by-filter', !show);
            if (show) visible++;
        });

        // Update showtime buttons visibility per active date / cinema / format
        applyShowtimeFilters(f);

        // Counter
        const countEl = document.getElementById('visible-count');
        if (countEl) countEl.textContent = visible;

        // Empty state
        const emptyMsg = document.getElementById('empty-state-msg');
        if (emptyMsg) emptyMsg.classList.toggle('visible', visible === 0);

        // Hide pagination when client filters are active
        const paginationWrap = document.getElementById('pagination-wrap');
        if (paginationWrap) {
            const clientFiltersActive = f.cinema !== '' || f.format !== 'all' || f.date !== 'all';
            paginationWrap.style.opacity = clientFiltersActive ? '0.4' : '1';
        }
    }

    function applyShowtimeFilters(f) {
        document.querySelectorAll('.showtime-btn').forEach(btn => {
            const btnDate   = btn.dataset.date   ?? '';
            const btnCinema = btn.dataset.cinema ?? '';
            const btnFormat = btn.dataset.format ?? '';

            let show = true;
            if (f.date !== 'all' && btnDate !== f.date) show = false;
            if (f.cinema && btnCinema !== f.cinema) show = false;
            if (f.format !== 'all' && btnFormat.toUpperCase() !== f.format.toUpperCase()) show = false;

            btn.classList.toggle('hidden-by-filter', !show);
        });

        // After filtering buttons, hide showtime-day-group if all its buttons are hidden
        document.querySelectorAll('.showtime-day-group').forEach(group => {
            const btns = group.querySelectorAll('.showtime-btn');
            const anyVisible = [...btns].some(b => !b.classList.contains('hidden-by-filter'));
            group.style.display = anyVisible ? '' : 'none';
        });
    }

    // =====================================================================
    // SERVER SORT / KEYWORD (URL CHANGE)
    // =====================================================================
    function navigateWithParams(params) {
        const url = new URL(window.location.href);
        Object.entries(params).forEach(([key, val]) => {
            if (val) url.searchParams.set(key, val);
            else url.searchParams.delete(key);
        });
        window.location.href = url.toString();
    }

    // =====================================================================
    // DATE TABS
    // =====================================================================
    document.querySelectorAll('.date-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.date-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            applyFilters();
        });
    });

    // =====================================================================
    // SEARCH (debounced — server-side redirect for keyword)
    // =====================================================================
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchDebounce);
        // Client-side immediate:
        applyFilters();
        // Server-side with debounce (for pagination across all movies):
        searchDebounce = setTimeout(() => {
            if (this.value.trim().length > 1 || this.value.trim().length === 0) {
                navigateWithParams({ keyword: this.value.trim(), sort: document.getElementById('sort-select').value });
            }
        }, 700);
    });

    // =====================================================================
    // CINEMA FILTER (client-side)
    // =====================================================================
    document.getElementById('cinema-filter').addEventListener('change', applyFilters);

    // =====================================================================
    // GENRE FILTER (server-side)
    // =====================================================================
    document.getElementById('genre-filter').addEventListener('change', function() {
        navigateWithParams({ genre_id: this.value, sort: document.getElementById('sort-select').value });
    });

    // =====================================================================
    // FORMAT PILLS (client-side)
    // =====================================================================
    document.querySelectorAll('#format-pills .filter-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('#format-pills .filter-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            applyFilters();
        });
    });

    // =====================================================================
    // SORT (server-side)
    // =====================================================================
    document.getElementById('sort-select').addEventListener('change', function() {
        navigateWithParams({ sort: this.value, keyword: document.getElementById('search-input').value.trim() });
    });

    // =====================================================================
    // RESET ALL
    // =====================================================================
    window.resetAllFilters = function() {
        document.getElementById('search-input').value = '';
        document.getElementById('cinema-filter').value = '';
        document.getElementById('genre-filter').value = '';
        document.getElementById('sort-select').value = 'latest';
        document.querySelectorAll('#format-pills .filter-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('#format-pills .filter-pill[data-format="all"]')?.classList.add('active');
        document.querySelectorAll('.date-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('.date-tab[data-date="all"]')?.classList.add('active');
        applyFilters();
    };

    // =====================================================================
    // TRAILER MODAL
    // =====================================================================
    const modal      = document.getElementById('trailer-modal');
    const iframe     = document.getElementById('trailer-iframe');
    const titleEl    = document.getElementById('trailer-modal-title-text');
    const closeBtn   = document.getElementById('trailer-close');

    function extractYoutubeId(url) {
        if (!url) return null;
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/shorts\/([^&\n?#]+)/
        ];
        for (const p of patterns) {
            const m = url.match(p);
            if (m) return m[1];
        }
        return null;
    }

    function openTrailer(trailerUrl, movieTitle) {
        const ytId = extractYoutubeId(trailerUrl);
        if (ytId) {
            iframe.src = `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0&modestbranding=1`;
        } else if (trailerUrl) {
            iframe.src = trailerUrl;
        } else {
            return;
        }
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

    // Delegate: open trailer buttons
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-trailer');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        openTrailer(btn.dataset.trailer, btn.dataset.title);
    });

    // =====================================================================
    // INIT
    // =====================================================================
    applyFilters();

})();
</script>
@endpush
