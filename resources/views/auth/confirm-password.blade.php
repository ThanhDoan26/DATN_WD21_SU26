<x-guest-layout>
    <!-- Header -->
    <div class="auth-header mb-2">
        <h2 class="auth-title">Xác Nhận Mật Khẩu</h2>
        <p class="auth-subtitle">Vui lòng xác minh danh tính của bạn</p>
    </div>

    <!-- Security Information -->
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 space-y-2">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-400">Khu vực an toàn</p>
                <p class="text-xs text-amber-300/80 mt-0.5">Nhập mật khẩu để tiếp tục</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Mật Khẩu</label>
            <div class="form-input-wrapper">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="form-input"
                    placeholder="Nhập mật khẩu của bạn"
                />
            </div>
            @error('password')
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
            <span>Xác Nhận Mật Khẩu</span>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
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
</x-guest-layout>
