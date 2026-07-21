@extends('admin.layouts.app')

@section('title', 'Chi tiết Combo')
@section('page_title', 'Chi tiết Combo Bắp Nước')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Chi tiết Combo: {{ $combo->name }}</h5>
        <div>
            <a href="{{ route('admin.combos.index') }}" class="btn btn-sm btn-light text-primary fw-bold me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-4 mb-md-0">
                <div class="border rounded p-3 bg-light d-flex align-items-center justify-content-center" style="min-height: 250px;">
                    @if($combo->image)
                        <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
                    @else
                        <div class="text-muted">
                            <i class="fas fa-image fa-4x mb-2"></i>
                            <p class="mb-0">Không có ảnh</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-8">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $combo->id }}</td>
                        </tr>
                        <tr>
                            <th>Tên Combo</th>
                            <td><strong class="text-primary">{{ $combo->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Giá bán</th>
                            <td><span class="text-danger fw-bold fs-5">{{ number_format($combo->price, 0, ',', '.') }} đ</span></td>
                        </tr>
                        <tr>
                            <th>Trạng thái</th>
                            <td>
                                @if($combo->status == 'ACTIVE')
                                    <span class="badge bg-success">Đang bán (ACTIVE)</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng bán (INACTIVE)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Mô tả</th>
                            <td>{!! nl2br(e($combo->description)) ?: '<em class="text-muted">Không có mô tả</em>' !!}</td>
                        </tr>
                        <tr>
                            <th>Ngày tạo</th>
                            <td>{{ $combo->created_at ? $combo->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Ngày cập nhật</th>
                            <td>{{ $combo->updated_at ? $combo->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
