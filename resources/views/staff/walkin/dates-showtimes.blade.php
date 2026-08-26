@extends('layouts.staff')

@section('page_title', 'Tạo Vé Tại Quầy - Chọn Suất Chiếu')

@section('content')
<div class="container-fluid px-0">
    <!-- Action Top Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4 bg-surface p-3 rounded-4 shadow-sm border border-light">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('staff.walkin.movies') }}" class="btn btn-outline-secondary rounded-pill px-4 font-sora fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Chọn Phim Khác
            </a>
            <div>
                <h4 class="mb-0 fw-extrabold text-ink font-sora"><i class="fas fa-ticket-alt text-amber me-2"></i>Bán Vé Tại Quầy POS</h4>
                <small class="text-muted">Chọn ngày chiếu và suất chiếu để mở sơ đồ ghế</small>
            </div>
        </div>
        <span class="badge bg-amber text-dark px-3 py-2 rounded-pill font-sora fw-bold">
            <i class="fas fa-building me-1"></i> {{ $cinema->name }}
        </span>
    </div>

    <!-- Movie Summary Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
        <div class="card-body p-4 d-flex align-items-center flex-wrap gap-4">
            @if($movie->poster)
                <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->title }}" class="rounded-3 shadow" style="width: 75px; height: 105px; object-fit: cover;">
            @else
                <div class="rounded-3 shadow bg-amber text-dark d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 75px; height: 105px;">
                    <i class="fas fa-film"></i>
                </div>
            @endif

            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-amber text-dark font-sora fw-bold px-2 py-1">POS WALK-IN</span>
                    <span class="text-muted small"><i class="fas fa-clock me-1 text-amber"></i>{{ $movie->duration ?? 120 }} phút</span>
                    @if($movie->age_rating)
                        <span class="badge bg-danger fw-bold">{{ $movie->age_rating }}</span>
                    @endif
                </div>
                <h3 class="fw-extrabold font-sora text-white mb-1">{{ $movie->title }}</h3>
                <p class="text-muted mb-0 small">
                    <i class="fas fa-video me-1 text-amber"></i> Đạo diễn: {{ $movie->director ?? 'N/A' }} | 
                    <i class="fas fa-tags me-1 text-amber"></i> Thể loại: {{ $movie->genre ?? 'Chiếu rạp' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Step 1: Select Date -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-surface);">
        <div class="card-body p-4">
            <h5 class="fw-extrabold font-sora text-ink mb-3 d-flex align-items-center">
                <span class="badge bg-amber text-dark rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
                Chọn Ngày Chiếu
            </h5>
            <div id="datesContainer" class="d-flex flex-wrap gap-3">
                <div class="text-center w-100 py-4">
                    <div class="spinner-border text-amber" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted small font-sora">Đang tải lịch chiếu khả dụng...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Select Showtime -->
    <div id="showtimeSection" class="d-none">
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-surface);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-light pb-3">
                    <h5 class="fw-extrabold font-sora text-ink mb-0 d-flex align-items-center">
                        <span class="badge bg-amber text-dark rounded-circle me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                        Chọn Giờ Chiếu
                    </h5>
                    <span id="selectedDateDisplay" class="badge bg-light text-ink border px-3 py-2 rounded-pill font-sora fw-bold"></span>
                </div>

                <div id="showtimesContainer" class="row g-3">
                    <!-- Showtimes will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- No Showtimes Message -->
    <div id="noShowtimesMessage" class="d-none card border-0 shadow-sm rounded-4 text-center py-5" style="background: var(--bg-surface);">
        <div class="card-body">
            <i class="fas fa-calendar-times text-muted opacity-50 mb-3" style="font-size: 3.5rem;"></i>
            <h5 class="text-muted font-sora fw-bold">Không có suất chiếu nào khả dụng cho ngày này</h5>
            <p class="text-muted small">Vui lòng chọn một ngày chiếu khác ở danh sách trên</p>
        </div>
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
                if (result.data[0]) {
                    const firstBtn = document.querySelector('.date-pos-pill');
                    if (firstBtn) firstBtn.click();
                }
            } else {
                document.getElementById('datesContainer').innerHTML = `
                    <div class="text-center w-100 py-4">
                        <i class="fas fa-calendar-times text-muted fa-3x mb-3 opacity-50"></i>
                        <p class="text-muted font-sora">Hiện tại chưa có lịch chiếu cho phim này.</p>
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
        container.innerHTML = dates.map(date => {
            const dateObj = new Date(date);
            const dayName = dateObj.toLocaleDateString('vi-VN', { weekday: 'short' });
            const dayNum = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');

            return `
                <button type="button" onclick="selectDate('${date}', this)" 
                        class="btn date-pos-pill d-flex flex-column align-items-center justify-content-center p-2 rounded-3 border shadow-sm transition-all" 
                        style="width: 110px; height: 68px; border-color: var(--border-light); background: var(--bg-surface);">
                    <span class="day-label text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.5px;">${dayName}</span>
                    <span class="date-label fw-extrabold font-sora text-amber fs-4" style="line-height: 1.1;">${dayNum}/${month}</span>
                </button>
            `;
        }).join('');
    }

    function selectDate(date, button) {
        selectedDate = date;

        document.querySelectorAll('.date-pos-pill').forEach(el => {
            el.style.background = 'var(--bg-surface)';
            el.style.borderColor = 'var(--border-light)';
            el.classList.remove('shadow-lg', 'active-pill');
            
            const dayLabel = el.querySelector('.day-label');
            const dateLabel = el.querySelector('.date-label');
            if (dayLabel) dayLabel.style.color = 'var(--text-muted)';
            if (dateLabel) dateLabel.style.color = '#d97706';
        });
        
        button.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
        button.style.borderColor = '#f59e0b';
        button.classList.add('shadow-lg', 'active-pill');

        const dayLabel = button.querySelector('.day-label');
        const dateLabel = button.querySelector('.date-label');
        if (dayLabel) dayLabel.style.color = 'rgba(255,255,255,0.85)';
        if (dateLabel) dateLabel.style.color = '#ffffff';

        loadShowtimes(date);
    }

    async function loadShowtimes(date) {
        try {
            const response = await fetch(`/api/booking/showtimes?movie_id=${movieId}&cinema_id=${cinemaId}&date=${date}`);
            const result = await response.json();

            const dateObj = new Date(date);
            document.getElementById('selectedDateDisplay').textContent = dateObj.toLocaleDateString('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' });

            if (result.data && result.data.length > 0) {
                displayShowtimes(result.data);
                document.getElementById('showtimeSection').classList.remove('d-none');
                document.getElementById('noShowtimesMessage').classList.add('d-none');
            } else {
                document.getElementById('showtimeSection').classList.add('d-none');
                document.getElementById('noShowtimesMessage').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error loading showtimes:', error);
        }
    }

    function displayShowtimes(showtimes) {
        const container = document.getElementById('showtimesContainer');
        container.innerHTML = showtimes.map(showtime => {
            const isDisabled = showtime.available_seats <= 0;
            const seatColorClass = showtime.available_seats > 20 ? 'bg-success-subtle text-success border-success-subtle' : (showtime.available_seats > 0 ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-danger-subtle text-danger border-danger-subtle');
            
            return `
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div onclick="${isDisabled ? '' : `proceedToSeats(${showtime.id})`}"
                         class="showtime-card p-3 rounded-4 border shadow-sm transition-all text-start position-relative ${isDisabled ? 'disabled-card' : 'cursor-pointer'}"
                         style="background: var(--bg-surface); border-color: var(--border-light);">
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-extrabold fs-2 font-sora text-ink">${showtime.time}</span>
                            <span class="badge bg-amber text-dark font-sora fw-bold px-2 py-1">${showtime.room_format || '2D'}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                            <span><i class="fas fa-door-open text-amber me-1"></i>${showtime.room_name}</span>
                            <span class="badge ${seatColorClass} border px-2 py-1 rounded-pill fw-bold">
                                <i class="fas fa-chair me-1"></i>${showtime.available_seats} ghế
                            </span>
                        </div>

                        <div class="pt-2 border-top border-light d-flex justify-content-between align-items-center font-sora fw-bold small text-amber">
                            <span>${isDisabled ? 'Hết ghế khả dụng' : 'Chọn sơ đồ ghế'}</span>
                            <i class="fas ${isDisabled ? 'fa-ban text-muted' : 'fa-arrow-right'}"></i>
                        </div>
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
