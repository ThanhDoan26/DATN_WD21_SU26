@extends('layouts.staff')

@section('title', 'Chọn Suất Chiếu: ' . $movie->title)
@section('page_title', 'Chọn Suất Chiếu (POS)')

@section('extra_css')
<style>
    .pos-movie-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 20px 24px;
        color: #ffffff;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .hero-poster {
        width: 100px;
        height: 145px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    .hero-title {
        font-family: 'Sora', sans-serif;
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    
    .pos-date-card {
        background: var(--bg-surface, #ffffff);
        border: 2px solid var(--border-light, #e2e8f0);
        border-radius: 14px;
        padding: 14px 18px;
        text-align: center;
        min-width: 110px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    .pos-date-card:hover {
        border-color: #f59e0b;
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(245, 158, 11, 0.15);
    }
    .pos-date-card.active {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: #d97706;
        color: #ffffff !important;
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.35);
        transform: translateY(-4px);
    }
    .pos-date-card.active .text-muted {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    
    .showtime-card-pos {
        background: var(--bg-surface, #ffffff);
        border: 1.5px solid var(--border-light, #e2e8f0);
        border-radius: 14px;
        padding: 18px;
        transition: all 0.25s ease;
        height: 100%;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .showtime-card-pos:hover:not(.disabled) {
        border-color: #10b981;
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(16, 185, 129, 0.18);
    }
    .showtime-card-pos.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f1f5f9;
    }
    .showtime-time-lg {
        font-family: 'Sora', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
    }
    .dark-theme .showtime-time-lg {
        color: #f8fafc;
    }
    .room-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Header Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('staff.walkin.movies') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Chọn Phim Khác
        </a>
        <div class="text-muted small">
            <i class="fas fa-map-marker-alt text-warning me-1"></i> Rạp: <strong class="text-dark">{{ $cinema->name }}</strong>
        </div>
    </div>

    <!-- Movie Hero Info Card -->
    <div class="pos-movie-hero">
        <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
            @if($movie->poster_url)
                <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" 
                     alt="{{ $movie->title }}" class="hero-poster">
            @else
                <div class="hero-poster bg-secondary d-flex align-items-center justify-content-center text-white">
                    <i class="fas fa-film fa-2x opacity-50"></i>
                </div>
            @endif

            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    @php
                        $age = $movie->age_rating ?? 'P';
                        $ageClass = match($age) {
                            'P', 'K', 'G' => 'bg-success',
                            'T13', '13+', 'PG' => 'bg-warning text-dark',
                            'T16', '16+' => 'bg-orange text-white',
                            'T18', '18+', 'R' => 'bg-danger text-white',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $ageClass }} fw-bold px-2 py-1">{{ $movie->age_rating ?? 'P' }}</span>
                    <span class="text-slate-300 small"><i class="fas fa-clock text-warning me-1"></i>{{ $movie->duration }} phút</span>
                    <span class="text-slate-300 small">&bull; {{ $movie->language ?? 'Tiếng Việt' }}</span>
                </div>
                <h3 class="hero-title">{{ $movie->title }}</h3>
                <p class="text-slate-300 small mb-0 opacity-80">
                    <i class="fas fa-video text-warning me-1"></i> Đạo diễn: {{ $movie->director ?? 'Đang cập nhật' }} &bull; Diễn viên: {{ $movie->cast ?? 'Đang cập nhật' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Step 1: Select Date -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3 d-flex align-items-center text-dark">
            <span class="badge bg-warning text-dark rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
            Chọn Ngày Chiếu
        </h5>
        
        <div id="datesContainer" class="d-flex flex-wrap gap-3">
            <div class="text-center w-100 py-4">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted small">Đang tải danh sách ngày chiếu...</p>
            </div>
        </div>
    </div>

    <!-- Step 2: Select Showtime -->
    <div id="showtimeSection" class="card border-0 shadow-sm rounded-4 p-4 d-none">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 pb-3 border-bottom">
            <h5 class="fw-bold mb-0 d-flex align-items-center text-dark">
                <span class="badge bg-success text-white rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                Chọn Giờ Chiếu & Phòng Chiếu
            </h5>
            <span id="selectedDateDisplay" class="badge bg-light text-dark fs-6 px-3 py-2 border"></span>
        </div>

        <div id="showtimesContainer" class="row g-3">
            <!-- Showtimes will be rendered here -->
        </div>
    </div>

    <!-- No Showtimes State -->
    <div id="noShowtimesMessage" class="d-none text-center py-5 card border-0 shadow-sm rounded-4">
        <i class="fas fa-calendar-times text-warning fa-3x mb-3 opacity-75"></i>
        <h5 class="fw-bold mb-1">Không có suất chiếu cho ngày này</h5>
        <p class="text-muted small">Vui lòng chọn ngày chiếu khác để tiếp tục đặt vé.</p>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    const movieId = {{ $movie->id }};
    const cinemaId = {{ $cinema->id }};
    let selectedDate = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (movieId && cinemaId) {
            loadDates();
        }
    });

    async function loadDates() {
        try {
            const response = await fetch(`/api/booking/dates?movie_id=${movieId}&cinema_id=${cinemaId}`);
            const result = await response.json();

            if (result.data && result.data.length > 0) {
                displayDates(result.data);
                // Auto-select first date
                const firstBtn = document.querySelector('.pos-date-card');
                if (firstBtn) {
                    firstBtn.click();
                }
            } else {
                document.getElementById('datesContainer').innerHTML = `
                    <div class="text-center w-100 py-4">
                        <i class="fas fa-calendar-times text-muted fa-2x mb-2"></i>
                        <p class="text-muted">Không có ngày chiếu khả dụng.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading dates:', error);
            document.getElementById('datesContainer').innerHTML = `
                <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-triangle me-2"></i>Lỗi kết nối khi tải lịch chiếu. Vui lòng thử lại.</div>
            `;
        }
    }

    function displayDates(dates) {
        const container = document.getElementById('datesContainer');
        const todayStr = new Date().toISOString().split('T')[0];

        container.innerHTML = dates.map(date => {
            const dateObj = new Date(date);
            const dayName = dateObj.toLocaleDateString('vi-VN', { weekday: 'short' });
            const dayNum = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const isToday = date === todayStr;

            return `
                <div onclick="selectDate('${date}', this)" class="pos-date-card">
                    <div class="text-uppercase small fw-bold text-muted mb-1">${isToday ? '★ Hôm nay' : dayName}</div>
                    <div class="fs-4 fw-bold font-sora">${dayNum}/${month}</div>
                </div>
            `;
        }).join('');
    }

    function selectDate(date, element) {
        selectedDate = date;

        document.querySelectorAll('.pos-date-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        loadShowtimes(date);
    }

    async function loadShowtimes(date) {
        const container = document.getElementById('showtimesContainer');
        container.innerHTML = `
            <div class="text-center w-100 py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 text-muted small">Đang tải danh sách suất chiếu...</p>
            </div>
        `;
        document.getElementById('showtimeSection').classList.remove('d-none');
        document.getElementById('noShowtimesMessage').classList.add('d-none');

        try {
            const response = await fetch(`/api/booking/showtimes?movie_id=${movieId}&cinema_id=${cinemaId}&date=${date}`);
            const result = await response.json();

            const dateObj = new Date(date);
            document.getElementById('selectedDateDisplay').innerHTML = `
                <i class="fas fa-calendar-check text-success me-2"></i> ${dateObj.toLocaleDateString('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' })}
            `;

            if (result.data && result.data.length > 0) {
                displayShowtimes(result.data);
                document.getElementById('showtimeSection').classList.remove('d-none');
                document.getElementById('noShowtimesMessage').classList.add('d-none');
            } else {
                document.getElementById('showtimeSection').classList.add('d-none');
                document.getElementById('noShowtimesMessage').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = `<div class="alert alert-danger">Lỗi khi tải suất chiếu.</div>`;
        }
    }

    function displayShowtimes(showtimes) {
        const container = document.getElementById('showtimesContainer');
        container.innerHTML = showtimes.map(showtime => {
            const isSoldOut = showtime.available_seats <= 0;
            const isFewSeats = showtime.available_seats > 0 && showtime.available_seats <= 10;
            
            return `
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div onclick="${isSoldOut ? '' : `proceedToSeats(${showtime.id})`}" 
                         class="showtime-card-pos ${isSoldOut ? 'disabled' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="showtime-time-lg">${showtime.time}</span>
                            <span class="badge ${showtime.room_format === 'IMAX' ? 'bg-danger' : (showtime.room_format === '3D' ? 'bg-primary' : 'bg-secondary')} room-badge">
                                ${showtime.room_format}
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom my-2 text-muted small">
                            <span><i class="fas fa-door-open me-1 text-primary"></i> ${showtime.room_name}</span>
                            <span class="fw-bold ${isSoldOut ? 'text-danger' : (isFewSeats ? 'text-warning' : 'text-success')}">
                                <i class="fas fa-chair me-1"></i> ${isSoldOut ? 'Hết vé' : `${showtime.available_seats} ghế trống`}
                            </span>
                        </div>

                        <button class="btn btn-sm ${isSoldOut ? 'btn-secondary disabled' : 'btn-success'} w-100 fw-bold py-2 rounded-3 mt-2" 
                                ${isSoldOut ? 'disabled' : ''}>
                            ${isSoldOut ? 'Đã Hết Chỗ' : 'Chọn Ghế Ngay &rarr;'}
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function proceedToSeats(showtimeId) {
        window.location.href = `/staff/walk-in/showtime/${showtimeId}/seats`;
    }
</script>

<style>
    .date-pos-pill {
        cursor: pointer;
        user-select: none;
    }
    .date-pos-pill:hover:not(.active-pill) {
        transform: translateY(-2px);
        border-color: #f59e0b !important;
    }
    .showtime-card {
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .showtime-card.cursor-pointer:hover {
        transform: translateY(-4px);
        border-color: #f59e0b !important;
        box-shadow: 0 10px 25px rgba(245, 158, 11, 0.15) !important;
    }
    .disabled-card {
        opacity: 0.5;
        cursor: not-allowed !allowed;
    }
</style>
@endsection
