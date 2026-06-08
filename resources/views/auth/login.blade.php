<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - CineTicket</title>
    <link rel="stylesheet" href="{{ asset('css/auth-styles.css') }}">
</head>
<body>
<div class="auth-container">
    <div class="form-card">
        <!-- Header -->
        <div class="form-header">
            <div class="logo">🎬</div>
            <h1 class="form-title">Đăng Nhập</h1>
            <p class="form-subtitle">Chào mừng quay trở lại CineTicket</p>
        </div>

        <!-- Status Message -->
        @if ($status = session('status'))
            <div style="padding: 12px 16px; background: #D1FAE5; border-left: 4px solid #10B981; border-radius: 10px; margin-bottom: 20px; font-size: 14px; color: #065F46; animation: fadeInUp 0.5s ease-out;">
                {{ $status }}
            </div>
        @endif

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">
                    Địa chỉ Email <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="form-input"
                        placeholder="you@example.com"
                    />
                </div>
                <div id="emailError" class="form-error hidden"></div>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">
                    Mật Khẩu <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="form-input"
                        placeholder="Nhập mật khẩu của bạn"
                    />
                    <button id="togglePassword" type="button" class="toggle-password" aria-label="Toggle password visibility">
                        👁️
                    </button>
                </div>
                <div id="passwordError" class="form-error hidden"></div>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember & Forgot -->
            <div class="remember-forgot">
                <div class="checkbox-group">
                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        class="checkbox-input"
                        {{ old('remember') ? 'checked' : '' }}
                    />
                    <label for="remember" class="checkbox-label">Ghi nhớ tôi</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Quên mật khẩu?</a>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="btn-group">
                <button type="submit" class="btn-primary">
                    <span class="btn-text">Đăng Nhập</span>
                    <div class="btn-loader"></div>
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="divider">Hoặc</div>

        <!-- Social Login -->
        <div class="social-login">
            <button type="button" class="btn-social">
                <span class="social-icon">G</span>
                <span class="social-text">Google</span>
            </button>
            <button type="button" class="btn-social">
                <span class="social-icon">f</span>
                <span class="social-text">Facebook</span>
            </button>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            <p class="footer-text">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="footer-link">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</div>

<script src="{{ asset('js/auth-script.js') }}"></script>
</body>
</html>
