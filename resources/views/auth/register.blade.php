<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - CineTicket</title>
    <link rel="stylesheet" href="{{ asset('css/auth-styles.css') }}">
</head>
<body>
<div class="auth-container">
    <div class="form-card">
        <!-- Header -->
        <div class="form-header">
            <div class="logo">🎬</div>
            <h1 class="form-title">Đăng Ký</h1>
            <p class="form-subtitle">Tạo tài khoản CineTicket của bạn hôm nay</p>
        </div>

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label">
                    Họ và Tên <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        class="form-input"
                        placeholder="Nguyễn Văn A"
                    />
                </div>
                <div id="nameError" class="form-error hidden"></div>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

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
                        autocomplete="new-password"
                        class="form-input"
                        placeholder="Tối thiểu 8 ký tự"
                    />
                    <button id="togglePassword" type="button" class="toggle-password" aria-label="Toggle password visibility">
                        👁️
                    </button>
                </div>

                <!-- Password Strength -->
                <div id="passwordStrength" class="password-strength">
                    <div class="strength-bar">
                        <div id="strengthFill" class="strength-fill weak"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Yếu</div>
                </div>

                <div id="passwordError" class="form-error hidden"></div>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label">
                    Xác Nhận Mật Khẩu <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="form-input"
                        placeholder="Nhập lại mật khẩu"
                    />
                    <button id="toggleConfirmPassword" type="button" class="toggle-password" aria-label="Toggle password visibility">
                        👁️
                    </button>
                </div>
                <div id="password_confirmationError" class="form-error hidden"></div>
                @error('password_confirmation')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Terms & Conditions -->
            <div class="checkbox-group">
                <input
                    id="agree_terms"
                    type="checkbox"
                    name="agree_terms"
                    required
                    class="checkbox-input"
                />
                <label for="agree_terms" class="checkbox-label">
                    Tôi đồng ý với
                    <a href="#">Điều khoản Dịch vụ</a>
                    và
                    <a href="#">Chính sách Bảo mật</a>
                </label>
            </div>
            <div id="agree_termsError" class="form-error hidden"></div>

            <!-- Submit Button -->
            <div class="btn-group">
                <button type="submit" class="btn-primary">
                    <span class="btn-text">Tạo Tài Khoản</span>
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
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="footer-link">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
</div>

<script src="{{ asset('js/auth-script.js') }}"></script>
</body>
</html>
