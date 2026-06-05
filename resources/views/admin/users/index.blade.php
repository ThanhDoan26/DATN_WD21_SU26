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
