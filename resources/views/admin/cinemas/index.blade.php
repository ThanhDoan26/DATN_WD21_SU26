@extends('admin.layouts.app')

@section('title', 'Cinemas - Admin')
@section('page_title', 'Cinemas Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Cinemas</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-building"></i> Danh sách Rạp Chiếu Phim</h2>
        <p class="text-muted" style="margin-top: 5px;">Xem danh sách tất cả các cụm rạp trong hệ thống</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.cinemas.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Thêm Rạp
        </a>
    </div>
</div>

<!-- Cinemas Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Rạp
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Rạp</th>
                    <th>Địa chỉ</th>
                    <th>Thành phố</th>
                    <th>Điện thoại</th>
                    <th>Trạng thái</th>
                    <th>Tạo lúc</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cinemas ?? [] as $cinema)
                <tr>
                    <td><strong>#{{ $cinema->id }}</strong></td>
                    <td>
                        <strong>{{ $cinema->name }}</strong>
                    </td>
                    <td>{{ $cinema->address }}</td>
                    <td>
                        <span class="badge bg-info">{{ $cinema->city }}</span>
                    </td>
                    <td>{{ $cinema->phone ?? 'N/A' }}</td>
                    <td>
                        @if($cinema->status === 'ACTIVE')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $cinema->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.cinemas.show', $cinema->id) }}" class="btn btn-info btn-sm" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.cinemas.edit', $cinema->id) }}" class="btn btn-warning btn-sm" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="deleteRecord('{{ route('admin.cinemas.destroy', $cinema->id) }}')" 
                                    title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có rạp nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($cinemas && $cinemas->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $cinemas->links() }}
</div>
@endif

<script>
function deleteRecord(deleteUrl) {
    if (confirm('Bạn có chắc chắn muốn xóa?')) {
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Lỗi xóa rạp!');
            }
        }).catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
