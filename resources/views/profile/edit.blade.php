@if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isStaff())
    @extends(auth()->user()->isAdmin() ? 'admin.layouts.app' : (auth()->user()->isManager() ? 'layouts.manager' : 'layouts.staff'))

    @section('title', 'Thông Tin Cá Nhân')
    @section('page_title', 'Hồ Sơ Cá Nhân')

    @section('content')
    <div class="container-fluid px-0">
        <!-- Success Alerts -->
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>Cập nhật thông tin tài khoản thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Cập nhật mật khẩu thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Edit Forms -->
            <div class="col-12 col-xl-8">
                <!-- Profile Information Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-user-edit me-2"></i>Thông Tin Tài Khoản
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold text-muted small">Họ và Tên</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-muted small">Địa chỉ Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold text-muted small">Số Điện Thoại</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="fas fa-save me-2"></i>Lưu Thay Đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Update Password Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-key me-2"></i>Đổi Mật Khẩu
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <label for="update_password_current_password" class="form-label fw-semibold text-muted small">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                       id="update_password_current_password" name="current_password" autocomplete="current-password" required>
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="update_password_password" class="form-label fw-semibold text-muted small">Mật khẩu mới</label>
                                <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                       id="update_password_password" name="password" autocomplete="new-password" required>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="update_password_password_confirmation" class="form-label fw-semibold text-muted small">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                       id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="fas fa-save me-2"></i>Cập Nhật Mật Khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Profile Card & Danger Zone -->
            <div class="col-12 col-xl-4">
                <!-- User Profile Summary Card -->
                <div class="card border-0 shadow-sm text-center p-4 mb-4">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="position-relative d-inline-block">
                            <div class="user-avatar-lg rounded-circle text-white d-flex align-items-center justify-content-center fs-1 fw-bold" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color, #9333ea) 0%, var(--brand-color, #ff4d7d) 100%);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle" style="width: 18px; height: 18px; transform: translate(-5%, -5%); animation: pulse-online 2s infinite;" title="Hoạt động"></span>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        @if($user->isAdmin())
                            <span class="badge bg-primary px-3 py-2 fs-6">ADMIN</span>
                        @elseif($user->isManager())
                            <span class="badge bg-success px-3 py-2 fs-6">MANAGER</span>
                        @elseif($user->isStaff())
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">STAFF</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 fs-6">USER</span>
                        @endif
                    </div>
                    
                    <hr class="my-3 border-light">
                    
                    <div class="text-start">
                        <p class="text-muted small mb-2"><strong><i class="fas fa-phone me-2 text-primary"></i>Số điện thoại:</strong> {{ $user->phone ?? 'Chưa cập nhật' }}</p>
                        @if($user->cinema)
                            <p class="text-muted small mb-2"><strong><i class="fas fa-building me-2 text-primary"></i>Chi nhánh rạp:</strong> {{ $user->cinema->name }}</p>
                        @endif
                        <p class="text-muted small mb-2"><strong><i class="fas fa-calendar-day me-2 text-primary"></i>Ngày tham gia:</strong> {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</p>
                        <p class="text-muted small mb-0"><strong><i class="fas fa-coins me-2 text-primary"></i>Điểm tích lũy:</strong> {{ number_format($user->loyalty_points ?? 0) }}đ</p>
                    </div>
                </div>

                <!-- Danger Zone Card -->
                <div class="card border-0 shadow-sm border-start border-danger border-3">
                    <div class="card-body p-4">
                        <h5 class="card-title text-danger fw-bold mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>Khu Vực Nguy Hiểm
                        </h5>
                        <p class="text-muted small mb-3">
                            Một khi bạn xóa tài khoản, tất cả tài nguyên và dữ liệu liên quan sẽ bị xóa vĩnh viễn.
                        </p>
                        <button type="button" class="btn btn-outline-danger w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash-alt me-2"></i>Xóa Tài Khoản
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Xác Nhận Xóa Tài Khoản
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark fw-semibold">Bạn có chắc chắn muốn xóa tài khoản của mình?</p>
                        <p class="text-muted small">
                            Nhập mật khẩu hiện tại của bạn để xác nhận hành động này. Thao tác này không thể hoàn tác.
                        </p>
                        <div class="mt-3">
                            <label for="password" class="form-label fw-bold text-muted small">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Nhập mật khẩu..." required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">
                            <i class="fas fa-trash-alt me-2"></i>Xác Nhận Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endsection

    @section('extra_css')
    <style>
        .badge.bg-primary { background-color: var(--primary-color, #9333ea) !important; color: #ffffff !important; }
        .badge.bg-success { background-color: #10b981 !important; color: #ffffff !important; }
        .badge.bg-warning { background-color: #f59e0b !important; color: #1e293b !important; }
        
        .form-control:focus {
            border-color: var(--primary-color, #9333ea) !important;
            box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.12) !important;
        }

        @keyframes pulse-online {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
    @endsection
@else
    <!-- Default Breeze layout for normal users -->
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@endif
