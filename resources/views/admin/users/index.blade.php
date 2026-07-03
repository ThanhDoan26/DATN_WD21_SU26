@extends('admin.layouts.app')

@section('title', 'Users - Admin')
@section('page_title', 'Users Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-users"></i> Danh sách Người Dùng</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Quản lý tài khoản người dùng và nhân sự</p>
    </div>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Thêm Mới
        </a>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Người Dùng
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Rạp</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr>
                    <td><strong>#{{ $user->id }}</strong></td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->phone)
                            <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $user->phone }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $user->email }}
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge bg-danger">ADMIN</span>
                        @elseif($user->isManager())
                            <span class="badge bg-warning text-dark">MANAGER</span>
                        @elseif($user->isStaff())
                            <span class="badge bg-info">STAFF</span>
                        @else
                            <span class="badge bg-secondary">USER</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $user->cinema->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($user->status === 'ACTIVE')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Nút Khóa / Mở khóa -->
                            @if(auth()->id() !== $user->id)
                            <button type="button" class="btn btn-sm btn-{{ $user->status === 'ACTIVE' ? 'secondary' : 'success' }} toggle-status-btn" 
                                    data-id="{{ $user->id }}" 
                                    data-status="{{ $user->status }}"
                                    title="{{ $user->status === 'ACTIVE' ? 'Khóa' : 'Mở khóa' }}">
                                <i class="fas fa-{{ $user->status === 'ACTIVE' ? 'lock' : 'unlock' }}"></i>
                            </button>
                            @endif

                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa người dùng này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-users" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có người dùng nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(method_exists($users, 'hasPages') && $users->hasPages())
    <div class="card-footer pb-0">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.toggle-status-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            const actionText = currentStatus === 'ACTIVE' ? 'Khóa' : 'Mở khóa';
            
            if (!confirm(`Bạn có chắc muốn ${actionText.toLowerCase()} người dùng này không?`)) {
                return;
            }
            
            try {
                // Hiển thị loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                const response = await axios.patch(`/admin/users/${userId}/toggle-status`, {}, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.data.success) {
                    alert(response.data.message);
                    window.location.reload(); // Hoặc cập nhật UI bằng JS thuần
                }
            } catch (error) {
                alert(error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                window.location.reload();
            }
        });
    });
});
</script>
@endpush
