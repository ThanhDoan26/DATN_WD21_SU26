@extends('admin.layouts.app')

@section('title', 'Chi tiết Đánh giá Combo')
@section('page_title', 'Chi tiết Đánh giá: ' . $combo->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-box h-100">
            <div class="stat-number text-warning">{{ number_format($stats['avg'], 1) }} <i class="fas fa-star text-sm"></i></div>
            <div class="stat-label">Điểm trung bình</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box h-100">
            <div class="stat-number text-info">{{ $stats['total'] }}</div>
            <div class="stat-label">Tổng lượt đánh giá</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 mb-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex align-items-center mb-1 text-sm">
                    <div class="text-warning me-2" style="width: 60px;">5 <i class="fas fa-star"></i></div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] ? ($stats['star5'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="ms-2 text-muted" style="width: 30px; text-align: right">{{ $stats['star5'] }}</div>
                </div>
                <div class="d-flex align-items-center mb-1 text-sm">
                    <div class="text-warning me-2" style="width: 60px;">4 <i class="fas fa-star"></i></div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] ? ($stats['star4'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="ms-2 text-muted" style="width: 30px; text-align: right">{{ $stats['star4'] }}</div>
                </div>
                <div class="d-flex align-items-center mb-1 text-sm">
                    <div class="text-warning me-2" style="width: 60px;">3 <i class="fas fa-star"></i></div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] ? ($stats['star3'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="ms-2 text-muted" style="width: 30px; text-align: right">{{ $stats['star3'] }}</div>
                </div>
                <div class="d-flex align-items-center mb-1 text-sm">
                    <div class="text-warning me-2" style="width: 60px;">2 <i class="fas fa-star"></i></div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] ? ($stats['star2'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="ms-2 text-muted" style="width: 30px; text-align: right">{{ $stats['star2'] }}</div>
                </div>
                <div class="d-flex align-items-center text-sm">
                    <div class="text-warning me-2" style="width: 60px;">1 <i class="fas fa-star"></i></div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] ? ($stats['star1'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="ms-2 text-muted" style="width: 30px; text-align: right">{{ $stats['star1'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white text-dark border-bottom">
        <form method="GET" action="{{ route('admin.combo-reviews.show', $combo) }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Tìm người dùng</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Tên hoặc Email">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Số sao</label>
                <select name="rating" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @for($i=5; $i>=1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Từ ngày</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Đến ngày</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Lọc kết quả</button>
                @if(request()->anyFilled(['search', 'rating', 'date_from', 'date_to']))
                    <a href="{{ route('admin.combo-reviews.show', $combo) }}" class="btn btn-sm btn-link w-100 text-muted mt-1 text-decoration-none">Xóa lọc</a>
                @endif
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th width="80">ID</th>
                        <th>Khách hàng</th>
                        <th>Mã đơn hàng</th>
                        <th width="150" class="text-center">Đánh giá</th>
                        <th>Bình luận</th>
                        <th width="150">Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>#{{ $review->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $review->user->name }}</div>
                            <div class="small text-muted">{{ $review->user->email }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.bookings.show', $review->booking_id) }}" class="text-primary text-decoration-none">
                                #{{ $review->booking->booking_code ?? $review->booking_id }}
                            </a>
                        </td>
                        <td class="text-center text-warning">
                            @for($i=1; $i<=5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-muted opacity-25' }}"></i>
                            @endfor
                        </td>
                        <td>
                            <div class="small text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $review->comment }}">
                                {{ $review->comment ?: 'Không có bình luận' }}
                            </div>
                        </td>
                        <td class="text-muted small">
                            {{ $review->updated_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Không tìm thấy lượt đánh giá nào phù hợp</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
