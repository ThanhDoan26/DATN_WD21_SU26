@extends('layouts.staff')

@section('title', 'Tra cứu & In vé')
@section('page_title', 'Tra cứu & In vé')

@section('extra_css')
<style>
    .search-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .status-badge {
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-paid { background-color: #dcfce7; color: #16a34a; }
    .badge-used { background-color: #e0f2fe; color: #0369a1; }
    .badge-pending { background-color: #fef3c7; color: #d97706; }
    .badge-cancelled { background-color: #fee2e2; color: #dc2626; }
    .badge-printed { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; }
    
    .ticket-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
        background: #fff;
    }
    .ticket-header {
        background: linear-gradient(135deg, #a16207 0%, #ca8a04 100%);
        color: #fff;
        padding: 20px 25px;
    }
    .ticket-body {
        padding: 30px 25px;
    }
    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
        font-weight: 600;
    }
    .info-value {
        font-size: 1.1rem;
        color: #1e293b;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .seat-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
    }
    .seat-item:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    .seat-code {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
    }
    #scannerWrapper {
        max-width: 480px;
        margin: 0 auto;
    }
    #reader {
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        background: #0f172a;
        position: relative;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
    }
    #reader video {
        border-radius: 14px;
        object-fit: cover;
    }
    .scanner-laser-line {
        position: absolute;
        top: 0;
        left: 5%;
        width: 90%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #eab308, transparent);
        box-shadow: 0 0 12px #eab308;
        animation: scanLaser 2s infinite ease-in-out;
        z-index: 10;
        pointer-events: none;
    }
    @keyframes scanLaser {
        0%, 100% { top: 15%; opacity: 0.2; }
        50% { top: 85%; opacity: 1; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 search-container">
    
    <!-- Search Form Card -->
    <div class="card ticket-card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-search me-2 text-warning"></i> Tra cứu & In vé</h5>
            
            <form action="{{ route('staff.ticket.search') }}" method="GET" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-8 col-sm-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-ticket-alt"></i></span>
                            <input type="text" 
                                   name="code" 
                                   id="ticketCodeInput"
                                   class="form-control border-start-0 py-3" 
                                   placeholder="Nhập mã đơn hàng (Ví dụ: BK...) hoặc quét mã QR" 
                                   value="{{ $code ?? '' }}" 
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold px-4 py-3 flex-grow-1"><i class="fas fa-search me-1"></i> Tra cứu</button>
                        @if($code)
                            <a href="{{ route('staff.ticket.search') }}" class="btn btn-outline-secondary px-3 py-3" title="Làm mới"><i class="fas fa-redo"></i></a>
                        @endif
                    </div>
                </div>
            </form>
            
            <div class="d-flex justify-content-center mt-4">
                <button type="button" class="btn btn-outline-warning fw-bold px-4 py-2" id="toggleScannerBtn">
                    <i class="fas fa-camera me-2"></i> <span id="scannerBtnText">Bật Camera Quét QR</span>
                </button>
            </div>
            
            <!-- QR Scanner region -->
            <div class="mt-3 text-center d-none" id="scannerWrapper">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="small fw-bold text-muted"><i class="fas fa-video me-1 text-warning"></i> Khung quét camera trực tiếp</span>
                    <div id="cameraSelectContainer" class="d-none">
                        <select id="cameraSelect" class="form-select form-select-sm py-1 shadow-none" style="font-size: 0.8rem; border-radius: 8px;">
                        </select>
                    </div>
                </div>

                <div id="scannerAlert" class="alert alert-warning py-2 small d-none mb-2 text-start rounded-3"></div>

                <div class="position-relative">
                    <div id="reader"></div>
                    <div class="scanner-laser-line" id="scannerLaser" style="display: none;"></div>
                </div>

                <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
                    <label class="btn btn-sm btn-outline-secondary px-3 py-1.5 mb-0" style="cursor: pointer; border-radius: 10px;">
                        <i class="fas fa-file-image me-1 text-primary"></i> Tải ảnh mã QR từ máy
                        <input type="file" id="qrFileInput" accept="image/*" class="d-none">
                    </label>
                </div>
                <p class="text-muted mt-2 small mb-0"><i class="fas fa-info-circle me-1"></i> Hướng camera về phía mã QR vé để hệ thống tự động nhận diện và tra cứu.</p>
            </div>
        </div>
    </div>

    <!-- Session Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 py-3 mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Thành công!</h6>
                    <p class="mb-0 text-success-emphasis">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 py-3 mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-times-circle fa-2x me-3 text-danger"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Lỗi!</h6>
                    <p class="mb-0 text-danger-emphasis">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Warnings & Alerts Section -->
    @if(isset($warnings) && count($warnings) > 0)
        @foreach($warnings as $warning)
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 py-3 mb-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Lưu ý:</h6>
                        <p class="mb-0 text-warning-emphasis">{{ $warning }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <!-- Results Section -->
    @if($result)
        <div class="card ticket-card">
            
            <!-- Result Header -->
            <div class="ticket-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    @if($searchType === 'booking')
                        <span class="text-uppercase small fw-bold opacity-75">Chi tiết đơn hàng</span>
                        <h3 class="mb-0 fw-extrabold mt-1">Mã đơn: {{ $result->booking_code }}</h3>
                    @else
                        <span class="text-uppercase small fw-bold opacity-75">Chi tiết vé đơn lẻ</span>
                        <h3 class="mb-0 fw-extrabold mt-1">Mã QR: {{ $result->qr_code }}</h3>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(!empty($isOtherCinema))
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold shadow-sm" style="font-size: 0.85rem; border-radius: 50px;">
                            <i class="fas fa-eye me-1"></i> VÉ RẠP KHÁC (CHỈ XEM)
                        </span>
                    @endif
                    @php
                        $statusStr = '';
                        $statusClass = '';
                        if ($searchType === 'booking') {
                            $status = strtolower($result->status ?? '');
                            if ($status === 'paid') { $statusStr = 'Đã thanh toán'; $statusClass = 'badge-paid'; }
                            elseif ($status === 'used') { $statusStr = 'Đã sử dụng'; $statusClass = 'badge-used'; }
                            elseif ($status === 'pending') { $statusStr = 'Chưa thanh toán (Chờ)'; $statusClass = 'badge-pending'; }
                            elseif ($status === 'cancelled') { $statusStr = 'Đã hủy bỏ'; $statusClass = 'badge-cancelled'; }
                            elseif ($status === 'expired') { $statusStr = 'Hết hạn giữ chỗ'; $statusClass = 'badge-cancelled'; }
                            else { $statusStr = $result->status_label ?? $result->status; $statusClass = 'badge-pending'; }
                        } else {
                            $status = strtoupper($result->status ?? '');
                            if ($status === 'PAID') { $statusStr = 'Đã thanh toán'; $statusClass = 'badge-paid'; }
                            elseif ($status === 'USED') { $statusStr = 'Đã sử dụng'; $statusClass = 'badge-used'; }
                            elseif ($status === 'RESERVED') { $statusStr = 'Chưa thanh toán (Đặt trước)'; $statusClass = 'badge-pending'; }
                            elseif ($status === 'CANCELLED') { $statusStr = 'Đã hủy bỏ'; $statusClass = 'badge-cancelled'; }
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        <i class="fas fa-circle me-1 small"></i> {{ $statusStr }}
                    </span>
                </div>
            </div>

            <div class="ticket-body">
                @if(!empty($isOtherCinema))
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 py-3 mb-4 d-flex align-items-center" style="background-color: #fffbeb; border-left: 5px solid #f59e0b !important;">
                        <i class="fas fa-info-circle fa-2x text-warning me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Vé thuộc chi nhánh khác: <span class="text-danger fw-bold">{{ $ticketCinemaName ?? ($searchType === 'booking' ? ($result->showtime->room->cinema->name ?? 'N/A') : ($result->booking->showtime->room->cinema->name ?? 'N/A')) }}</span></h6>
                            <p class="mb-0 text-muted small">Nhân viên được phép tra cứu thông tin vé này nhưng <strong>không có quyền Chỉnh sửa hoặc In vé</strong> tại rạp hiện tại.</p>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <!-- Column 1: Movie & Showtime -->
                    <div class="col-md-6 col-sm-12">
                        <div class="info-label">Phim chiếu</div>
                        <div class="info-value text-warning-emphasis">
                            {{ $searchType === 'booking' ? ($result->showtime->movie->title ?? 'N/A') : ($result->booking->showtime->movie->title ?? 'N/A') }}
                        </div>

                        <div class="info-label">Rạp chiếu</div>
                        <div class="info-value">
                            {{ $searchType === 'booking' ? ($result->showtime->room->cinema->name ?? 'N/A') : ($result->booking->showtime->room->cinema->name ?? 'N/A') }}
                            @if(!empty($isOtherCinema))
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2" style="font-size: 0.75rem; vertical-align: middle;">
                                    <i class="fas fa-map-marker-alt me-1"></i>Khác rạp hiện tại
                                </span>
                            @endif
                        </div>

                        <div class="info-label">Phòng chiếu & Định dạng</div>
                        <div class="info-value text-uppercase">
                            {{ $searchType === 'booking' ? ($result->showtime->room->name ?? 'N/A') : ($result->booking->showtime->room->name ?? 'N/A') }} 
                            ({{ $searchType === 'booking' ? ($result->showtime->room->format ?? 'N/A') : ($result->booking->showtime->room->format ?? 'N/A') }})
                        </div>

                        <div class="info-label">Suất chiếu</div>
                        <div class="info-value">
                            <i class="far fa-clock me-1 text-muted"></i>
                            @php
                                $st = $searchType === 'booking' ? $result->showtime : ($result->booking->showtime ?? null);
                            @endphp
                            @if($st)
                                <span class="fw-bold">{{ $st->start_time->format('H:i') }}</span> ngày {{ $st->start_time->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>

                    <!-- Column 2: Customer & Payment -->
                    <div class="col-md-6 col-sm-12">
                        <div class="info-label">Khách hàng</div>
                        <div class="info-value">
                            @php
                                $usr = $searchType === 'booking' ? $result->user : ($result->booking->user ?? null);
                            @endphp
                            {{ $usr->name ?? ($result->notes ?? 'Khách tại quầy') }}
                        </div>

                        <div class="info-label">Số điện thoại / Email</div>
                        <div class="info-value text-muted" style="font-size: 1rem;">
                            {{ $usr->phone ?? 'N/A' }} / {{ $usr->email ?? 'N/A' }}
                        </div>

                        <div class="info-label">Tổng tiền đơn hàng</div>
                        <div class="info-value fs-4 text-warning">
                            {{ $searchType === 'booking' ? number_format($result->total_price) : number_format($result->booking->total_price) }}đ
                        </div>

                        @if($searchType === 'booking' && $result->notes)
                            <div class="info-label">Ghi chú</div>
                            <div class="info-value text-secondary small">{{ $result->notes }}</div>
                        @endif
                    </div>
                </div>

                @php
                    $targetBooking = $searchType === 'booking' ? $result : ($result->booking ?? null);
                    $bookingCombos = $targetBooking ? $targetBooking->combos : collect();
                @endphp

                @if($bookingCombos && $bookingCombos->count() > 0)
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-popcorn me-2 text-warning"></i> Combo Bắp Nước đã đặt ({{ $bookingCombos->count() }})</h5>
                    <div class="row g-3 mb-2">
                        @foreach($bookingCombos as $combo)
                            <div class="col-md-6 col-sm-12">
                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center shadow-sm">
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $combo->name }}</div>
                                        <small class="text-muted">Số lượng: <span class="badge bg-warning text-dark fw-bold">x{{ $combo->pivot->quantity }}</span></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark fs-6">{{ number_format($combo->pivot->price * $combo->pivot->quantity) }}đ</div>
                                        <small class="text-muted">{{ number_format($combo->pivot->price) }}đ / item</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <hr class="my-4">

                <!-- Ghế & In vé Actions -->
                <h5 class="fw-bold mb-3"><i class="fas fa-chair me-2 text-warning"></i> Chi tiết ghế đặt</h5>

                @if($searchType === 'booking')
                    <!-- Case 1: Search by Booking Code - show list of all seats with print buttons -->
                    <div class="row">
                        <div class="col-12">
                            @foreach($result->bookedSeats as $seat)
                                <div class="seat-item">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="seat-code">{{ $seat->seat ? ($seat->seat->row_name . $seat->seat->seat_number) : 'N/A' }}</span>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $seat->seat->seat_type ?? 'Regular' }}</span>
                                        <span class="text-muted">{{ number_format($seat->price_at_booking) }}đ</span>
                                        
                                        @if($seat->printed_at || ($seat->print_count ?? 0) > 0)
                                            <span class="badge badge-printed" title="Thời gian in gần nhất: {{ $seat->printed_at ? $seat->printed_at->format('H:i:s d/m/Y') : 'N/A' }}">
                                                <i class="fas fa-print me-1"></i>Đã in (Lần {{ $seat->print_count ?: 1 }} - {{ $seat->printed_at ? $seat->printed_at->format('H:i d/m/Y') : '' }})
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border">
                                                <i class="fas fa-print me-1 opacity-50"></i>Chưa in vé
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(empty($isOtherCinema))
                                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $seat->id]) }}')" class="btn btn-sm {{ ($seat->printed_at || ($seat->print_count ?? 0) > 0) ? 'btn-outline-purple' : 'btn-outline-dark' }} fw-bold px-3">
                                                <i class="fas fa-print me-1"></i> {{ ($seat->printed_at || ($seat->print_count ?? 0) > 0) ? 'In lại' : 'In vé' }}
                                            </button>
                                            @if($seat->status === 'PAID')
                                                <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i>Đã thanh toán</span>
                                            @elseif($seat->status === 'USED')
                                                <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                                            @elseif($seat->status === 'RESERVED')
                                                <span class="badge badge-pending">Chờ thanh toán</span>
                                            @elseif($seat->status === 'CANCELLED')
                                                <span class="badge badge-cancelled">Đã hủy</span>
                                            @endif
                                        @else
                                            @if($seat->status === 'PAID')
                                                <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i>Đã thanh toán</span>
                                            @elseif($seat->status === 'USED')
                                                <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                                            @elseif($seat->status === 'RESERVED')
                                                <span class="badge badge-pending">Chờ thanh toán</span>
                                            @elseif($seat->status === 'CANCELLED')
                                                <span class="badge badge-cancelled">Đã hủy</span>
                                            @endif
                                            <span class="badge bg-secondary-subtle text-secondary border"><i class="fas fa-lock me-1"></i>Chỉ xem</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Overall actions for booking -->
                    @if(empty($isOtherCinema))
                        @php
                            $hasAnyPrinted = $result->bookedSeats->contains(fn($s) => $s->printed_at || ($s->print_count ?? 0) > 0);
                        @endphp
                        <div class="mt-4 text-center d-flex justify-content-center gap-3">
                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'booking', 'id' => $result->id]) }}')" class="btn btn-dark fw-bold px-5 py-3 fs-5 shadow">
                                <i class="fas fa-print me-2"></i> {{ $hasAnyPrinted ? 'IN LẠI TOÀN BỘ VÉ' : 'IN TOÀN BỘ VÉ' }}
                            </button>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted fw-bold">
                                <i class="fas fa-lock me-2 text-warning"></i> Chức năng In vé bị khóa vì vé này thuộc rạp khác ({{ $ticketCinemaName ?? ($result->showtime->room->cinema->name ?? 'Rạp khác') }}).
                            </span>
                        </div>
                    @endif

                @else
                    <!-- Case 2: Search by Seat QR code - display single seat info with print button -->
                    <div class="seat-item p-3 mb-4">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="seat-code">{{ $result->seat ? ($result->seat->row_name . $result->seat->seat_number) : 'N/A' }}</span>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $result->seat->seat_type ?? 'Regular' }}</span>
                            <span class="text-muted">{{ number_format($result->price_at_booking) }}đ</span>
                            
                            @if($result->printed_at || ($result->print_count ?? 0) > 0)
                                <span class="badge badge-printed" title="Thời gian in gần nhất: {{ $result->printed_at ? $result->printed_at->format('H:i:s d/m/Y') : 'N/A' }}">
                                                <i class="fas fa-print me-1"></i>Đã in (Lần {{ $result->print_count ?: 1 }} - {{ $result->printed_at ? $result->printed_at->format('H:i d/m/Y') : '' }})
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border">
                                    <i class="fas fa-print me-1 opacity-50"></i>Chưa in vé
                                </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(empty($isOtherCinema))
                                <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $result->id]) }}')" class="btn btn-outline-dark fw-bold px-3 py-1">
                                    <i class="fas fa-print me-1"></i> {{ ($result->printed_at || ($result->print_count ?? 0) > 0) ? 'In lại' : 'In vé' }}
                                </button>
                                @if($result->status === 'PAID')
                                    <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i>Đã thanh toán</span>
                                @elseif($result->status === 'USED')
                                    <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                                @elseif($result->status === 'RESERVED')
                                    <span class="badge badge-pending">Chờ thanh toán</span>
                                @elseif($result->status === 'CANCELLED')
                                    <span class="badge badge-cancelled">Đã hủy</span>
                                @endif
                            @else
                                @if($result->status === 'PAID')
                                    <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i>Đã thanh toán</span>
                                @elseif($result->status === 'USED')
                                    <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                                @elseif($result->status === 'RESERVED')
                                    <span class="badge badge-pending">Chờ thanh toán</span>
                                @elseif($result->status === 'CANCELLED')
                                    <span class="badge badge-cancelled">Đã hủy</span>
                                @endif
                                <span class="badge bg-secondary-subtle text-secondary border"><i class="fas fa-lock me-1"></i>Chỉ xem</span>
                            @endif
                        </div>
                    </div>

                    @if(empty($isOtherCinema))
                        <div class="text-center d-flex justify-content-center gap-3">
                            @if($result->status === 'PAID' || $result->status === 'USED')
                                <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $result->id]) }}')" class="btn btn-dark fw-bold px-5 py-3 fs-5 shadow">
                                    <i class="fas fa-print me-2"></i> {{ ($result->printed_at || ($result->print_count ?? 0) > 0) ? 'IN LẠI VÉ' : 'IN VÉ' }}
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted fw-bold">
                                <i class="fas fa-lock me-2 text-warning"></i> Chức năng In vé bị khóa vì vé này thuộc rạp khác ({{ $ticketCinemaName ?? ($result->booking->showtime->room->cinema->name ?? 'Rạp khác') }}).
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@section('extra_js')
<!-- html5-qrcode library from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleScannerBtn = document.getElementById('toggleScannerBtn');
        const scannerBtnText = document.getElementById('scannerBtnText');
        const scannerWrapper = document.getElementById('scannerWrapper');
        const ticketCodeInput = document.getElementById('ticketCodeInput');
        const searchForm = document.getElementById('searchForm');
        const cameraSelect = document.getElementById('cameraSelect');
        const cameraSelectContainer = document.getElementById('cameraSelectContainer');
        const scannerAlert = document.getElementById('scannerAlert');
        const scannerLaser = document.getElementById('scannerLaser');
        const qrFileInput = document.getElementById('qrFileInput');
        
        let html5QrCode = null;
        let isScannerRunning = false;
        let availableCameras = [];

        function showScannerAlert(msg, type = 'warning') {
            if (scannerAlert) {
                scannerAlert.className = `alert alert-${type} py-2 small mb-2 text-start rounded-3`;
                scannerAlert.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> ${msg}`;
                scannerAlert.classList.remove('d-none');
            }
        }

        function hideScannerAlert() {
            if (scannerAlert) {
                scannerAlert.classList.add('d-none');
                scannerAlert.innerHTML = '';
            }
        }

        async function initCameras() {
            try {
                availableCameras = await Html5Qrcode.getCameras();
                if (availableCameras && availableCameras.length > 0) {
                    cameraSelect.innerHTML = '';
                    availableCameras.forEach((cam, index) => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.text = cam.label || `Camera ${index + 1}`;
                        cameraSelect.appendChild(opt);
                    });
                    if (availableCameras.length > 1) {
                        cameraSelectContainer.classList.remove('d-none');
                    }
                }
            } catch (err) {
                console.warn("Không thể liệt kê danh sách camera trước: ", err);
            }
        }

        async function startScanner(cameraId = null) {
            scannerWrapper.classList.remove('d-none');
            hideScannerAlert();

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            const config = {
                fps: 15,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const qrboxSize = Math.floor(minEdge * 0.75);
                    return {
                        width: Math.max(qrboxSize, 180),
                        height: Math.max(qrboxSize, 180)
                    };
                },
                aspectRatio: 1.0
            };

            const cameraConfig = cameraId ? cameraId : { facingMode: "environment" };

            try {
                await html5QrCode.start(
                    cameraConfig,
                    config,
                    onScanSuccess,
                    onScanFailure
                );
                
                isScannerRunning = true;
                if (scannerLaser) scannerLaser.style.display = 'block';
                scannerBtnText.textContent = "Tắt Camera";
                toggleScannerBtn.classList.remove('btn-outline-warning');
                toggleScannerBtn.classList.add('btn-danger');

                // Lấy danh sách camera sau khi đã được cấp quyền thành công
                if (availableCameras.length === 0) {
                    initCameras();
                }
            } catch (err) {
                console.error("Lỗi khởi động camera: ", err);
                if (scannerLaser) scannerLaser.style.display = 'none';
                
                // Thử fallback sang camera đầu tiên nếu facingMode thất bại (thường xảy ra trên webcam máy bàn)
                if (!cameraId) {
                    try {
                        const cameras = await Html5Qrcode.getCameras();
                        if (cameras && cameras.length > 0) {
                            await html5QrCode.start(cameras[0].id, config, onScanSuccess, onScanFailure);
                            isScannerRunning = true;
                            if (scannerLaser) scannerLaser.style.display = 'block';
                            scannerBtnText.textContent = "Tắt Camera";
                            toggleScannerBtn.classList.remove('btn-outline-warning');
                            toggleScannerBtn.classList.add('btn-danger');
                            return;
                        }
                    } catch (fallbackErr) {
                        console.error("Fallback camera cũng thất bại: ", fallbackErr);
                    }
                }

                let errorMsg = "Không thể mở camera. ";
                const errStr = (err && err.toString()) || '';
                if (err && (err.name === 'NotAllowedError' || errStr.includes('Permission') || errStr.includes('permission') || errStr.includes('NotAllowedError'))) {
                    errorMsg += "Trình duyệt đang chặn quyền Camera. Vui lòng bấm vào <strong>biểu tượng Máy ảnh/Ổ khóa</strong> trên thanh địa chỉ URL của trình duyệt để <strong>Cho phép (Allow)</strong> truy cập Camera.";
                } else if (err && (err.name === 'NotFoundError' || errStr.includes('NotFound') || errStr.includes('DevicesNotFoundError'))) {
                    errorMsg += "Không tìm thấy thiết bị Camera trên thiết bị này. Bạn có thể dùng nút <strong>Tải ảnh mã QR</strong> bên dưới hoặc gõ mã vé trực tiếp.";
                } else if (err && (err.name === 'NotReadableError' || errStr.includes('in use') || errStr.includes('SourceUnavailableError'))) {
                    errorMsg += "Camera đang bị ứng dụng khác sử dụng hoặc đang bị khóa.";
                } else {
                    errorMsg += "Vui lòng kiểm tra quyền Camera hoặc dùng tính năng tải ảnh mã QR.";
                }
                showScannerAlert(errorMsg, 'danger');
            }
        }

        async function stopScanner() {
            if (html5QrCode && isScannerRunning) {
                try {
                    await html5QrCode.stop();
                } catch (err) {
                    console.error("Lỗi khi dừng camera: ", err);
                }
            }
            isScannerRunning = false;
            if (scannerLaser) scannerLaser.style.display = 'none';
            scannerWrapper.classList.add('d-none');
            scannerBtnText.textContent = "Bật Camera Quét QR";
            toggleScannerBtn.classList.remove('btn-danger');
            toggleScannerBtn.classList.add('btn-outline-warning');
            hideScannerAlert();
        }

        toggleScannerBtn.addEventListener('click', function() {
            if (isScannerRunning) {
                stopScanner();
            } else {
                startScanner();
            }
        });

        if (cameraSelect) {
            cameraSelect.addEventListener('change', async function() {
                if (isScannerRunning && html5QrCode) {
                    try {
                        await html5QrCode.stop();
                    } catch(e) {}
                    isScannerRunning = false;
                    await startScanner(this.value);
                }
            });
        }

        // Tải ảnh mã QR để nhận diện
        if (qrFileInput) {
            qrFileInput.addEventListener('change', async function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    const imageFile = e.target.files[0];
                    if (!html5QrCode) {
                        html5QrCode = new Html5Qrcode("reader");
                    }
                    try {
                        scannerWrapper.classList.remove('d-none');
                        showScannerAlert("Đang phân tích hình ảnh mã QR...", "info");
                        const decodedText = await html5QrCode.scanFile(imageFile, true);
                        onScanSuccess(decodedText);
                    } catch (fileErr) {
                        showScannerAlert("Không tìm thấy mã QR hợp lệ trong hình ảnh đã chọn. Vui lòng chọn ảnh chụp rõ nét hơn.", "danger");
                    }
                }
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Tắt camera
            stopScanner();
            
            // Xử lý chuỗi quét được: bóc tách lấy mã nếu là URL
            let extractedCode = decodedText ? decodedText.trim() : '';
            if (extractedCode.includes('/tickets/')) {
                const parts = extractedCode.split('/tickets/');
                extractedCode = parts[1].split('?')[0].split('#')[0];
            } else if (extractedCode.includes('/booking-history/')) {
                const parts = extractedCode.split('/booking-history/');
                extractedCode = parts[1].split('?')[0].split('#')[0];
            }

            // Điền vào input và submit
            ticketCodeInput.value = extractedCode;
            ticketCodeInput.style.backgroundColor = '#dcfce7';
            
            setTimeout(() => {
                searchForm.submit();
            }, 300);
        }

        function onScanFailure(error) {
            // Callback từng frame quét không khớp - không log để tránh nghẽn console
        }

        // Handle iframe printing in same tab
        window.printTicketIframe = function(url) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = url;

            // Tự động làm mới trang sau khi phát lệnh in để cập nhật trạng thái "Đã in vé" và thời gian in
            setTimeout(function() {
                window.location.reload();
            }, 1800);
        };

        // Tự động bật camera nếu URL có tham số scan=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('scan') === '1') {
            startScanner();
        }

        // Auto uppercase inputs
        ticketCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endsection
