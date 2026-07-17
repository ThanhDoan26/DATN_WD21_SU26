@extends('admin.layouts.app')

@section('title', 'Chi tiết: ' . $user->name . ' - Admin')
@section('page_title', 'Chi tiết Người Dùng')

@section('content')
{{-- Breadcrumb --}}
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>
</div>

{{-- Page Title --}}
<div class="page-title d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-user-circle me-2"></i>Chi tiết Người Dùng</h2>
        <p class="text-muted mb-0">Thông tin tài khoản và lịch sử hoạt động</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Chỉnh sửa
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- ── CỘT TRÁI: Thông tin cá nhân ── --}}
    <div class="col-xl-4 col-lg-5">

        {{-- Profile Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center pt-4 pb-3">
                {{-- Avatar --}}
                <div class="mx-auto mb-3" style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#a855f7);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;box-shadow:0 4px 15px rgba(108,99,255,.35);">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>

                {{-- Role Badge --}}
                @if($user->isAdmin())
                    <span class="badge bg-danger px-3 py-2 mb-3">
                        <i class="fas fa-crown me-1"></i>ADMIN
                    </span>
                @elseif($user->isManager())
                    <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                        <i class="fas fa-user-tie me-1"></i>MANAGER
                    </span>
                @elseif($user->isStaff())
                    <span class="badge bg-info text-dark px-3 py-2 mb-3">
                        <i class="fas fa-user-cog me-1"></i>STAFF
                    </span>
                @else
                    <span class="badge bg-secondary px-3 py-2 mb-3">
                        <i class="fas fa-user me-1"></i>CUSTOMER
                    </span>
                @endif

                {{-- Status --}}
                <div class="mb-3">
                    @if($user->status === 'ACTIVE')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                            <i class="fas fa-check-circle me-1"></i> Đang hoạt động
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                            <i class="fas fa-ban me-1"></i> Đã bị khóa
                        </span>
                    @endif
                </div>

                {{-- Toggle Status (nếu không phải chính mình) --}}
                @if(auth()->id() !== $user->id)
                <div class="border-top pt-3">
                    <button type="button"
                            class="btn btn-sm btn-{{ $user->status === 'ACTIVE' ? 'outline-danger' : 'outline-success' }} toggle-status-btn"
                            data-id="{{ $user->id }}"
                            data-status="{{ $user->status }}"
                            id="toggle-btn">
                        <i class="fas fa-{{ $user->status === 'ACTIVE' ? 'lock' : 'unlock' }} me-1"></i>
                        {{ $user->status === 'ACTIVE' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Thông tin tài khoản --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold py-3">
                <i class="fas fa-id-card me-2 text-primary"></i>Thông tin tài khoản
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-start">
                    <span class="text-muted small"><i class="fas fa-hashtag me-2"></i>ID</span>
                    <strong>#{{ $user->id }}</strong>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-start">
                    <span class="text-muted small"><i class="fas fa-envelope me-2"></i>Email</span>
                    <span class="text-end small">{{ $user->email }}</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-phone me-2"></i>Điện thoại</span>
                    <strong>{{ $user->phone ?? '—' }}</strong>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-building me-2"></i>Rạp chiếu</span>
                    <span class="badge bg-light text-dark border">{{ $user->cinema->name ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-star me-2"></i>Điểm tích lũy</span>
                    <strong class="text-warning">{{ number_format($user->loyalty_points ?? 0) }} pts</strong>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-calendar-plus me-2"></i>Ngày đăng ký</span>
                    <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-clock me-2"></i>Cập nhật lần cuối</span>
                    <span class="small text-muted">{{ $user->updated_at->diffForHumans() }}</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="fas fa-envelope-open me-2"></i>Email xác thực</span>
                    @if($user->email_verified_at)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="fas fa-check me-1"></i>Đã xác thực
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="fas fa-exclamation-triangle me-1"></i>Chưa xác thực
                        </span>
                    @endif
                </li>
            </ul>
        </div>

    </div>

    {{-- ── CỘT PHẢI: Thống kê & lịch sử ── --}}
    <div class="col-xl-8 col-lg-7">

        {{-- Thống kê hoạt động --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-3">
                <div class="card border-0 shadow-sm text-center h-100" style="border-top:4px solid #6c63ff !important;">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-primary">{{ $totalBookings }}</div>
                        <div class="text-muted small">Tổng đặt vé</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card border-0 shadow-sm text-center h-100" style="border-top:4px solid #28a745 !important;">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-success">{{ $paidBookings }}</div>
                        <div class="text-muted small">Đã thanh toán</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card border-0 shadow-sm text-center h-100" style="border-top:4px solid #fd7e14 !important;">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-warning">{{ number_format($totalSpent / 1000) }}K</div>
                        <div class="text-muted small">Tổng chi tiêu</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card border-0 shadow-sm text-center h-100" style="border-top:4px solid #0dcaf0 !important;">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-info">{{ $totalReviews }}</div>
                        <div class="text-muted small">Đánh giá</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lịch sử đặt vé gần đây --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom fw-semibold py-3 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-ticket-alt me-2 text-primary"></i>Lịch sử đặt vé gần đây</span>
                <span class="badge bg-secondary rounded-pill">{{ $totalBookings }} tổng</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đặt vé</th>
                            <th>Phim</th>
                            <th>Suất chiếu</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                        <tr>
                            <td>
                                <span class="fw-mono text-primary small">{{ $booking->booking_code ?? '#' . $booking->id }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold small">{{ $booking->showtime->movie->title ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ optional($booking->showtime)->start_time ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i d/m/Y') : 'N/A' }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ number_format($booking->total_price) }}đ</strong>
                            </td>
                            <td>
                                @php
                                    $badgeMap = [
                                        'PAID'      => ['bg-success-subtle text-success border-success-subtle', 'check-circle', 'Đã thanh toán'],
                                        'PENDING'   => ['bg-warning-subtle text-warning border-warning-subtle', 'clock', 'Chờ thanh toán'],
                                        'CANCELLED' => ['bg-danger-subtle text-danger border-danger-subtle',   'times-circle', 'Đã hủy'],
                                    ];
                                    $b = $badgeMap[$booking->status] ?? ['bg-secondary text-white', 'question', $booking->status];
                                @endphp
                                <span class="badge {{ $b[0] }} border">
                                    <i class="fas fa-{{ $b[1] }} me-1"></i>{{ $b[2] }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $booking->created_at->format('d/m/Y') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-ticket-alt me-2"></i>Chưa có lịch sử đặt vé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Đánh giá gần đây --}}
        @if($user->reviews->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold py-3">
                <i class="fas fa-star me-2 text-warning"></i>Đánh giá phim gần đây
            </div>
            <ul class="list-group list-group-flush">
                @foreach($user->reviews->take(3) as $review)
                <li class="list-group-item px-4 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small mb-1">{{ $review->movie->title ?? 'Phim không xác định' }}</div>
                            <div class="text-muted small">{{ Str::limit($review->comment, 100) }}</div>
                        </div>
                        <div class="text-end ms-3 flex-shrink-0">
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"
                                       style="color:{{ $i <= $review->rating ? '#ffc107' : '#dee2e6' }};font-size:.75rem;"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggle-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const userId  = this.dataset.id;
        const current = this.dataset.status;
        const action  = current === 'ACTIVE' ? 'khóa' : 'mở khóa';

        if (!confirm(`Bạn có chắc muốn ${action} tài khoản này không?`)) return;

        const icon = this.querySelector('i');
        const origClass = this.className;
        icon.className = 'fas fa-spinner fa-spin me-1';
        this.disabled = true;

        try {
            const res = await axios.patch(`/admin/users/${userId}/toggle-status`, {}, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (res.data.success) {
                alert(res.data.message);
                window.location.reload();
            }
        } catch (err) {
            alert(err.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            icon.className = 'fas fa-' + (current === 'ACTIVE' ? 'lock' : 'unlock') + ' me-1';
            this.disabled = false;
        }
    });
});
</script>
@endpush
