<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'movieGo') }} - Đăng nhập</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback if Vite is not running, using CDN for preview -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Outfit', 'sans-serif'],
                        },
                        colors: {
                            primary: '#e50914',
                        }
                    }
                }
            }
        </script>
    @endif
    
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .hero-gradient {
            background: linear-gradient(to top, #0f172a 0%, rgba(15, 23, 42, 0.4) 100%);
        }
        .auth-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-slate-900 text-white antialiased selection:bg-primary selection:text-white min-h-screen flex flex-col">

    <!-- Include Navigation -->
    @include('layouts.guest-navigation')

    <!-- Background Image with Overlay -->
    <div class="fixed inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Cinema Background" class="w-full h-full object-cover opacity-30" />
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 flex-grow flex items-center justify-center pt-24 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            
            <!-- Logo Section (Optional inside auth card context, since it's on navbar we can just show a welcome text) -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/20 text-primary mb-4 shadow-[0_0_15px_rgba(229,9,20,0.5)]">
                    <i class="fas fa-user-circle text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-white">
                    Chào mừng trở lại
                </h2>
                <p class="mt-2 text-sm text-slate-400">
                    Đăng nhập để đặt vé và nhận nhiều ưu đãi
                </p>
            </div>

            <!-- Auth Card -->
            <div class="auth-card rounded-2xl p-8">
                {{ $slot }}
            </div>
            
            <!-- Footer Info -->
            <div class="text-center mt-8 text-sm text-slate-500">
                <p>© {{ date('Y') }} movieGo. Bảo vệ quyền riêng tư của bạn.</p>
                <div class="mt-2 flex justify-center gap-4">
                    <a href="#" class="hover:text-white transition-colors">Hỗ trợ</a>
                    <span>&bull;</span>
                    <a href="#" class="hover:text-white transition-colors">Điều khoản</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
