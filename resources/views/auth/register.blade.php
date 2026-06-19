<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - movieGo</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] }, colors: { primary: '#e50914' } } } }
        </script>
    @endif
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass-card {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-slate-900 text-white antialiased selection:bg-[#e50914] selection:text-white min-h-screen flex items-center justify-center relative py-10">
    
    <!-- Background Image -->
    <div class="absolute inset-0 z-0 fixed">
        <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Cinema Background" class="w-full h-full object-cover opacity-30" />
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent"></div>
    </div>

    <div class="relative z-10 w-full max-w-md px-6">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 group">
                <div class="w-12 h-12 rounded-xl bg-[#e50914] flex items-center justify-center text-white font-bold text-2xl group-hover:scale-105 transition-transform shadow-lg shadow-red-500/30">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span class="font-bold text-3xl tracking-tight">movie<span class="text-[#e50914]">Go</span></span>
            </a>
            <p class="text-slate-400 mt-2">Tạo tài khoản để đặt vé và nhận ưu đãi</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Họ và Tên <span class="text-[#e50914]">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="Nguyễn Văn A">
                    @error('name')
                        <p class="mt-2 text-sm text-[#e50914]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-[#e50914]">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="you@example.com">
                    @error('email')
                        <p class="mt-2 text-sm text-[#e50914]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu <span class="text-[#e50914]">*</span></label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="Tối thiểu 8 ký tự">
                    @error('password')
                        <p class="mt-2 text-sm text-[#e50914]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Xác nhận mật khẩu <span class="text-[#e50914]">*</span></label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="Nhập lại mật khẩu">
                </div>

                <!-- Terms -->
                <div class="flex items-start mt-4">
                    <div class="flex items-center h-5">
                        <input id="agree_terms" name="agree_terms" type="checkbox" required class="w-4 h-4 rounded border-slate-700 bg-slate-800/50 text-[#e50914] focus:ring-[#e50914]">
                    </div>
                    <label for="agree_terms" class="ml-2 text-sm text-slate-300">
                        Tôi đồng ý với <a href="#" class="text-[#e50914] hover:underline">Điều khoản dịch vụ</a> và <a href="#" class="text-[#e50914] hover:underline">Chính sách bảo mật</a>.
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#e50914] hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2 mt-4">
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
                <button class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <i class="fab fa-google text-red-500"></i> Google
                </button>
                <button class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <i class="fab fa-facebook text-blue-500"></i> Facebook
                </button>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-sm text-slate-400">
                Đã có tài khoản? 
                <a href="{{ route('login') }}" class="text-[#e50914] hover:text-red-400 font-medium transition-colors">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
</body>
</html>
