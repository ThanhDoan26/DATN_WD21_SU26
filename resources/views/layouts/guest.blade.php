<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="CineTicket - Nền tảng đặt vé xem phim trực tuyến hàng đầu">
        <meta name="theme-color" content="#9333ea">

        <title>{{ config('app.name', 'CineTicket') }} - Đặt vé xem phim</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @supports (backdrop-filter: blur(1px)) {
                .backdrop-blur-md {
                    backdrop-filter: blur(12px);
                }
            }

            /* Animated background gradients */
            .bg-animated {
                background: linear-gradient(-45deg, #9333ea, #7e22ce, #6b21a8, #581c87);
                background-size: 400% 400%;
                animation: gradient 15s ease infinite;
            }

            @keyframes gradient {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            /* Glow effect */
            .glow-element {
                position: absolute;
                border-radius: 50%;
                filter: blur(40px);
                opacity: 0.15;
                animation: float 6s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px) translateX(0px); }
                25% { transform: translateY(-20px) translateX(10px); }
                50% { transform: translateY(-40px) translateX(-10px); }
                75% { transform: translateY(-20px) translateX(10px); }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Background -->
        <div class="fixed inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950 -z-20"></div>
        
        <!-- Animated gradient orbs -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="glow-element w-96 h-96 bg-primary-600 top-1/4 -left-48" style="animation-delay: 0s;"></div>
            <div class="glow-element w-80 h-80 bg-pink-600 bottom-1/4 -right-40" style="animation-delay: 2s;"></div>
            <div class="glow-element w-72 h-72 bg-primary-700 top-1/2 left-1/3" style="animation-delay: 4s;"></div>
        </div>

        <!-- Main content -->
        <div class="auth-container">
            <div class="w-full max-w-md">
                <!-- Logo Section -->
                <div class="text-center mb-12 animate-fade-in">
                    <a href="/" class="inline-flex items-center justify-center mb-6 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative w-16 h-16 bg-gradient-to-br from-primary-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-2xl">
                                <span class="text-3xl">🎬</span>
                            </div>
                        </div>
                    </a>
                    <h1 class="text-4xl font-display font-bold text-white mb-3 tracking-tight">
                        Cine<span class="text-gradient">Ticket</span>
                    </h1>
                    <p class="text-gray-400 text-base font-light">Trải nghiệm điện ảnh đỉnh cao</p>
                </div>

                <!-- Auth Card -->
                <div class="auth-card animate-slide-up">
                    <div class="auth-card-content">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="text-center mt-8 space-y-2 text-sm text-gray-500 font-light">
                    <p>© {{ date('Y') }} CineTicket. Bảo vệ quyền riêng tư của bạn.</p>
                    <div class="flex items-center justify-center gap-4">
                        <a href="#" class="hover:text-gray-400 transition-colors">Chính sách</a>
                        <span>•</span>
                        <a href="#" class="hover:text-gray-400 transition-colors">Điều khoản</a>
                        <span>•</span>
                        <a href="#" class="hover:text-gray-400 transition-colors">Hỗ trợ</a>
                    </div>
                </div>

                <!-- Security badge -->
                <div class="mt-6 flex items-center justify-center gap-2 text-xs text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Kết nối an toàn với SSL</span>
                </div>
            </div>
        </div>
    </body>
</html>
