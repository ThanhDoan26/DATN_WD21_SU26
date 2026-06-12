<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - movieGo</title>
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
<body class="bg-slate-900 text-white antialiased selection:bg-[#e50914] selection:text-white min-h-screen flex flex-col relative">
    
    <!-- Navigation Bar -->
    @include('layouts.guest-navigation')
    
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Cinema Background" class="w-full h-full object-cover opacity-30" />
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent"></div>
    </div>

    <div class="relative z-10 w-full max-w-sm px-6 mx-auto flex-grow flex flex-col justify-center pt-24 pb-12">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 group">
                <div class="w-12 h-12 rounded-xl bg-[#e50914] flex items-center justify-center text-white font-bold text-2xl group-hover:scale-105 transition-transform shadow-lg shadow-red-500/30">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span class="font-bold text-3xl tracking-tight">movie<span class="text-[#e50914]">Go</span></span>
            </a>
            <p class="text-slate-400 mt-2">Đăng nhập để trải nghiệm điện ảnh đỉnh cao</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <!-- Status Message -->
            @if ($status = session('status'))
                <div class="mb-4 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
                    {{ $status }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-[#e50914]">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="you@example.com">
                    @error('email')
                        <p class="mt-2 text-sm text-[#e50914]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu <span class="text-[#e50914]">*</span></label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition-colors" placeholder="Nhập mật khẩu">
                    @error('password')
                        <p class="mt-2 text-sm text-[#e50914]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-800/50 text-[#e50914] focus:ring-[#e50914]" {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-slate-300 hover:text-white transition-colors">Ghi nhớ tôi</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-[#e50914] hover:text-red-400 transition-colors">Quên mật khẩu?</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#e50914] hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-red-500/30 flex justify-center items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-6 flex items-center justify-center space-x-4">
                <div class="flex-1 border-t border-slate-700"></div>
                <span class="text-slate-500 text-sm">Hoặc</span>
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
                Chưa có tài khoản? 
                <a href="{{ route('register') }}" class="text-[#e50914] hover:text-red-400 font-medium transition-colors">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</body>
</html>
