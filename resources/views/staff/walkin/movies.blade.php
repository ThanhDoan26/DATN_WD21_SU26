@extends('layouts.staff')

@section('title', 'Tạo vé tại quầy - Chọn Phim')
@section('page_title', 'Bán Vé Tại Quầy (POS)')

@section('extra_css')
<style>
    .pos-hero-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 24px;
        color: #ffffff;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .pos-search-input {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #ffffff !important;
        border-radius: 12px;
        padding: 12px 18px 12px 45px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .pos-search-input:focus {
        background: rgba(255, 255, 255, 0.2);
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
    }
    .pos-search-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    .pos-filter-btn {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.05);
        color: #cbd5e1;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .pos-filter-btn:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
    .pos-filter-btn.active {
        background: #f59e0b;
        color: #0f172a;
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
    }
    
    .movie-pos-card {
        background: var(--bg-surface, #ffffff);
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border-light, #e2e8f0);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }
    .movie-pos-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: #f59e0b;
    }
    .movie-poster-wrapper {
        position: relative;
        height: 280px;
        overflow: hidden;
        background: #0f172a;
    }
    .movie-poster-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .movie-pos-card:hover .movie-poster-img {
        transform: scale(1.06);
    }
    .movie-overlay-gradient {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 100%);
    }
    .movie-duration-pill {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .movie-age-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 12px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .badge-age-p { background-color: #10b981; color: #fff; }
    .badge-age-t13 { background-color: #f59e0b; color: #000; }
    .badge-age-t16 { background-color: #f97316; color: #fff; }
    .badge-age-t18 { background-color: #ef4444; color: #fff; }
    
    .movie-card-content {
        padding: 18px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .movie-title-text {
        font-family: 'Sora', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-ink, #0f172a);
        margin-bottom: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .movie-meta-info {
        font-size: 13px;
        color: var(--text-muted, #64748b);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .showtime-count-tag {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        font-weight: 700;
        font-size: 11.5px;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-select-movie {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        margin-top: auto;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
    }
    .btn-select-movie:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        transform: scale(1.02);
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.35);
    }

    .pos-empty-state {
        background: var(--bg-surface, #ffffff);
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        border: 2px dashed var(--border-light, #e2e8f0);
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    
    <!-- Hero Banner with POS Controls & Live Search -->
    <div class="pos-hero-banner">
        <div class="row align-items-center g-3">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning text-dark p-3 rounded-4 shadow-sm">
                        <i class="fas fa-ticket-alt fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 font-sora">Bán Vé Tại Quầy (POS Walk-in)</h3>
                        <p class="text-slate-300 mb-0 opacity-75 small">
                            <i class="fas fa-map-marker-alt text-warning me-1"></i> Rạp: <strong>{{ Auth::user()?->cinema?->name ?? 'Đang trực' }}</strong> &bull; Tổng <strong>{{ $movies->total() }}</strong> phim đang mở suất
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instant Search Input -->
            <div class="col-lg-6">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-warning"></i>
                    <input type="text" id="movieSearchInput" class="form-control pos-search-input" 
                           placeholder="Nhập tên phim, thể loại hoặc đạo diễn để lọc nhanh..." 
                           oninput="filterMovies()">
                </div>
            </div>
        </div>

        <!-- Filter Chips -->
        <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top border-white border-opacity-10">
            <span class="text-slate-400 small fw-bold me-1">Phân loại:</span>
            <button type="button" class="pos-filter-btn active" onclick="setAgeFilter('ALL', this)">
                Tất cả ({{ $movies->count() }})
            </button>
            <button type="button" class="pos-filter-btn" onclick="setAgeFilter('P', this)">
                🟢 P (Mọi độ tuổi)
            </button>
            <button type="button" class="pos-filter-btn" onclick="setAgeFilter('T13', this)">
                🟡 T13 (13+)
            </button>
            <button type="button" class="pos-filter-btn" onclick="setAgeFilter('T16', this)">
                🟠 T16 (16+)
            </button>
            <button type="button" class="pos-filter-btn" onclick="setAgeFilter('T18', this)">
                🔴 T18 (18+)
            </button>
        </div>
    </div>

    <!-- Movie Cards Grid -->
    @if($movies->count() > 0)
        <div class="row g-4" id="moviesGrid">
            @foreach($movies as $movie)
                @php
                    $age = $movie->age_rating ?? 'P';
                    $ageClass = match($age) {
                        'P', 'K', 'G' => 'badge-age-p',
                        'T13', '13+', 'PG' => 'badge-age-t13',
                        'T16', '16+' => 'badge-age-t16',
                        'T18', '18+', 'R' => 'badge-age-t18',
                        default => 'bg-secondary'
                    };
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 movie-card-item" 
                     data-title="{{ mb_strtolower($movie->title, 'UTF-8') }}"
                     data-director="{{ mb_strtolower($movie->director ?? '', 'UTF-8') }}"
                     data-age="{{ $age }}">
                    <div class="movie-pos-card">
                        <!-- Poster -->
                        <div class="movie-poster-wrapper">
                            @if($movie->poster_url)
                                <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" 
                                     alt="{{ $movie->title }}" 
                                     class="movie-poster-img" loading="lazy">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-secondary">
                                    <i class="fas fa-film fa-3x mb-2 opacity-50"></i>
                                    <span class="small">Chưa có poster</span>
                                </div>
                            @endif

                            <div class="movie-overlay-gradient"></div>

                            <!-- Duration Tag -->
                            <div class="movie-duration-pill">
                                <i class="fas fa-clock text-warning"></i> {{ $movie->duration ?? 120 }}'
                            </div>

                            <!-- Age Rating Badge -->
                            <span class="movie-age-badge {{ $ageClass }}">
                                {{ $movie->age_rating ?? 'P' }}
                            </span>
                        </div>
                        
                        <!-- Details -->
                        <div class="movie-card-content">
                            <h5 class="movie-title-text" title="{{ $movie->title }}">{{ $movie->title }}</h5>
                            
                            <div class="movie-meta-info">
                                @if($movie->showtimes_count ?? false)
                                    <span class="showtime-count-tag">
                                        <i class="fas fa-calendar-check"></i> {{ $movie->showtimes_count }} suất chiếu
                                    </span>
                                @endif
                                <span>{{ $movie->language ?? 'Phụ đề / Lồng tiếng' }}</span>
                            </div>
                            
                            <a href="{{ route('staff.walkin.dates', $movie->id) }}" class="btn-select-movie text-decoration-none">
                                <span>Chọn Suất Chiếu</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty Filter Results -->
        <div id="noFilterResults" class="pos-empty-state d-none my-4">
            <i class="fas fa-search text-warning fa-3x mb-3 opacity-75"></i>
            <h4 class="fw-bold mb-2">Không tìm thấy phim phù hợp</h4>
            <p class="text-muted mb-0">Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc độ tuổi.</p>
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            {{ $movies->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="pos-empty-state">
            <i class="fas fa-film text-muted fa-4x mb-3 opacity-50"></i>
            <h4 class="fw-bold mb-2">Hiện chưa có suất chiếu khả dụng</h4>
            <p class="text-muted mb-0">Vui lòng kiểm tra lịch chiếu của rạp hoặc liên hệ quản lý rạp.</p>
        </div>
    @endif
</div>
@endsection

@section('extra_js')
<script>
    let activeAge = 'ALL';

    function setAgeFilter(age, btn) {
        activeAge = age;
        document.querySelectorAll('.pos-filter-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        filterMovies();
    }

    function filterMovies() {
        const query = document.getElementById('movieSearchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.movie-card-item');
        let visibleCount = 0;

        cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const director = card.getAttribute('data-director') || '';
            const age = card.getAttribute('data-age') || '';

            const matchesQuery = query === '' || title.includes(query) || director.includes(query);
            const matchesAge = activeAge === 'ALL' || age === activeAge;

            if (matchesQuery && matchesAge) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noFilterResults');
        if (noResults) {
            if (visibleCount === 0 && cards.length > 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }
        }
    }
</script>
@endsection
