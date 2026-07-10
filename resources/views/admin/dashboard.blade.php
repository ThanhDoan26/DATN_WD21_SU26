@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-users" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalActiveUsers ?? 0) }}</div>
            <div class="stat-label">Người dùng</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-video" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ number_format($totalMovies ?? 0) }}</div>
            <div class="stat-label">Phim</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-building" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalCinemas ?? 0) }}</div>
            <div class="stat-label">Cụm rạp</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <i class="fas fa-calendar-alt" style="font-size: 2rem; color: #2a5298;"></i>
            <div class="stat-number">{{ number_format($totalShowtimes ?? 0) }}</div>
            <div class="stat-label">Suất chiếu</div>
        </div>
    </div>

    <div class="col-md-3 mt-4">
        <div class="stat-box">
            <i class="fas fa-ticket-alt" style="font-size: 2rem; color: #1e3c72;"></i>
            <div class="stat-number">{{ number_format($totalTicketsSold ?? 0) }}</div>
            <div class="stat-label">Vé đã bán</div>
        </div>
    </div>
    <div class="col-md-3 mt-4">
        <div class="stat-box">
            <i class="fas fa-dollar-sign" style="font-size: 2rem; color: #0f766e;"></i>
            <div class="stat-number">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} ₫</div>
            <div class="stat-label">Doanh thu tổng</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <h5 class="mb-0 text-primary"><i class="fas fa-chart-line"></i> Thống kê doanh thu chi tiết</h5>
                    <small class="text-muted">
                        @if($selectedCinemaId && $cinemas->firstWhere('id', $selectedCinemaId))
                            Cụm rạp: <strong>{{ $cinemas->firstWhere('id', $selectedCinemaId)->name }}</strong> |
                        @endif
                        Tháng: {{ str_pad($selectedMonth ?? now()->month, 2, '0', STR_PAD_LEFT) }} / Năm: {{ $selectedYear ?? now()->year }}
                    </small>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex flex-wrap gap-2">
                    <div class="input-group">
                        <label class="input-group-text" for="filter-cinema">Cụm rạp</label>
                        <select id="filter-cinema" name="cinema_id" class="form-select">
                            <option value="">Tất cả cụm rạp</option>
                            @foreach($cinemas ?? [] as $cinema)
                                <option value="{{ $cinema->id }}" @if(($selectedCinemaId ?? null) == $cinema->id) selected @endif>{{ $cinema->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-group-text" for="filter-month">Tháng</label>
                        <select id="filter-month" name="month" class="form-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @if(($selectedMonth ?? now()->month) == $m) selected @endif>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-group-text" for="filter-year">Năm</label>
                        <select id="filter-year" name="year" class="form-select">
                            @php
                                $currentYear = now()->year;
                            @endphp
                            @foreach(range($currentYear, $currentYear - 10) as $y)
                                <option value="{{ $y }}" @if(($selectedYear ?? $currentYear) == $y) selected @endif>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-box">
                            <i class="fas fa-dollar-sign" style="font-size: 2rem; color: #0f766e;"></i>
                            <div class="stat-number">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} ₫</div>
                            <div class="stat-label">Tổng doanh thu</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-box">
                            <i class="fas fa-calendar-day" style="font-size: 2rem; color: #16a34a;"></i>
                            <div class="stat-number">{{ number_format($dailyRevenue ?? 0, 0, ',', '.') }} ₫</div>
                            <div class="stat-label">Doanh thu hôm nay</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-box">
                            <i class="fas fa-calendar-alt" style="font-size: 2rem; color: #0ea5e9;"></i>
                            <div class="stat-number">{{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }} ₫</div>
                            <div class="stat-label">Doanh thu tháng</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-box">
                            <i class="fas fa-calendar" style="font-size: 2rem; color: #7c3aed;"></i>
                            <div class="stat-number">{{ number_format($yearlyRevenue ?? 0, 0, ',', '.') }} ₫</div>
                            <div class="stat-label">Doanh thu năm</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title"><i class="fas fa-info-circle"></i> Welcome to Admin Panel</h5>
        <p class="card-text">
            Hệ thống Quản lý Đặt vé Xem phim. Sử dụng menu bên trái để quản lý:
        </p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong><i class="fas fa-building"></i> Cinemas</strong> - Quản lý cụm rạp chiếu phim
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-door-open"></i> Rooms</strong> - Quản lý phòng chiếu
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-chair"></i> Seats</strong> - Quản lý sơ đồ ghế ngồi
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-video"></i> Movies</strong> - Quản lý danh sách phim
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-calendar-alt"></i> Showtimes</strong> - Quản lý lịch chiếu
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-ticket-alt"></i> Bookings</strong> - Quản lý đơn hàng
            </li>
            <li class="list-group-item">
                <strong><i class="fas fa-users"></i> Users</strong> - Quản lý người dùng
            </li>
        </ul>
    </div>
</div>

<!-- Top 5 Combos Widget -->
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 text-primary"><i class="fas fa-star text-warning"></i> Top 5 Combo được yêu thích nhất</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center">Hạng</th>
                        <th>Tên Combo</th>
                        <th width="150" class="text-center">Điểm TB</th>
                        <th width="150" class="text-center">Lượt ĐG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCombos ?? [] as $index => $combo)
                    <tr>
                        <td class="text-center">
                            @if($index == 0)
                                <i class="fas fa-medal text-warning fs-4"></i>
                            @elseif($index == 1)
                                <i class="fas fa-medal text-secondary fs-4"></i>
                            @elseif($index == 2)
                                <i class="fas fa-medal" style="color: #cd7f32; font-size: 1.5rem;"></i>
                            @else
                                <span class="fw-bold text-muted">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $combo->name }}</td>
                        <td class="text-center text-warning fw-bold">
                            {{ number_format($combo->average_rating, 1) }} <i class="fas fa-star small"></i>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">{{ $combo->total_reviews }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">Chưa có đánh giá nào cho các Combo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Revenue Transactions Table -->
<div class="card mt-4" id="revenue-details-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 text-primary"><i class="fas fa-list-alt text-success me-2"></i> Chi tiết giao dịch doanh thu</h5>
        <div class="btn-group">
            <button onclick="exportTableToCSV('bao-cao-doanh-thu.csv')" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Tải về CSV
            </button>
            <button onclick="printReport()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-print me-1"></i> In báo cáo
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="revenue-details-table">
                <thead class="table-light">
                    <tr>
                        <th width="60" class="text-center">STT</th>
                        <th>Thời gian</th>
                        <th>Mã hóa đơn</th>
                        <th>Cụm rạp</th>
                        <th>Phim</th>
                        <th>Phòng chiếu</th>
                        <th class="text-center">Số vé</th>
                        <th>Hình thức thanh toán</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailedBookings ?? [] as $index => $booking)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $booking->payment_time ? $booking->payment_time->format('d/m/Y H:i') : $booking->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="fw-bold text-primary">{{ $booking->booking_code }}</span></td>
                        <td>{{ $booking->showtime->room->cinema->name ?? 'N/A' }}</td>
                        <td>{{ $booking->showtime->movie->title ?? 'N/A' }}</td>
                        <td>{{ $booking->showtime->room->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $booking->bookedSeats->count() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $booking->payment_method ?? 'Khác' }}</span>
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ number_format($booking->total_price, 0, ',', '.') }} ₫
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">Không có giao dịch nào phù hợp với bộ lọc</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('extra_css')
<style>
    @media print {
        /* Hide all page content except the detailed report card */
        body * {
            visibility: hidden;
            background: none !important;
            box-shadow: none !important;
        }
        #revenue-details-card, #revenue-details-card * {
            visibility: visible;
        }
        #revenue-details-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        #revenue-details-card .card-header .btn-group {
            display: none !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }
    }
</style>
@endsection

<script>
function printReport() {
    window.print();
}

function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#revenue-details-table tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            // Clean inner text
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").trim();
            // Escape double quotes
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(","));
    }

    // Include the UTF-8 BOM so Excel opens it with the correct encoding
    var csvContent = "\ufeff" + csv.join("\n");
    var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    var link = document.createElement("a");
    if (link.download !== undefined) {
        var url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>
@endsection
