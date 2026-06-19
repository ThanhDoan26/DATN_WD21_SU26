<x-guest-layout>
    <!-- Header -->
    <div class="auth-header mb-2">
        <h2 class="auth-title">Quên Mật Khẩu?</h2>
        <p class="auth-subtitle">Chúng tôi sẽ giúp bạn khôi phục tài khoản</p>
    </div>

    <!-- Status Messages -->
    @if ($status = session('status'))
        <div class="alert alert-success">
            <p class="font-medium">Thành công!</p>
            <p class="text-sm opacity-90">{{ $status }}</p>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <p class="text-sm text-gray-400 leading-relaxed bg-gray-800/30 p-4 rounded-lg border border-gray-700/50">
            Nhập địa chỉ email của bạn và chúng tôi sẽ gửi một liên kết để đặt lại mật khẩu của bạn.
        </p>

        <!-- Email -->
        <div class="form-group">
            <label for="email" class="form-label">Địa chỉ Email</label>
            <div class="form-input-wrapper">
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
                <svg class="form-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            <span>Gửi Liên Kết Đặt Lại</span>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </button>

        <!-- Back to Login -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="link-primary text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Quay lại đăng nhập
            </a>
        </div>
    </form>

    <!-- Help Section -->
    <div class="border-t border-gray-700 pt-5 mt-6">
        <p class="text-xs text-gray-500 text-center">
            Vẫn gặp vấn đề? <a href="#" class="link-primary">Liên hệ với chúng tôi</a>
        </p>
    </div>
</x-guest-layout>
