@extends('admin.layouts.app')

@section('title', 'Quản lý Combo')
@section('page_title', 'Danh sách Combo Bắp Nước')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách Combo</h5>
        <a href="{{ route('admin.combos.create') }}" class="btn btn-sm btn-light text-primary fw-bold">
            <i class="fas fa-plus me-1"></i> Thêm mới
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">Hình ảnh</th>
                        <th>Tên Combo</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combos as $combo)
                    <tr>
                        <td>{{ $combo->id }}</td>
                        <td>
                            @if($combo->image)
                                <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded">
                            @else
                                <span class="text-muted">Không có ảnh</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $combo->name }}</strong>
                            <div class="text-muted small">{{ Str::limit($combo->description, 50) }}</div>
                        </td>
                        <td>{{ number_format($combo->price, 0, ',', '.') }} đ</td>
                        <td>
                            @if($combo->status == 'ACTIVE')
                                <span class="badge bg-success">Đang bán</span>
                            @else
                                <span class="badge bg-secondary">Ngừng bán</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa combo này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu Combo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $combos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
