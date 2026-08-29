@extends('layouts.manager')

@section('title', 'Thùng rác Mã Giảm Giá - Manager')
@section('page_title', 'Thùng rác Mã Giảm Giá')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-trash-alt text-danger me-2"></i>Thùng rác Mã Giảm Giá</h5>
        <a href="{{ route('manager.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('manager.coupons.trashed') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="code" class="form-control" placeholder="Tìm theo mã code..." value="{{ request('code') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-search me-1"></i> Tìm kiếm</button>
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
                        <th>Ngày Xóa</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td><strong class="text-danger font-monospace fs-6">{{ $coupon->code }}</strong></td>
                            <td>
                                @if($coupon->type == 'percent')
                                    <span class="badge bg-info bg-opacity-10 text-info">Phần trăm (%)</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success">Cố định (VNĐ)</span>
                                @endif
                            </td>
                            <td>
                                @if($coupon->type == 'percent')
                                    {{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                                @else
                                    {{ number_format($coupon->value, 0, ',', '.') }}đ
                                @endif
                            </td>
                            <td>{{ $coupon->deleted_at ? $coupon->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('manager.coupons.restore', $coupon->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm me-1" title="Khôi phục">
                                        <i class="fas fa-undo me-1"></i> Khôi phục
                                    </button>
                                </form>
                                <form action="{{ route('manager.coupons.forceDelete', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ xóa VĨNH VIỄN mã giảm giá này khỏi hệ thống. Bạn chắc chắn chứ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Xóa vĩnh viễn">
                                        <i class="fas fa-times me-1"></i> Xóa hẳn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-trash-restore fs-3 mb-2 d-block"></i>
                                Thùng rác trống. Không có mã giảm giá nào bị xóa.
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
