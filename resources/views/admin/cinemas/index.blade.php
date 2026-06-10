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
<div class="page-title d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-building"></i> Danh sách Rạp Chiếu Phim</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Xem danh sách tất cả các cụm rạp trong hệ thống</p>
    </div>
    <div>
        <a href="{{ route('admin.cinemas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Rạp Mới</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
                        <a href="{{ route('admin.cinemas.show', $cinema->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.cinemas.edit', $cinema->id) }}" class="btn btn-sm btn-warning" title="Sửa rạp">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.cinemas.destroy', $cinema->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa rạp này không?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa rạp">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
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
