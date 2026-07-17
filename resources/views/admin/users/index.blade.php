@extends('admin.layouts.app')

@section('title', 'Users - Admin')
@section('page_title', 'Users Management')

@section('content')
{{-- Breadcrumb --}}
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
    </nav>
</div>

{{-- Page Title --}}
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

{{-- Stats Cards --}}
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c63ff !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:12px;background:rgba(108,99,255,.15);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users fa-lg" style="color:#6c63ff;"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng người dùng</div>
                    <div class="fw-bold fs-4">{{ $totalUsers }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:12px;background:rgba(40,167,69,.15);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-check fa-lg" style="color:#28a745;"></i>
                </div>
                <div>
                    <div class="text-muted small">Đang hoạt động</div>
                    <div class="fw-bold fs-4">{{ $activeUsers }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:12px;background:rgba(220,53,69,.15);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-slash fa-lg" style="color:#dc3545;"></i>
                </div>
                <div>
                    <div class="text-muted small">Bị khóa</div>
                    <div class="fw-bold fs-4">{{ $inactiveUsers }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filter --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                {{-- Tìm kiếm --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Tìm kiếm</label>
                    <input type="text"
                           name="search"
                           id="searchInput"
                           class="form-control"
                           placeholder="Tên, email, số điện thoại..."
                           value="{{ request('search') }}">
                </div>

                {{-- Lọc theo vai trò --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-shield-alt me-1"></i>Vai trò</label>
                    <select name="role_id" class="form-select" id="roleFilter">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc theo trạng thái --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-toggle-on me-1"></i>Trạng thái</label>
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">-- Tất cả --</option>
                        <option value="ACTIVE"   {{ request('status') === 'ACTIVE'   ? 'selected' : '' }}>✅ Active</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>🔒 Inactive</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>

        {{-- Hiển thị số kết quả --}}
        @if(request()->hasAny(['search','role_id','status']))
        <div class="mt-3 pt-3 border-top">
            <span class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Tìm thấy <strong>{{ $users->total() }}</strong> người dùng
                @if(request('search'))
                    với từ khóa "<strong>{{ request('search') }}</strong>"
                @endif
                @if(request('status'))
                    — trạng thái <strong>{{ request('status') }}</strong>
                @endif
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Users Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <span class="fw-semibold"><i class="fas fa-table me-2"></i>Danh sách Người Dùng</span>
        <span class="badge bg-secondary rounded-pill">{{ $users->total() }} người dùng</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">#</th>
                    <th>Thông tin</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Rạp</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width:130px" class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr id="user-row-{{ $user->id }}">
                    <td><span class="text-muted small">#{{ $user->id }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#a855f7);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if($user->phone)
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $user->phone }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $user->email }}</td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge bg-danger">ADMIN</span>
                        @elseif($user->isManager())
                            <span class="badge bg-warning text-dark">MANAGER</span>
                        @elseif($user->isStaff())
                            <span class="badge bg-info text-dark">STAFF</span>
                        @else
                            <span class="badge bg-secondary">USER</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $user->cinema?->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($user->status === 'ACTIVE')
                            <span class="badge bg-success-subtle text-success border border-success-subtle" id="status-badge-{{ $user->id }}">
                                <i class="fas fa-circle me-1" style="font-size:.5rem;"></i>Active
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" id="status-badge-{{ $user->id }}">
                                <i class="fas fa-circle me-1" style="font-size:.5rem;"></i>Inactive
                            </span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}<br>{{ $user->created_at->format('H:i') }}</small>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            {{-- Xem chi tiết --}}
                            <a href="{{ route('admin.users.show', $user->id) }}"
                               class="btn btn-sm btn-outline-info"
                               title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- Sửa --}}
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Khóa / Mở khóa --}}
                            @if(auth()->id() !== $user->id)
                            <button type="button"
                                    class="btn btn-sm btn-outline-{{ $user->status === 'ACTIVE' ? 'secondary' : 'success' }} toggle-status-btn"
                                    data-id="{{ $user->id }}"
                                    data-status="{{ $user->status }}"
                                    id="toggle-btn-{{ $user->id }}"
                                    title="{{ $user->status === 'ACTIVE' ? 'Khóa' : 'Mở khóa' }}">
                                <i class="fas fa-{{ $user->status === 'ACTIVE' ? 'lock' : 'unlock' }}"></i>
                            </button>
                            @endif

                            {{-- Xóa --}}
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Xác nhận xóa người dùng {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-users-slash" style="font-size:2.5rem;color:#ccc;"></i>
                        <p class="text-muted mt-2 mb-0">
                            @if(request()->hasAny(['search','role_id','status']))
                                Không tìm thấy người dùng phù hợp.
                                <a href="{{ route('admin.users.index') }}">Xóa bộ lọc</a>
                            @else
                                Chưa có người dùng nào.
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle Status ────────────────────────────────
    document.querySelectorAll('.toggle-status-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const userId  = this.dataset.id;
            const current = this.dataset.status;
            const action  = current === 'ACTIVE' ? 'khóa' : 'mở khóa';

            if (!confirm(`Bạn có chắc muốn ${action} người dùng này không?`)) return;

            const icon = this.querySelector('i');
            const orig = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            this.disabled = true;

            try {
                const res = await axios.patch(`/admin/users/${userId}/toggle-status`, {}, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (res.data.success) {
                    const newStatus = res.data.status;
                    this.dataset.status = newStatus;

                    // Cập nhật nút
                    if (newStatus === 'ACTIVE') {
                        this.className = 'btn btn-sm btn-outline-secondary toggle-status-btn';
                        this.title     = 'Khóa';
                        icon.className = 'fas fa-lock';
                    } else {
                        this.className = 'btn btn-sm btn-outline-success toggle-status-btn';
                        this.title     = 'Mở khóa';
                        icon.className = 'fas fa-unlock';
                    }
                    this.disabled = false;

                    // Cập nhật badge trạng thái
                    const badge = document.getElementById(`status-badge-${userId}`);
                    if (badge) {
                        if (newStatus === 'ACTIVE') {
                            badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
                            badge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:.5rem;"></i>Active';
                        } else {
                            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                            badge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:.5rem;"></i>Inactive';
                        }
                    }
                }
            } catch (err) {
                alert(err.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                icon.className = orig;
                this.disabled  = false;
            }
        });
    });

    // ── Auto-submit filter khi thay đổi select ────────
    ['roleFilter', 'statusFilter'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>
@endpush
