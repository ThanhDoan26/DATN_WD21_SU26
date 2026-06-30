<x-guest-layout>
    <!-- Status Message -->
    @if ($status = session('status'))
        <div class="mb-4 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
            {{ $status }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="remember" value="1">

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-primary">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="you@example.com">
            @error('email')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu <span class="text-primary">*</span></label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Nhập mật khẩu">
            @error('password')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
            
            <div class="flex items-center justify-between mt-3">
                <!-- Show Password Checkbox -->
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900">
                    <span class="ml-2 text-xs text-slate-400 hover:text-slate-300 transition-colors">Hiển thị mật khẩu</span>
                </label>

                <!-- Forgot Password Link -->
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary hover:text-red-400 transition-colors">Quên mật khẩu?</a>
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-primary hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2">
            <i class="fas fa-sign-in-alt"></i> Đăng Nhập
        </button>
    </form>

    <script>
    function togglePasswordVisibility() {
        var passwordField = document.getElementById("password");
        var checkbox = document.getElementById("show-password");
        if (checkbox.checked) {
            passwordField.type = "text";
        } else {
            passwordField.type = "password";
        }
    }
    </script>

    <!-- Divider -->
    <div class="mt-6 flex items-center justify-center space-x-4">
        <div class="flex-1 border-t border-slate-700"></div>
        <span class="text-slate-500 text-sm">Hoặc</span>
        <div class="flex-1 border-t border-slate-700"></div>
    </div>

    <!-- Social Login -->
    <div class="mt-6 grid grid-cols-2 gap-4">
        <button class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors text-white">
            <i class="fab fa-google text-red-500"></i> Google
        </button>
        <button class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors text-white">
            <i class="fab fa-facebook text-blue-500"></i> Facebook
        </button>
    </div>

    <!-- Footer -->
    <p class="mt-8 text-center text-sm text-slate-400">
        Chưa có tài khoản? 
        <a href="{{ route('register') }}" class="text-primary hover:text-red-400 font-medium transition-colors">Đăng ký ngay</a>
    </p>
</x-guest-layout>
