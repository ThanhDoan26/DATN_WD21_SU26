@extends('layouts.manager')

@section('title', 'Quản lý Mã Giảm Giá - Manager')
@section('page_title', 'Danh sách Mã Giảm Giá')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tags text-primary me-2"></i>Danh sách Mã Giảm Giá</h5>
        <div>
            <a href="{{ route('manager.coupons.trashed') }}" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-trash me-1"></i> Thùng rác
            </a>
            <a href="{{ route('manager.coupons.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Thêm mã mới
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('manager.coupons.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="code" class="form-control border-start-0" placeholder="Tìm kiếm theo mã code..." value="{{ request('code') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Hoạt động (ACTIVE)</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Khóa (INACTIVE)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter me-1"></i> Lọc dữ liệu</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Mã Code</th>
                        <th>Loại Giảm</th>
                        <th>Giá Trị</th>
                        <th>Đơn Tối Thiểu</th>
                        <th>Số Lượng Còn</th>
                        <th>Đã Dùng</th>
                        <th>Thời Gian Hiệu Lực</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>
                                <span class="fw-bold text-primary font-monospace fs-6">{{ $coupon->code }}</span>
                            </td>
                            <td>
                                @if($coupon->type == 'percent')
                                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1"><i class="fas fa-percent me-1"></i>Phần trăm</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1"><i class="fas fa-money-bill-wave me-1"></i>Cố định (VNĐ)</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">
                                    @if($coupon->type == 'percent')
                                        {{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                                        @if($coupon->max_discount_amount)
                                            <br><small class="text-muted fw-normal">(Tối đa: {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}đ)</small>
                                        @endif
                                    @else
                                        {{ number_format($coupon->value, 0, ',', '.') }}đ
                                    @endif
                                </strong>
                            </td>
                            <td>{{ number_format($coupon->min_order_value, 0, ',', '.') }}đ</td>
                            <td>
                                @if($coupon->quantity == 0 || $coupon->quantity === null)
                                    <span class="badge bg-secondary">Vô hạn</span>
                                @else
                                    <span class="fw-bold">{{ number_format($coupon->quantity - $coupon->used_count) }}</span> / {{ number_format($coupon->quantity) }}
                                @endif
                            </td>
                            <td>{{ number_format($coupon->used_count) }}</td>
                            <td style="font-size: 0.83rem;">
                                <div><i class="far fa-clock text-muted me-1"></i> Từ: {{ $coupon->start_date ? $coupon->start_date->format('d/m/Y H:i') : '-' }}</div>
                                <div><i class="far fa-calendar-times text-muted me-1"></i> Đến: 
                                    <span class="{{ $coupon->end_date && $coupon->end_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ $coupon->end_date ? $coupon->end_date->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if ($coupon->end_date && $coupon->end_date->isPast())
                                    <span class="badge bg-secondary">Hết hạn</span>
                                @elseif ($coupon->status === 'ACTIVE')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Khóa</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('manager.coupons.edit', $coupon->id) }}" class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('manager.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xoá mã này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Xóa tạm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-tags fs-3 mb-2 d-block"></i>
                                Không tìm thấy dữ liệu mã giảm giá nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $coupons->links() }}
        </div>
    </div>
</div>
@endsection
