@extends('layouts.staff')

@section('content')
<div class="container-fluid p-4 bg-white rounded-3 shadow-sm">
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <a href="{{ route('staff.walkin.movies') }}" class="btn btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i> Trở Lại
        </a>
        <h2 class="mb-0 text-primary fw-bold"><i class="fas fa-calendar-alt me-2"></i>Chọn Suất Chiếu</h2>
    </div>

    <!-- Movie & Cinema Info -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-film fa-2x me-3 text-info"></i>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ $movie->title }}</h5>
                    <p class="mb-0 text-secondary">Rạp: <span class="fw-bold">{{ $cinema->name }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: Select Date -->
    <h4 class="fw-bold mb-3 d-flex align-items-center">
        <span class="badge bg-primary rounded-circle me-2">1</span> Chọn Ngày Chiếu
    </h4>
    <div id="datesContainer" class="d-flex flex-wrap gap-2 mb-5">
        <div class="text-center w-100 py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải danh sách ngày...</p>
        </div>
    </div>

    <!-- Step 2: Select Showtime -->
    <div id="showtimeSection" class="d-none">
        <h4 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
            <div><span class="badge bg-primary rounded-circle me-2">2</span> Chọn Giờ Chiếu</div>
            <small id="selectedDateDisplay" class="text-muted fw-normal"></small>
        </h4>
        <div id="showtimesContainer" class="row g-3">
            <!-- Showtimes will be loaded here -->
        </div>
    </div>

    <!-- No Showtimes Message -->
    <div id="noShowtimesMessage" class="d-none text-center py-5">
        <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3">Không có suất chiếu nào cho ngày này</h5>
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
            } else {
                document.getElementById('datesContainer').innerHTML = `
                    <div class="text-center w-100 py-4">
                        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Không có ngày chiếu nào.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading dates:', error);
            document.getElementById('datesContainer').innerHTML = `
                <div class="alert alert-danger">Lỗi khi tải dữ liệu ngày chiếu. Vui lòng tải lại trang.</div>
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
                <button onclick="selectDate('${date}', this)" class="btn btn-outline-primary date-btn p-3 flex-fill text-center rounded-3 fs-5 border-2 shadow-sm" style="min-width: 120px; transition: all 0.2s;">
                    <div class="fw-bold fs-3 mb-1">${dayNum}/${month}</div>
                    <div class="text-uppercase small">${dayName}</div>
                </button>
            `;
        }).join('');
    }

    function selectDate(date, button) {
        selectedDate = date;

        document.querySelectorAll('.date-btn').forEach(el => {
            el.classList.remove('btn-primary', 'text-white');
            el.classList.add('btn-outline-primary');
        });
        
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-primary', 'text-white');

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
            console.error('Error:', error);
        }
    }

    function displayShowtimes(showtimes) {
        const container = document.getElementById('showtimesContainer');
        container.innerHTML = showtimes.map(showtime => {
            const isDisabled = showtime.available_seats <= 0;
            
            return `
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <button onclick="proceedToSeats(${showtime.id})"
                            class="btn ${isDisabled ? 'btn-outline-secondary disabled' : 'btn-outline-success'} w-100 text-start p-3 h-100 border-2 rounded-3 shadow-sm"
                            ${isDisabled ? 'disabled' : ''} style="transition: all 0.2s;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fs-3 fw-bold">${showtime.time}</span>
                            <span class="badge ${isDisabled ? 'bg-secondary' : 'bg-success'} p-2">${showtime.room_format}</span>
                        </div>
                        <hr class="my-2 border-secondary">
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-dark">
                            <span><i class="fas fa-door-open me-1"></i> ${showtime.room_name}</span>
                            <span class="fw-bold ${showtime.available_seats > 10 ? 'text-success' : 'text-danger'}">
                                <i class="fas fa-chair me-1"></i> ${showtime.available_seats} ghế trống
                            </span>
                        </div>
                    </button>
                </div>
            `;
        }).join('');
    }

    function proceedToSeats(showtimeId) {
        window.location.href = `/staff/walk-in/showtime/${showtimeId}/seats`;
    }
</script>
@endsection
