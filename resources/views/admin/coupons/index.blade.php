@extends('admin.layouts.app')

@section('title', 'Quản lý Mã Giảm Giá')
@section('page_title', 'Danh sách Mã Giảm Giá')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Mã Giảm Giá</h5>
        <div>
            <a href="{{ route('admin.coupons.trashed') }}" class="btn btn-secondary btn-sm me-2">
                <i class="fas fa-trash"></i> Thùng rác
            </a>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif --}}

        <form method="GET" action="{{ route('admin.coupons.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="code" class="form-control" placeholder="Tìm theo mã..." value="{{ request('code') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Khóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-search"></i> Lọc</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Mã code</th>
                        <th>Loại giảm</th>
                        <th>Giá trị</th>
                        <th>Đơn tối thiểu</th>
                        <th>Số lượng còn</th>
                        <th>Đã dùng</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td><strong>{{ $coupon->code }}</strong></td>
                            <td>
                                @if($coupon->type == 'percent')
                                    <span class="badge bg-info">Phần trăm (%)</span>
                                @else
                                    <span class="badge bg-secondary">Cố định (VNĐ)</span>
                                @endif
                            </td>
                            <td>
                                @if($coupon->type == 'percent')
                                    {{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                                @else
                                    {{ number_format($coupon->value, 0, ',', '.') }} đ
                                @endif
                            </td>
                            <td>{{ number_format($coupon->min_order_value, 0, ',', '.') }} đ</td>
                            <td>{{ $coupon->quantity }}</td>
                            <td>{{ $coupon->used_count }}</td>
                            <td style="font-size: 0.85rem;">
                                Bắt đầu: {{ $coupon->start_date ? $coupon->start_date->format('d/m/Y H:i') : '-' }}<br>
                                Kết thúc: <span class="{{ $coupon->end_date && $coupon->end_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $coupon->end_date ? $coupon->end_date->format('d/m/Y H:i') : '-' }}
                                </span>
                            </td>
                            <td>
                                @if ($coupon->status === 'ACTIVE')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Khóa</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-warning btn-sm mb-1" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xoá mã này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Không có dữ liệu mã giảm giá.</td>
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
