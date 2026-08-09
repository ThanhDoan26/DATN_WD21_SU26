<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Quên Mật Khẩu?</h2>
        <p class="text-slate-400">Chúng tôi sẽ giúp bạn khôi phục tài khoản</p>
    </div>

    <!-- Status Messages -->
    @if ($status = session('status'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
            {{ $status }}
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <p class="text-sm text-slate-400 leading-relaxed bg-slate-800/30 p-4 rounded-lg border border-slate-700/50 mb-6">
            Nhập địa chỉ email của bạn và chúng tôi sẽ gửi một liên kết để đặt lại mật khẩu của bạn.
        </p>

        <!-- Email -->
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Địa chỉ Email <span class="text-primary">*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-primary hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2">
            <span>Gửi Liên Kết Đặt Lại</span>
            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </button>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Quay lại đăng nhập
            </a>
        </div>
    </form>

    <!-- Help Section -->
    <div class="border-t border-slate-700 pt-5 mt-6">
        <p class="text-xs text-slate-500 text-center">
            Vẫn gặp vấn đề? <a href="#" class="text-primary hover:text-red-400 font-medium transition-colors">Liên hệ với chúng tôi</a>
        </p>
    </div>
</x-guest-layout>
