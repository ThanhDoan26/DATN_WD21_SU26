@extends('layouts.staff')

@section('title', 'Tra cứu & Check-in vé')
@section('page_title', 'Tra cứu & Check-in vé')

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
    #reader {
        width: 100%;
        max-width: 450px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        border: none !important;
    }
    #reader__scan_region {
        background: #f8fafc;
    }
    .scanner-active {
        border: 3px solid #ca8a04 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 search-container">
    
    <!-- Search Form Card -->
    <div class="card ticket-card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-search me-2 text-warning"></i> Tra cứu thông tin vé</h5>
            
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
                <div id="reader"></div>
                <p class="text-muted mt-2 small"><i class="fas fa-info-circle me-1"></i> Di chuyển mã QR của vé vào vùng quét của camera.</p>
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
            @php
                // Xác định loại cảnh báo dựa trên nội dung
                $isInfo = str_contains($warning, 'đã được sử dụng');
                $alertClass = $isInfo ? 'alert-info' : 'alert-warning';
                $iconClass = $isInfo ? 'fa-info-circle text-info' : 'fa-exclamation-triangle text-warning';
                $title = $isInfo ? 'Thông tin vé:' : 'Lưu ý vé không đủ điều kiện Check-in:';
                $textClass = $isInfo ? 'text-info-emphasis' : 'text-warning-emphasis';
            @endphp
            <div class="alert {{ $alertClass }} alert-dismissible fade show border-0 shadow-sm rounded-3 py-3 mb-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas {{ $iconClass }} fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">{{ $title }}</h6>
                        <p class="mb-0 {{ $textClass }}">{{ $warning }}</p>
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
                <div>
                    @php
                        $statusStr = '';
                        $statusClass = '';
                        if ($searchType === 'booking') {
                            $status = $result->status;
                            if ($status === 'Paid') { $statusStr = 'Đã thanh toán (Sẵn sàng)'; $statusClass = 'badge-paid'; }
                            elseif ($status === 'Used') { $statusStr = 'Đã sử dụng (Đã check-in)'; $statusClass = 'badge-used'; }
                            elseif ($status === 'Pending') { $statusStr = 'Chưa thanh toán (Chờ)'; $statusClass = 'badge-pending'; }
                            elseif ($status === 'Cancelled') { $statusStr = 'Đã hủy bỏ'; $statusClass = 'badge-cancelled'; }
                        } else {
                            $status = $result->status;
                            if ($status === 'PAID') { $statusStr = 'Đã thanh toán (Sẵn sàng)'; $statusClass = 'badge-paid'; }
                            elseif ($status === 'USED') { $statusStr = 'Đã sử dụng (Đã check-in)'; $statusClass = 'badge-used'; }
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

                <hr class="my-4">

                <!-- Ghế & Checkin Actions -->
                <h5 class="fw-bold mb-3"><i class="fas fa-chair me-2 text-warning"></i> Chi tiết ghế đặt</h5>

                @if($searchType === 'booking')
                    <!-- Case 1: Search by Booking Code - show list of all seats with individual buttons -->
                    <div class="row">
                        <div class="col-12">
                            @foreach($result->bookedSeats as $seat)
                                <div class="seat-item">
                                    <div>
                                        <span class="seat-code">{{ $seat->seat ? ($seat->seat->row_name . $seat->seat->seat_number) : 'N/A' }}</span>
                                        <span class="ms-2 badge bg-secondary-subtle text-secondary-emphasis">{{ $seat->seat->seat_type ?? 'Regular' }}</span>
                                        <span class="ms-2 text-muted">{{ number_format($seat->price_at_booking) }}đ</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($seat->status === 'PAID')
                                            <span class="badge badge-paid me-2"><i class="fas fa-check-circle me-1"></i>Sẵn sàng check-in</span>
                                            
                                            <!-- Individual Seat Check-in form -->
                                            <form action="{{ route('staff.ticket.checkin') }}" method="POST" class="m-0 d-inline-block">
                                                @csrf
                                                <input type="hidden" name="type" value="booking">
                                                <input type="hidden" name="id" value="{{ $result->id }}">
                                                <input type="hidden" name="seat_id" value="{{ $seat->id }}">
                                                <button type="submit" class="btn btn-sm btn-warning fw-bold px-3">Check-in ghế</button>
                                            </form>
                                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $seat->id]) }}')" class="btn btn-sm btn-outline-secondary ms-1" title="In vé"><i class="fas fa-print"></i></button>
                                        @elseif($seat->status === 'USED')
                                            <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                                            <small class="text-muted ms-2">{{ $seat->checked_in_at ? $seat->checked_in_at->format('H:i d/m') : '' }}</small>
                                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $seat->id]) }}')" class="btn btn-sm btn-outline-secondary ms-2" title="In vé"><i class="fas fa-print"></i></button>
                                        @elseif($seat->status === 'RESERVED')
                                            <span class="badge badge-pending">Chờ thanh toán</span>
                                        @elseif($seat->status === 'CANCELLED')
                                            <span class="badge badge-cancelled">Đã hủy</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Overall checkin button for booking -->
                    <div class="mt-4 text-center d-flex justify-content-center gap-3">
                        @if($canCheckIn)
                            <form action="{{ route('staff.ticket.checkin') }}" method="POST" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="type" value="booking">
                                <input type="hidden" name="id" value="{{ $result->id }}">
                                <button type="submit" class="btn btn-warning fw-bold px-5 py-3 fs-5 shadow">
                                    <i class="fas fa-check-circle me-2"></i> CHECK-IN TOÀN BỘ GHẾ
                                </button>
                            </form>
                        @endif

                        @if($result->status === 'Paid' || $result->status === 'Used')
                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'booking', 'id' => $result->id]) }}')" class="btn btn-secondary fw-bold px-5 py-3 fs-5 shadow">
                                <i class="fas fa-print me-2"></i> IN TOÀN BỘ VÉ
                            </button>
                        @endif
                    </div>

                @else
                    <!-- Case 2: Search by Seat QR code - display single seat info with big button -->
                    <div class="seat-item p-3 mb-4">
                        <div>
                            <span class="seat-code">{{ $result->seat ? ($result->seat->row_name . $result->seat->seat_number) : 'N/A' }}</span>
                            <span class="ms-2 badge bg-secondary-subtle text-secondary-emphasis">{{ $result->seat->seat_type ?? 'Regular' }}</span>
                            <span class="ms-2 text-muted">{{ number_format($result->price_at_booking) }}đ</span>
                        </div>
                        <div>
                            @if($result->status === 'PAID')
                                <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i>Sẵn sàng check-in</span>
                            @elseif($result->status === 'USED')
                                <span class="badge badge-used"><i class="fas fa-check-double me-1"></i>Đã sử dụng</span>
                            @elseif($result->status === 'RESERVED')
                                <span class="badge badge-pending">Chờ thanh toán</span>
                            @elseif($result->status === 'CANCELLED')
                                <span class="badge badge-cancelled">Đã hủy</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-center d-flex justify-content-center gap-3">
                        @if($canCheckIn)
                            <form action="{{ route('staff.ticket.checkin') }}" method="POST" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="type" value="seat">
                                <input type="hidden" name="id" value="{{ $result->id }}">
                                <button type="submit" class="btn btn-warning fw-bold px-5 py-3 fs-5 shadow">
                                    <i class="fas fa-check-circle me-2"></i> XÁC NHẬN CHECK-IN VÉ NÀY
                                </button>
                            </form>
                        @endif

                        @if($result->status === 'PAID' || $result->status === 'USED')
                            <button type="button" onclick="printTicketIframe('{{ route('staff.ticket.print', ['type' => 'seat', 'id' => $result->id]) }}')" class="btn btn-secondary fw-bold px-5 py-3 fs-5 shadow">
                                <i class="fas fa-print me-2"></i> IN VÉ
                            </button>
                        @endif
                    </div>
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
        
        let html5QrcodeScanner = null;
        let isScannerRunning = false;

        function startScanner() {
            scannerWrapper.classList.remove('d-none');
            document.getElementById('reader').classList.add('scanner-active');
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                /* verbose= */ false
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            scannerBtnText.textContent = "Tắt Camera";
            toggleScannerBtn.classList.replace('btn-outline-warning', 'btn-danger');
            isScannerRunning = true;
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    scannerWrapper.classList.add('d-none');
                    scannerBtnText.textContent = "Bật Camera Quét QR";
                    toggleScannerBtn.classList.replace('btn-danger', 'btn-outline-warning');
                    isScannerRunning = false;
                }).catch(err => {
                    console.error("Lỗi khi tắt camera: ", err);
                });
            }
        }

        toggleScannerBtn.addEventListener('click', function() {
            if (isScannerRunning) {
                stopScanner();
            } else {
                startScanner();
            }
        });

        function onScanSuccess(decodedText, decodedResult) {
            // Dừng quét
            stopScanner();
            
            // Điền vào input và submit
            ticketCodeInput.value = decodedText;
            
            // Hiệu ứng và submit
            ticketCodeInput.style.backgroundColor = '#dcfce7';
            setTimeout(() => {
                searchForm.submit();
            }, 500);
        }

        function onScanFailure(error) {
            // Không log lỗi liên tục tránh tràn console
        }

        // Handle iframe printing
        window.printTicketIframe = function(url) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = url;
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
