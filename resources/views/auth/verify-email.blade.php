<x-guest-layout>
    <!-- Header -->
    <div class="auth-header mb-2">
        <h2 class="auth-title">Xác Minh Email</h2>
        <p class="auth-subtitle">Hoàn thành quy trình đăng ký</p>
    </div>

    <!-- Status Message -->
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            <p class="font-medium">Email xác minh đã gửi!</p>
            <p class="text-sm opacity-90">Vui lòng kiểm tra hộp thư để tìm liên kết xác minh.</p>
        </div>
    @endif

    <!-- Information Box -->
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 space-y-2">
        <p class="text-sm text-blue-400 font-medium">Bước cuối cùng</p>
        <p class="text-sm text-gray-400">
            Cảm ơn bạn đã đăng ký! Vui lòng nhấp vào liên kết trong email để xác minh tài khoản của bạn. Nếu bạn không nhận được email, vui lòng yêu cầu gửi lại.
        </p>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <span>Gửi Lại Email Xác Minh</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-secondary btn-block btn-lg">
                <span>Đăng Xuất</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- Help Text -->
    <p class="text-xs text-gray-500 text-center">
        Email bị rơi vào spam? <a href="#" class="link-primary">Hãy liên hệ với chúng tôi</a>
    </p>
</x-guest-layout>
