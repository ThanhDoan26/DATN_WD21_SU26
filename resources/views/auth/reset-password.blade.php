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
        <input type="hidden" name="token" value="{{ $request->route('token') ?? $request->token }}">

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
            </div>
            @error('password_confirmation')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Show Password Checkbox -->
        <div style="margin-top: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" style="cursor: pointer; width: 16px; height: 16px;">
            <label for="show-password" style="cursor: pointer; font-size: 14px; color: #9ca3af; user-select: none;">Hiển thị mật khẩu</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            <span>Cập Nhật Mật Khẩu</span>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </button>
    </form>

    <script>
    function togglePasswordVisibility() {
        var passwordField = document.getElementById("password");
        var confirmField = document.getElementById("password_confirmation");
        var checkbox = document.getElementById("show-password");
        
        if (checkbox.checked) {
            passwordField.type = "text";
            confirmField.type = "text";
        } else {
            passwordField.type = "password";
            confirmField.type = "password";
        }
    }
    </script>
</x-guest-layout>
