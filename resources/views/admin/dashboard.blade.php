@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-info shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-users text-info fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalActiveUsers ?? 0 }}">0</div>
                <div class="stat-card-label text-muted small fw-semibold">Người dùng</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-primary shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-video text-primary fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalMovies ?? 0 }}">0</div>
                <div class="stat-card-label text-muted small fw-semibold">Phim</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-success shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-building text-success fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalCinemas ?? 0 }}">0</div>
                <div class="stat-card-label text-muted small fw-semibold">Cụm rạp</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-warning shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-calendar-alt text-warning fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalShowtimes ?? 0 }}">0</div>
                <div class="stat-card-label text-muted small fw-semibold">Suất chiếu</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-danger shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-ticket-alt text-danger fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalTicketsSold ?? 0 }}">0</div>
                <div class="stat-card-label text-muted small fw-semibold">Vé đã bán</div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="stat-card stat-card-secondary shadow-sm">
            <div class="stat-card-body p-3 text-center">
                <i class="fas fa-chart-line text-secondary fs-3 animate-icon"></i>
                <div class="stat-card-number fw-bold text-dark fs-4 mt-2 count-number" data-value="{{ $totalRevenue ?? 0 }}" data-is-money="true">0 đ</div>
                <div class="stat-card-label text-muted small fw-semibold">Doanh thu tổng</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Filters Group -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center bg-white p-3 rounded-3 shadow-sm border border-light">
            <span class="text-muted fw-bold me-2"><i class="fas fa-bolt text-warning"></i> Lọc nhanh:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="today">Hôm nay</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="yesterday">Hôm qua</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="last7">7 ngày gần nhất</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="last30">30 ngày gần nhất</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="thisMonth">Tháng này</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="lastMonth">Tháng trước</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter-btn" data-type="thisYear">Năm nay</button>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card filter-card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h5 class="card-title text-primary fw-bold mb-4">
            <i class="fas fa-filter me-2 text-primary"></i>BÁO CÁO DOANH THU
        </h5>
        <form id="dashboard-filter-form" class="row g-3">
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label fw-bold text-muted small" for="filter-cinema">Cụm rạp</label>
                <select id="filter-cinema" name="cinema_id" class="form-select border-2">
                    <option value="">Tất cả cụm rạp</option>
                    @foreach($cinemas ?? [] as $cinema)
                        <option value="{{ $cinema->id }}" @if(($selectedCinemaId ?? null) == $cinema->id) selected @endif>{{ $cinema->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label fw-bold text-muted small" for="filter-report-type">Loại báo cáo</label>
                <select id="filter-report-type" name="report_type" class="form-select border-2 text-dark fw-semibold">
                    <option value="date" @if(($selectedReportType ?? 'month') == 'date') selected @endif>Theo ngày</option>
                    <option value="week" @if(($selectedReportType ?? 'month') == 'week') selected @endif>Theo tuần</option>
                    <option value="month" @if(($selectedReportType ?? 'month') == 'month') selected @endif>Theo tháng</option>
                    <option value="year" @if(($selectedReportType ?? 'month') == 'year') selected @endif>Theo năm</option>
                </select>
            </div>

            <!-- Từ ngày / Đến ngày -->
            <div class="col-12 col-sm-6 col-md-6 col-lg-3 filter-group" id="group-from-date">
                <label class="form-label fw-bold text-muted small" for="filter-from-date">Từ ngày</label>
                <input type="date" id="filter-from-date" name="from_date" value="{{ $fromDate ?? '' }}" class="form-control border-2">
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3 filter-group" id="group-to-date">
                <label class="form-label fw-bold text-muted small" for="filter-to-date">Đến ngày</label>
                <input type="date" id="filter-to-date" name="to_date" value="{{ $toDate ?? '' }}" class="form-control border-2">
            </div>

            <!-- Tuần -->
            <div class="col-12 col-md-6 col-lg-3 filter-group" id="group-week">
                <label class="form-label fw-bold text-muted small" for="filter-week">Tuần</label>
                <select id="filter-week" name="week" class="form-select border-2">
                    @foreach(range(1, 53) as $w)
                        <option value="{{ $w }}" @if(($selectedWeek ?? now()->weekOfYear) == $w) selected @endif>Tuần {{ str_pad($w, 2, '0', STR_PAD_LEFT) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tháng -->
            <div class="col-12 col-md-6 col-lg-3 filter-group" id="group-month">
                <label class="form-label fw-bold text-muted small" for="filter-month">Tháng</label>
                <select id="filter-month" name="month" class="form-select border-2">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @if(($selectedMonth ?? now()->month) == $m) selected @endif>Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Năm -->
            <div class="col-12 col-md-6 col-lg-3 filter-group" id="group-year">
                <label class="form-label fw-bold text-muted small" for="filter-year">Năm</label>
                <select id="filter-year" name="year" class="form-select border-2">
                    @php
                        $currentYear = now()->year;
                    @endphp
                    @foreach(range($currentYear, $currentYear - 10) as $y)
                        <option value="{{ $y }}" @if(($selectedYear ?? $currentYear) == $y) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end mt-4 pt-2">
                <button type="button" id="btn-reset" class="btn btn-outline-secondary px-4 fw-bold"><i class="fas fa-sync-alt me-2"></i>Đặt lại</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-check me-2"></i>Áp dụng</button>
            </div>
        </form>
    </div>
</div>

<!-- Dashboard Data Output Wrapper -->
<div class="position-relative" id="dashboard-content-wrapper">
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(255,255,255,0.7); z-index: 1050; border-radius: 10px;">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span class="text-primary fw-bold fs-5">Đang tải báo cáo...</span>
    </div>

    <!-- ĐANG XEM BÁO CÁO HEADER -->
    <div class="card mb-4 overflow-hidden">
        <div class="card-body p-4">
            <h6 class="text-uppercase text-muted fw-bold mb-1 small" style="letter-spacing: 0.5px;">Đang xem báo cáo</h6>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h3 class="fw-bold text-dark mb-0 d-inline-block">Doanh thu</h3>
                    <span class="badge bg-primary fs-6 px-3 py-2 ms-2 align-middle" id="header-report-type">
                        @if(($selectedReportType ?? 'month') == 'date') Theo ngày
                        @elseif(($selectedReportType ?? 'month') == 'week') Theo tuần
                        @elseif(($selectedReportType ?? 'month') == 'month') Theo tháng
                        @elseif(($selectedReportType ?? 'month') == 'year') Theo năm
                        @endif
                    </span>
                </div>
                <div class="text-md-end">
                    <h5 class="fw-bold text-primary mb-1" id="header-cinema">
                        @if($selectedCinemaId && $cinemas->firstWhere('id', $selectedCinemaId))
                            {{ $cinemas->firstWhere('id', $selectedCinemaId)->name }}
                        @else
                            Tất cả cụm rạp
                        @endif
                    </h5>
                    <p class="text-muted mb-0 fw-semibold" id="header-time">
                        @if(($selectedReportType ?? 'month') == 'date')
                            {{ Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} ↓ {{ Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                        @elseif(($selectedReportType ?? 'month') == 'week')
                            Tuần {{ str_pad($selectedWeek ?? now()->weekOfYear, 2, '0', STR_PAD_LEFT) }} / Năm {{ $selectedYear ?? now()->year }}
                        @elseif(($selectedReportType ?? 'month') == 'month')
                            Tháng {{ str_pad($selectedMonth ?? now()->month, 2, '0', STR_PAD_LEFT) }} / Năm {{ $selectedYear ?? now()->year }}
                        @elseif(($selectedReportType ?? 'month') == 'year')
                            Năm {{ $selectedYear ?? now()->year }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Card Doanh Thu -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Tổng doanh thu -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-primary shadow-sm h-100">
                <div class="stat-card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-card-label text-uppercase text-muted fw-bold small">Tổng doanh thu</div>
                        <div class="stat-card-number fw-extrabold text-primary fs-3 mt-1 count-number" data-value="{{ $totalRevenue ?? 0 }}">0 đ</div>
                    </div>
                    <div class="stat-card-icon bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="fas fa-dollar-sign fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Doanh thu hôm nay -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-success shadow-sm h-100">
                <div class="stat-card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-card-label text-uppercase text-muted fw-bold small">Doanh thu hôm nay</div>
                        <div class="stat-card-number fw-extrabold text-success fs-3 mt-1 count-number" data-value="{{ $dailyRevenue ?? 0 }}">0 đ</div>
                    </div>
                    <div class="stat-card-icon bg-success-light text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="fas fa-calendar-day fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Doanh thu kỳ chọn -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-info shadow-sm h-100">
                <div class="stat-card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-card-label text-uppercase text-muted fw-bold small" id="period-card-label">
                            @if(($selectedReportType ?? 'month') == 'date') Doanh thu kỳ chọn (Theo ngày)
                            @elseif(($selectedReportType ?? 'month') == 'week') Doanh thu kỳ chọn (Theo tuần)
                            @elseif(($selectedReportType ?? 'month') == 'month') Doanh thu kỳ chọn (Theo tháng)
                            @elseif(($selectedReportType ?? 'month') == 'year') Doanh thu kỳ chọn (Theo năm)
                            @endif
                        </div>
                        <div class="stat-card-number fw-extrabold text-info fs-3 mt-1 count-number" id="period-card-number" data-value="{{ $periodRevenue ?? 0 }}">0 đ</div>
                    </div>
                    <div class="stat-card-icon bg-info-light text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="fas fa-chart-bar fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4: Doanh thu năm -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-warning shadow-sm h-100">
                <div class="stat-card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-card-label text-uppercase text-muted fw-bold small">Doanh thu năm ({{ $selectedYear ?? now()->year }})</div>
                        <div class="stat-card-number fw-extrabold text-warning fs-3 mt-1 count-number" id="year-card-number" data-value="{{ $yearlyRevenue ?? 0 }}">0 đ</div>
                    </div>
                    <div class="stat-card-icon bg-warning-light text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="fas fa-calendar-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables row 1: Top Movies & Top Combo -->
    <div class="row">
        <!-- Top Movies -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-film text-danger me-2"></i>Top 5 Phim bán chạy</h5>
                </div>
                <div class="card-body p-0" id="top-movies-wrapper">
                    @include('admin.partials.top_movies')
                </div>
            </div>
        </div>

        <!-- Top Combo -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-star text-warning me-2"></i>Top 5 Combo được yêu thích</h5>
                </div>
                <div class="card-body p-0" id="top-combos-wrapper">
                    @include('admin.partials.top_combos')
                </div>
            </div>
        </div>
    </div>

    <!-- Tables row 2: Detailed Transactions -->
    <div class="row">
        <!-- Detailed Transactions -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm h-100" id="revenue-details-card">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-list-alt text-success me-2"></i>Chi tiết giao dịch doanh thu</h5>
                    <div class="btn-group">
                        <button type="button" onclick="exportTableToCSV('bao-cao-doanh-thu.csv')" class="btn btn-outline-success btn-sm fw-bold">
                            <i class="fas fa-file-excel me-1"></i>Tải về CSV
                        </button>
                        <button type="button" onclick="printReport()" class="btn btn-outline-primary btn-sm fw-bold">
                            <i class="fas fa-print me-1"></i>In báo cáo
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" id="revenue-table-wrapper">
                    @include('admin.partials.revenue_table')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4">
        <h5 class="card-title text-primary fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Welcome to Admin Panel</h5>
        <p class="card-text text-muted">
            Hệ thống Quản lý Đặt vé Xem phim. Sử dụng menu bên trái để quản lý:
        </p>
        <ul class="list-group list-group-flush mt-2">
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-building text-primary me-2" style="width: 20px;"></i> Cinemas</strong> - Quản lý cụm rạp chiếu phim
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-door-open text-primary me-2" style="width: 20px;"></i> Rooms</strong> - Quản lý phòng chiếu
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-chair text-primary me-2" style="width: 20px;"></i> Seats</strong> - Quản lý sơ đồ ghế ngồi
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-video text-primary me-2" style="width: 20px;"></i> Movies</strong> - Quản lý danh sách phim
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-calendar-alt text-primary me-2" style="width: 20px;"></i> Showtimes</strong> - Quản lý lịch chiếu
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-ticket-alt text-primary me-2" style="width: 20px;"></i> Bookings</strong> - Quản lý đơn hàng
            </li>
            <li class="list-group-item text-muted border-light px-0">
                <strong><i class="fas fa-users text-primary me-2" style="width: 20px;"></i> Users</strong> - Quản lý người dùng
            </li>
        </ul>
    </div>
</div>

@endsection

@section('extra_css')
<style>
    /* Giao diện bộ lọc nâng cao */
    .filter-card {
        border-radius: 12px !important;
        background-color: var(--bg-surface);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid var(--border-light) !important;
    }
    .filter-card .form-label {
        letter-spacing: 0.5px;
    }
    .filter-card .form-select, .filter-card .form-control {
        border-radius: 8px;
        padding: 10px 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-color: var(--border-light) !important;
        background-color: var(--bg-base);
    }
    .filter-card .form-select:focus, .filter-card .form-control:focus {
        border-color: var(--primary-color) !important;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.12) !important;
    }

    /* Quick Filter Buttons */
    .quick-filter-btn {
        border-radius: 30px !important;
        padding: 6px 16px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border-color: var(--border-light) !important;
        color: var(--text-muted) !important;
        background: var(--bg-base) !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .quick-filter-btn:hover {
        background-color: var(--primary-light) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(147, 51, 234, 0.08);
    }
    .quick-filter-btn.active {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 8px rgba(147, 51, 234, 0.2) !important;
    }

    /* Stat Cards */
    .stat-card {
        border: 1px solid var(--border-light);
        border-radius: 12px;
        background: var(--bg-surface);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04) !important;
    }
    .stat-card:hover.stat-card-primary { border-color: var(--primary-color); }
    .stat-card:hover.stat-card-success { border-color: #10b981; }
    .stat-card:hover.stat-card-info { border-color: #0ea5e9; }
    .stat-card:hover.stat-card-warning { border-color: #f59e0b; }
    .stat-card:hover.stat-card-danger { border-color: #ef4444; }
    .stat-card:hover.stat-card-secondary { border-color: #64748b; }

    .bg-primary-light { background-color: var(--primary-light); }
    .bg-success-light { background-color: rgba(16, 185, 129, 0.08); }
    .bg-info-light { background-color: rgba(14, 165, 233, 0.08); }
    .bg-warning-light { background-color: rgba(245, 158, 11, 0.08); }
    .bg-danger-light { background-color: rgba(239, 68, 68, 0.08); }
    .bg-secondary-light { background-color: rgba(100, 116, 139, 0.08); }

    .stat-card-icon {
        transition: all 0.3s ease;
    }
    .stat-card:hover .stat-card-icon {
        transform: scale(1.1);
    }
    .stat-card:hover .animate-icon {
        animation: pulseIcon 1s infinite alternate;
    }
    @keyframes pulseIcon {
        from { transform: scale(1); }
        to { transform: scale(1.15); }
    }

    .count-number {
        letter-spacing: -0.5px;
    }

    /* Fade animation when data reloads */
    .fade-content {
        animation: fadeEffect 0.4s ease-in-out;
    }
    @keyframes fadeEffect {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media print {
        body * {
            visibility: hidden;
            background: none !important;
            box-shadow: none !important;
        }
        #dashboard-content-wrapper, #dashboard-content-wrapper * {
            visibility: visible;
        }
        #dashboard-content-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        #loading-overlay, .btn-group, .filter-card, .quick-filter-btn, .row.mb-3 {
            display: none !important;
        }
    }
</style>
@endsection

@section('extra_js')
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

    // JS Xử lý Ẩn/Hiện form input theo loại báo cáo
    function updateFilterInputs() {
        const type = document.getElementById('filter-report-type').value;
        
        const gFromDate = document.getElementById('group-from-date');
        const gToDate = document.getElementById('group-to-date');
        const gWeek = document.getElementById('group-week');
        const gMonth = document.getElementById('group-month');
        const gYear = document.getElementById('group-year');
        
        // Ẩn tất cả trước
        gFromDate.classList.add('d-none');
        gToDate.classList.add('d-none');
        gWeek.classList.add('d-none');
        gMonth.classList.add('d-none');
        gYear.classList.add('d-none');
        
        // Hiển thị tùy loại báo cáo
        if (type === 'date') {
            gFromDate.classList.remove('d-none');
            gToDate.classList.remove('d-none');
        } else if (type === 'week') {
            gWeek.classList.remove('d-none');
            gYear.classList.remove('d-none');
        } else if (type === 'month') {
            gMonth.classList.remove('d-none');
            gYear.classList.remove('d-none');
        } else if (type === 'year') {
            gYear.classList.remove('d-none');
        }
    }

    // Format tiền Việt Nam
    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
    }

    // Animation chạy số từ 0 -> thực tế
    function animateCounters() {
        const counters = document.querySelectorAll('.count-number');
        counters.forEach(counter => {
            const target = parseFloat(counter.getAttribute('data-value')) || 0;
            const isMoney = counter.getAttribute('data-is-money') === 'true' || counter.id.includes('revenue') || counter.id.includes('card-number') || counter.innerText.includes('đ') || counter.getAttribute('data-value') > 1000;
            
            let start = 0;
            const duration = 800; // ms
            const startTime = performance.now();
            
            function updateNumber(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Easing function outQuad
                const easeProgress = progress * (2 - progress);
                
                const currentValue = Math.floor(start + easeProgress * (target - start));
                
                if (isMoney) {
                    counter.innerText = formatMoney(currentValue);
                } else {
                    counter.innerText = new Intl.NumberFormat('vi-VN').format(currentValue);
                }
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                } else {
                    if (isMoney) {
                        counter.innerText = formatMoney(target);
                    } else {
                        counter.innerText = new Intl.NumberFormat('vi-VN').format(target);
                    }
                }
            }
            requestAnimationFrame(updateNumber);
        });
    }

    // Gọi AJAX lấy dữ liệu & cập nhật DOM
    function applyFilter(e) {
        if (e) e.preventDefault();
        
        const form = document.getElementById('dashboard-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        
        const overlay = document.getElementById('loading-overlay');
        overlay.classList.remove('d-none');
        
        const newUrl = window.location.pathname + '?' + params;
        window.history.pushState({ path: newUrl }, '', newUrl);
        
        fetch(newUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            overlay.classList.add('d-none');
            
            // Hiệu ứng Fade
            const contentWrapper = document.getElementById('dashboard-content-wrapper');
            contentWrapper.classList.remove('fade-content');
            void contentWrapper.offsetWidth; // Trigger reflow
            contentWrapper.classList.add('fade-content');
            
            // Cập nhật giá trị counters
            document.querySelectorAll('.count-number').forEach(counter => {
                const parentCard = counter.closest('.stat-card');
                if (parentCard) {
                    if (parentCard.classList.contains('stat-card-primary')) {
                        counter.setAttribute('data-value', data.totalRevenue);
                    } else if (parentCard.classList.contains('stat-card-success')) {
                        counter.setAttribute('data-value', data.dailyRevenue);
                    } else if (parentCard.classList.contains('stat-card-info')) {
                        counter.setAttribute('data-value', data.periodRevenue);
                    } else if (parentCard.classList.contains('stat-card-warning')) {
                        counter.setAttribute('data-value', data.yearlyRevenue);
                    }
                }
                
                // Cho 6 card nhỏ ở trên
                const parentCardCol = counter.closest('.col-lg-2');
                if (parentCardCol) {
                    const labelEl = parentCardCol.querySelector('.stat-card-label');
                    if (labelEl) {
                        const label = labelEl.innerText.trim();
                        if (label.includes('Người dùng')) counter.setAttribute('data-value', data.totalActiveUsers);
                        if (label.includes('Phim')) counter.setAttribute('data-value', data.totalMovies);
                        if (label.includes('Cụm rạp')) counter.setAttribute('data-value', data.totalCinemas);
                        if (label.includes('Suất chiếu')) counter.setAttribute('data-value', data.totalShowtimes);
                        if (label.includes('Vé đã bán')) counter.setAttribute('data-value', data.totalTicketsSold);
                        if (label.includes('Doanh thu tổng')) counter.setAttribute('data-value', data.totalRevenue);
                    }
                }
            });
            
            // Animate counters
            animateCounters();
            
            // Cập nhật Header đang xem báo cáo
            let reportTypeText = 'Theo tháng';
            if (data.selectedReportType === 'date') reportTypeText = 'Theo ngày';
            else if (data.selectedReportType === 'week') reportTypeText = 'Theo tuần';
            else if (data.selectedReportType === 'year') reportTypeText = 'Theo năm';
            
            document.getElementById('header-report-type').innerText = reportTypeText;
            document.getElementById('header-cinema').innerText = data.cinemaName;
            
            let timeText = '';
            if (data.selectedReportType === 'date') {
                const fDateArr = data.fromDate.split('-');
                const tDateArr = data.toDate.split('-');
                timeText = `${fDateArr[2]}/${fDateArr[1]}/${fDateArr[0]} ↓ ${tDateArr[2]}/${tDateArr[1]}/${tDateArr[0]}`;
            } else if (data.selectedReportType === 'week') {
                timeText = `Tuần ${data.selectedWeek.toString().padStart(2, '0')} / Năm ${data.selectedYear}`;
            } else if (data.selectedReportType === 'month') {
                timeText = `Tháng ${data.selectedMonth.toString().padStart(2, '0')} / Năm ${data.selectedYear}`;
            } else if (data.selectedReportType === 'year') {
                timeText = `Năm ${data.selectedYear}`;
            }
            document.getElementById('header-time').innerText = timeText;
            
            // Cập nhật nhãn card kỳ chọn
            document.getElementById('period-card-label').innerText = `Doanh thu kỳ chọn (${reportTypeText})`;
            
            // Cập nhật nhãn card doanh thu năm
            const yearLabel = document.querySelector('.stat-card-warning .stat-card-label');
            if (yearLabel) {
                yearLabel.innerText = `Doanh thu năm (${data.selectedYear})`;
            }
            
            // Cập nhật Tables HTML
            document.getElementById('revenue-table-wrapper').innerHTML = data.html_revenue_table;
            document.getElementById('top-combos-wrapper').innerHTML = data.html_top_combos;
            document.getElementById('top-movies-wrapper').innerHTML = data.html_top_movies;
        })
        .catch(err => {
            console.error('Fetch error:', err);
            overlay.classList.add('d-none');
            alert('Có lỗi xảy ra khi tải báo cáo!');
        });
    }

    // Xử lý Quick Filters click
    function handleQuickFilter(btn) {
        document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const filterType = btn.getAttribute('data-type');
        const today = new Date();
        
        const reportTypeSelect = document.getElementById('filter-report-type');
        const fromDateInput = document.getElementById('filter-from-date');
        const toDateInput = document.getElementById('filter-to-date');
        const monthSelect = document.getElementById('filter-month');
        const yearSelect = document.getElementById('filter-year');
        const weekSelect = document.getElementById('filter-week');
        
        const formatDateStr = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };
        
        const getWeekNumber = (d) => {
            d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
            var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            return weekNo;
        };
        
        if (filterType === 'today') {
            reportTypeSelect.value = 'date';
            fromDateInput.value = formatDateStr(today);
            toDateInput.value = formatDateStr(today);
        } else if (filterType === 'yesterday') {
            reportTypeSelect.value = 'date';
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            fromDateInput.value = formatDateStr(yesterday);
            toDateInput.value = formatDateStr(yesterday);
        } else if (filterType === 'last7') {
            reportTypeSelect.value = 'date';
            const last7 = new Date(today);
            last7.setDate(today.getDate() - 6);
            fromDateInput.value = formatDateStr(last7);
            toDateInput.value = formatDateStr(today);
        } else if (filterType === 'last30') {
            reportTypeSelect.value = 'date';
            const last30 = new Date(today);
            last30.setDate(today.getDate() - 29);
            fromDateInput.value = formatDateStr(last30);
            toDateInput.value = formatDateStr(today);
        } else if (filterType === 'thisMonth') {
            reportTypeSelect.value = 'month';
            monthSelect.value = today.getMonth() + 1;
            yearSelect.value = today.getFullYear();
        } else if (filterType === 'lastMonth') {
            reportTypeSelect.value = 'month';
            const lastMonthDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            monthSelect.value = lastMonthDate.getMonth() + 1;
            yearSelect.value = lastMonthDate.getFullYear();
        } else if (filterType === 'thisYear') {
            reportTypeSelect.value = 'year';
            yearSelect.value = today.getFullYear();
        }
        
        updateFilterInputs();
        applyFilter();
    }

    // Xử lý Reset form
    function handleReset() {
        document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
        
        const form = document.getElementById('dashboard-filter-form');
        form.reset();
        
        document.getElementById('filter-cinema').value = '';
        document.getElementById('filter-report-type').value = 'month';
        
        const today = new Date();
        document.getElementById('filter-month').value = today.getMonth() + 1;
        document.getElementById('filter-year').value = today.getFullYear();
        
        const getWeekNumber = (d) => {
            d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
            var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            return weekNo;
        };
        document.getElementById('filter-week').value = getWeekNumber(today);
        
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const formatDateStr = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };
        document.getElementById('filter-from-date').value = formatDateStr(firstDay);
        document.getElementById('filter-to-date').value = formatDateStr(today);
        
        updateFilterInputs();
        applyFilter();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Mặc định điền Từ ngày / Đến ngày ban đầu nếu trống
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const formatDateStr = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };
        
        if (!document.getElementById('filter-from-date').value) {
            document.getElementById('filter-from-date').value = formatDateStr(firstDay);
        }
        if (!document.getElementById('filter-to-date').value) {
            document.getElementById('filter-to-date').value = formatDateStr(today);
        }

        updateFilterInputs();
        
        document.getElementById('filter-report-type').addEventListener('change', updateFilterInputs);
        document.getElementById('dashboard-filter-form').addEventListener('submit', applyFilter);
        document.getElementById('btn-reset').addEventListener('click', handleReset);
        
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                handleQuickFilter(this);
            });
        });
        
        // Active nút Quick Filter ban đầu tương ứng nếu có
        const currentReportType = document.getElementById('filter-report-type').value;
        const currentMonth = document.getElementById('filter-month').value;
        const currentYear = document.getElementById('filter-year').value;
        
        if (currentReportType === 'month' && parseInt(currentMonth) === (today.getMonth() + 1) && parseInt(currentYear) === today.getFullYear()) {
            const btn = document.querySelector('.quick-filter-btn[data-type="thisMonth"]');
            if (btn) btn.classList.add('active');
        } else if (currentReportType === 'year' && parseInt(currentYear) === today.getFullYear()) {
            const btn = document.querySelector('.quick-filter-btn[data-type="thisYear"]');
            if (btn) btn.classList.add('active');
        }
        
        // Bắt đầu chạy counter hiệu ứng ban đầu
        animateCounters();
    });
</script>
@endsection
