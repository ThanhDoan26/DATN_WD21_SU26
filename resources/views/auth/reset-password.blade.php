<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Đặt Lại Mật Khẩu</h2>
        <p class="text-slate-400">Tạo mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Hidden Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') ?? $request->token }}">

        <!-- Email -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Địa chỉ Email <span class="text-primary">*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
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

        <!-- New Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật Khẩu Mới <span class="text-primary">*</span></label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                placeholder="Nhập mật khẩu mới"
            />
            <p class="mt-2 text-xs text-slate-400">Tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường và số</p>
            @error('password')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Xác Nhận Mật Khẩu <span class="text-primary">*</span></label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                placeholder="Nhập lại mật khẩu"
            />
            @error('password_confirmation')
                <p class="mt-2 text-sm text-primary">{{ $message }}</p>
            @enderror
            
            <div class="flex items-center justify-between mt-3">
                <!-- Show Password Checkbox -->
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary focus:ring-offset-slate-900">
                    <span class="ml-2 text-xs text-slate-400 hover:text-slate-300 transition-colors">Hiển thị mật khẩu</span>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-primary hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2">
            <span>Cập Nhật Mật Khẩu</span>
            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
