@extends('admin.layouts.app')

@section('title', 'Thùng rác - Mã Giảm Giá')
@section('page_title', 'Thùng rác: Mã Giảm Giá đã xóa')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Mã Giảm Giá đã xóa</h5>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.coupons.trashed') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="code" class="form-control" placeholder="Tìm theo mã..." value="{{ request('code') }}">
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
                        <th>Ngày xóa</th>
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
                            <td>{{ $coupon->deleted_at ? $coupon->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <form action="{{ route('admin.coupons.restore', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục mã này không?');">
                                    @csrf
                                    <button class="btn btn-success btn-sm mb-1" title="Khôi phục">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.coupons.forceDelete', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn mã này không? Hành động này không thể hoàn tác!');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1" title="Xóa vĩnh viễn">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Thùng rác trống.</td>
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
