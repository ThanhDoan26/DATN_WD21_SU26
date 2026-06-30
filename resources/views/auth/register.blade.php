<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-white">Tạo tài khoản mới</h2>
        <p class="mt-2 text-sm text-slate-400">Tạo tài khoản để đặt vé và nhận ưu đãi</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Họ và Tên <span class="text-primary">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Nguyễn Văn A">
            @error('name')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-primary">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="you@example.com">
            @error('email')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu <span class="text-primary">*</span></label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Tối thiểu 8 ký tự">
            @error('password')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Xác nhận mật khẩu <span class="text-primary">*</span></label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Nhập lại mật khẩu">
            
            <div class="flex items-center mt-3">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900">
                    <span class="ml-2 text-xs text-slate-400 hover:text-slate-300 transition-colors">Hiển thị mật khẩu</span>
                </label>
            </div>
        </div>

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

        <!-- Terms -->
        <div class="flex items-start mt-4">
            <div class="flex items-center h-5">
                <input id="agree_terms" name="agree_terms" type="checkbox" required class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900">
            </div>
            <label for="agree_terms" class="ml-2 text-sm text-slate-300">
                Tôi đồng ý với <a href="#" class="text-primary hover:underline">Điều khoản dịch vụ</a> và <a href="#" class="text-primary hover:underline">Chính sách bảo mật</a>.
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-primary hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2 mt-4">
            <i class="fas fa-user-plus"></i> Tạo Tài Khoản
        </button>
    </form>

    <!-- Divider -->
    <div class="mt-6 flex items-center justify-center space-x-4">
        <div class="flex-1 border-t border-slate-700"></div>
        <span class="text-slate-500 text-sm">Hoặc đăng ký bằng</span>
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
        Đã có tài khoản? 
        <a href="{{ route('login') }}" class="text-primary hover:text-red-400 font-medium transition-colors">Đăng nhập ngay</a>
    </p>
</x-guest-layout>
