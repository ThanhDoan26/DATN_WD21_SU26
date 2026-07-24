@props(['movie', 'type' => 'current'])

@php
// ---------- Age rating badge color ----------
$ageColor = match(true) {
    in_array($movie->age_rating, ['P', 'G', 'Phổ biến'])                  => 'bg-emerald-500',
    in_array($movie->age_rating, ['T13', '13+', 'PG', 'PG-13'])           => 'bg-amber-500',
    in_array($movie->age_rating, ['T16', '16+'])                           => 'bg-orange-500',
    in_array($movie->age_rating, ['T18', '18+', 'R', 'NC-17'])            => 'bg-red-600',
    default                                                                 => 'bg-slate-500',
};

// ---------- Poster URL ----------
$posterUrl = $movie->poster_url
    ? (str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url))
    : null;

// ---------- Average rating ----------
$avgRating = round($movie->reviews_avg_rating ?? 0, 1);

// ---------- Showtimes grouped by date ----------
$showtimesByDate = $movie->showtimes->groupBy(fn($s) => $s->start_time->format('Y-m-d'));

// ---------- All cinema IDs on this movie ----------
$cinemaDates = $movie->showtimes->map(fn($s) => $s->start_time->format('Y-m-d'))->unique()->filter()->values()->join(',');
$cinemaIds   = $movie->showtimes->map(fn($s) => optional($s->room?->cinema)->id)->unique()->filter()->values()->join(',');
$genreIds    = $movie->categories->pluck('id')->join(',');
$formats     = $movie->showtimes->map(fn($s) => $s->room?->format)->unique()->filter()->values()->join(',');
@endphp

<div class="cinema-movie-card"
     data-title="{{ strtolower($movie->title) }}"
     data-genres="{{ $genreIds }}"
     data-dates="{{ $cinemaDates }}"
     data-cinemas="{{ $cinemaIds }}"
     data-formats="{{ $formats }}"
     data-rating="{{ $avgRating }}">

    {{-- ===== POSTER ===== --}}
    <div class="poster-section">
        {{-- Poster image --}}
        <a href="{{ route('movies.show', $movie->id) }}" class="block w-full h-full">
            @if($posterUrl)
                <img src="{{ $posterUrl }}" alt="{{ $movie->title }}" class="poster-img">
            @else
                <div class="poster-placeholder">
                    <i class="fas fa-film text-slate-600 text-4xl"></i>
                </div>
            @endif
        </a>

        {{-- Hover overlay: darken + zoom handled by CSS --}}
        <div class="poster-overlay">
            @if($movie->trailer_url)
            {{-- Play/Trailer button --}}
            <button class="trailer-btn open-trailer"
                    data-trailer="{{ $movie->trailer_url }}"
                    data-title="{{ $movie->title }}"
                    title="Xem trailer">
                <div class="play-circle">
                    <i class="fas fa-play ml-0.5"></i>
                </div>
                <span class="play-label">Trailer</span>
            </button>
            @endif
        </div>

        {{-- Top badges --}}
        @if($movie->age_rating)
        <div class="age-badge {{ $ageColor }}">{{ $movie->age_rating }}</div>
        @endif

        @if($type === 'current')
        <div class="status-badge status-showing">
            <span class="dot"></span> Đang Chiếu
        </div>
        @else
        <div class="status-badge status-coming">
            <i class="fas fa-clock text-[9px]"></i> Sắp Chiếu
        </div>
        @endif

        {{-- Rating overlay bottom-left --}}
        @if($avgRating > 0)
        <div class="rating-overlay">
            <i class="fas fa-star text-amber-400 text-[10px]"></i>
            <span class="text-white font-bold text-xs">{{ $avgRating }}</span>
        </div>
        @endif
    </div>

    {{-- ===== BODY ===== --}}
    <div class="card-body">
        {{-- Title --}}
        <a href="{{ route('movies.show', $movie->id) }}" class="card-title-link">
            <h3 class="card-title">{{ $movie->title }}</h3>
        </a>

        {{-- Meta row --}}
        <div class="card-meta">
            @if($movie->duration)
            <span><i class="fas fa-clock"></i> {{ $movie->getDurationFormatted() }}</span>
            @endif
            @if($movie->language)
            <span><i class="fas fa-globe"></i> {{ $movie->language }}</span>
            @endif
            @if($avgRating > 0)
            <span class="rating-meta">
                <i class="fas fa-star text-amber-400"></i> {{ $avgRating }}/10
            </span>
            @endif
        </div>

        {{-- Genre tags --}}
        @if($movie->categories->count() > 0)
        <div class="genre-tags">
            @foreach($movie->categories->take(3) as $cat)
            <span class="genre-tag">{{ $cat->name }}</span>
            @endforeach
        </div>
        @endif

        {{-- ===== SHOWTIMES (current only) ===== --}}
        @if($type === 'current' && $movie->showtimes->count() > 0)
        <div class="showtimes-wrap">
            <div class="showtime-header">
                <i class="fas fa-calendar-alt"></i>
                <span>Lịch chiếu</span>
                <span class="showtime-count">{{ $movie->showtimes->count() }} suất</span>
            </div>

            {{-- Showtimes grouped by date --}}
            <div class="showtime-dates" id="showtime-dates-{{ $movie->id }}">
                @foreach($showtimesByDate as $date => $showtimes)
                @php
                    $dateObj   = \Carbon\Carbon::parse($date);
                    $isToday   = $dateObj->isToday();
                    $dayLabel  = $isToday ? 'Hôm nay' : $dateObj->locale('vi')->isoFormat('ddd D/M');
                @endphp
                <div class="showtime-day-group" data-date="{{ $date }}">
                    <div class="showtime-day-label">{{ $dayLabel }}</div>
                    <div class="showtime-buttons">
                        @foreach($showtimes->take(4) as $showtime)
                        @php
                            $cinemaName = optional($showtime->room?->cinema)->name ?? '';
                            $cinemaId   = optional($showtime->room?->cinema)->id ?? '';
                            $format     = $showtime->room?->format ?? '2D';
                        @endphp
                        <a href="{{ route('booking.select-seats', $showtime->id) }}"
                           class="showtime-btn"
                           data-date="{{ $date }}"
                           data-cinema="{{ $cinemaId }}"
                           data-format="{{ $format }}"
                           title="{{ $cinemaName }} — {{ $format }}">
                            <span class="btn-time">{{ $showtime->start_time->format('H:i') }}</span>
                            @if($format && $format !== '2D')
                            <span class="btn-format">{{ $format }}</span>
                            @endif
                        </a>
                        @endforeach
                        @if($showtimes->count() > 4)
                        <span class="showtime-more">+{{ $showtimes->count() - 4 }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CTA: Book ticket --}}
        <a href="{{ route('booking.select-cinema', $movie) }}"
           class="btn-book w-full">
            <i class="fas fa-ticket-alt"></i> Đặt Vé Ngay
        </a>

        @elseif($type === 'upcoming')
        {{-- Upcoming: show first showtime + notify btn --}}
        @if($movie->showtimes->count() > 0)
        <div class="upcoming-showtime-box">
            <i class="fas fa-calendar-check text-indigo-400 text-xs"></i>
            <div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider">Suất chiếu đầu tiên</div>
                <div class="text-slate-200 text-sm font-medium">{{ $movie->showtimes->first()->start_time->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @endif
        <button onclick="alert('Tính năng sẽ được kích hoạt khi phim có lịch chiếu.')"
                class="btn-notify w-full">
            <i class="fas fa-bell"></i> Nhận Thông Báo
        </button>
        @else
        {{-- No showtimes --}}
        <a href="{{ route('booking.select-cinema', $movie) }}" class="btn-book w-full">
            <i class="fas fa-ticket-alt"></i> Đặt Vé Ngay
        </a>
        @endif
    </div>
</div>

<style>
/* ========== CINEMA MOVIE CARD ========== */
.cinema-movie-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
}
.cinema-movie-card:hover {
    border-color: rgba(229,9,20,0.25);
    transform: translateY(-6px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(229,9,20,0.1);
}

/* ----- Poster ----- */
.poster-section {
    position: relative;
    height: 280px;
    overflow: hidden;
    background: #0a0f1e;
    flex-shrink: 0;
}
.poster-img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease, filter 0.4s ease;
}
.cinema-movie-card:hover .poster-img {
    transform: scale(1.08);
    filter: brightness(0.75);
}
.poster-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #0f172a, #1e1b4b);
}

/* ----- Overlay (hover) ----- */
.poster-overlay {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    opacity: 0;
    transition: opacity 0.35s ease;
    background: rgba(0,0,0,0.15);
}
.cinema-movie-card:hover .poster-overlay { opacity: 1; }

.trailer-btn {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    cursor: pointer; border: none; background: none; padding: 0;
}
.play-circle {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: rgba(229,9,20,0.9);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    transform: scale(0.8);
    transition: all 0.3s ease;
    box-shadow: 0 0 30px rgba(229,9,20,0.5);
}
.trailer-btn:hover .play-circle {
    transform: scale(1.1);
    background: #e50914;
}
.play-label {
    color: white; font-size: 11px; font-weight: 700;
    letter-spacing: 0.15em; text-transform: uppercase;
    text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}

/* ----- Badges ----- */
.age-badge {
    position: absolute; top: 10px; right: 10px;
    color: white; font-size: 11px; font-weight: 800;
    padding: 3px 8px; border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.status-badge {
    position: absolute; top: 10px; left: 10px;
    font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
    padding: 4px 10px; border-radius: 9999px;
    display: flex; align-items: center; gap: 5px;
    backdrop-filter: blur(8px);
}
.status-showing {
    background: rgba(16,185,129,0.15);
    border: 1px solid rgba(16,185,129,0.35);
    color: #34d399;
}
.status-showing .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #34d399; animation: ping 1s ease infinite;
}
.status-coming {
    background: rgba(99,102,241,0.15);
    border: 1px solid rgba(99,102,241,0.35);
    color: #a5b4fc;
}
.rating-overlay {
    position: absolute; bottom: 10px; left: 10px;
    background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
    border-radius: 9999px;
    padding: 3px 10px;
    display: flex; align-items: center; gap: 4px;
    border: 1px solid rgba(251,191,36,0.2);
}

/* ----- Body ----- */
.card-body { padding: 16px; display: flex; flex-direction: column; flex: 1; gap: 10px; }

.card-title-link:hover .card-title { color: #e50914; }
.card-title {
    font-weight: 700; font-size: 15px; color: white;
    line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    transition: color 0.25s;
}

.card-meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    font-size: 11px; color: #64748b;
}
.card-meta span { display: flex; align-items: center; gap: 4px; }
.card-meta i { color: #475569; }
.rating-meta { color: #fbbf24 !important; }

.genre-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.genre-tag {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    color: #94a3b8; font-size: 10px; font-weight: 600;
    padding: 3px 9px; border-radius: 9999px;
    text-transform: uppercase; letter-spacing: 0.06em;
    transition: all 0.2s;
}
.genre-tag:hover { background: rgba(229,9,20,0.12); color: #fca5a5; border-color: rgba(229,9,20,0.25); }

/* ----- Showtimes ----- */
.showtimes-wrap {
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 10px;
    margin-top: 2px;
}
.showtime-header {
    display: flex; align-items: center; gap: 6px;
    font-size: 10px; color: #e50914; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    margin-bottom: 8px;
}
.showtime-count {
    margin-left: auto;
    background: rgba(229,9,20,0.12);
    color: #fca5a5;
    padding: 2px 7px; border-radius: 9999px;
    font-size: 9px;
}
.showtime-day-group { margin-bottom: 8px; }
.showtime-day-group:last-child { margin-bottom: 0; }
.showtime-day-label {
    font-size: 9px; color: #475569; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.1em;
    margin-bottom: 5px;
}
.showtime-buttons { display: flex; flex-wrap: wrap; gap: 5px; }

.showtime-btn {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #cbd5e1; font-size: 12px; font-weight: 600;
    padding: 5px 10px; border-radius: 7px;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}
.showtime-btn:hover {
    background: rgba(229,9,20,0.2);
    border-color: rgba(229,9,20,0.45);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(229,9,20,0.25);
}
.showtime-btn.hidden-by-filter { display: none; }
.btn-time { font-weight: 700; }
.btn-format {
    font-size: 9px; font-weight: 800;
    background: rgba(229,9,20,0.2);
    color: #fca5a5; padding: 1px 5px; border-radius: 4px;
}
.showtime-more {
    font-size: 11px; color: #475569; padding: 5px 8px;
    display: flex; align-items: center;
}

/* ----- Upcoming showtime box ----- */
.upcoming-showtime-box {
    display: flex; align-items: center; gap: 10px;
    background: rgba(99,102,241,0.08);
    border: 1px solid rgba(99,102,241,0.15);
    border-radius: 8px; padding: 10px 12px;
}

/* ----- CTA Buttons ----- */
.btn-book {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(135deg, #e50914, #b0060f);
    color: white; font-weight: 700; font-size: 13px;
    padding: 11px; border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(229,9,20,0.25);
    margin-top: auto;
}
.btn-book:hover {
    background: linear-gradient(135deg, #ff1a22, #e50914);
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(229,9,20,0.4);
}

.btn-notify {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: rgba(99,102,241,0.12);
    border: 1px solid rgba(99,102,241,0.3);
    color: #a5b4fc; font-weight: 700; font-size: 13px;
    padding: 11px; border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: auto;
}
.btn-notify:hover {
    background: rgba(99,102,241,0.25);
    border-color: rgba(165,180,252,0.5);
    color: white;
    box-shadow: 0 8px 24px rgba(99,102,241,0.2);
}

@keyframes ping {
    0%   { box-shadow: 0 0 0 0 rgba(52,211,153,0.6); }
    70%  { box-shadow: 0 0 0 6px rgba(52,211,153,0); }
    100% { box-shadow: 0 0 0 0 rgba(52,211,153,0); }
}
</style>
