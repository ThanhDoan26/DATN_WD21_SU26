<x-guest-layout>
    <!-- Header -->
    <div class="auth-header mb-2">
        <h2 class="auth-title">Đặt Lại Mật Khẩu</h2>
        <p class="auth-subtitle">Tạo mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Hidden Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div class="form-group">
            <label for="email" class="form-label">Địa chỉ Email</label>
            <div class="form-input-wrapper">
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="form-input"
                    placeholder="you@example.com"
                />
                <svg class="form-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="form-group">
            <label for="password" class="form-label">Mật Khẩu Mới</label>
            <div class="form-input-wrapper">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="form-input"
                    placeholder="Nhập mật khẩu mới"
                />
                <svg class="form-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <p class="form-hint">Tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường và số</p>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Xác Nhận Mật Khẩu</label>
            <div class="form-input-wrapper">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="form-input"
                    placeholder="Nhập lại mật khẩu"
                />
                <svg class="form-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            @error('password_confirmation')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            <span>Cập Nhật Mật Khẩu</span>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </button>
    </form>
</x-guest-layout>
