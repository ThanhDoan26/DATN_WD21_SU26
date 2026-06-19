<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>movieGo - Đỉnh Cao Điện Ảnh</title>

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
        .glass-nav {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .hero-gradient {
            background: linear-gradient(to top, #0f172a 0%, rgba(15, 23, 42, 0.4) 100%);
        }
    </style>
</head>
<body class="bg-slate-900 text-white antialiased selection:bg-primary selection:text-white">

    <!-- Navigation Bar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-xl group-hover:scale-105 transition-transform">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <span class="font-bold text-2xl tracking-tight">movie<span class="text-primary">Go</span></span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-white hover:text-primary transition-colors font-medium">Trang chủ</a>
                    <a href="#" class="text-slate-300 hover:text-white transition-colors font-medium">Lịch chiếu</a>
                    <a href="#" class="text-slate-300 hover:text-white transition-colors font-medium">Cụm rạp</a>
                    <a href="#" class="text-slate-300 hover:text-white transition-colors font-medium">Khuyến mãi</a>
                </div>

                <!-- Auth / User Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-white hover:text-primary transition-colors font-medium">Bảng điều khiển</a>
                        @else
                            <a href="{{ route('login') }}" class="text-slate-300 hover:text-white transition-colors font-medium">Đăng nhập</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-primary hover:bg-red-700 text-white px-5 py-2.5 rounded-full font-medium transition-all transform hover:scale-105 shadow-lg shadow-red-500/30">
                                    Đăng ký
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="text-slate-300 hover:text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen flex items-center justify-center pt-20">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1925&q=80" alt="Cinema Background" class="w-full h-full object-cover" />
            <div class="absolute inset-0 hero-gradient"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto mt-10">
            <span class="inline-block py-1 px-4 rounded-full bg-primary/20 text-red-400 border border-primary/30 font-semibold text-sm mb-6 animate-pulse">
                Trải nghiệm điện ảnh đỉnh cao
            </span>
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 drop-shadow-lg leading-tight">
                Bom Tấn Mới Nhất <br/> Đang Chờ Bạn
            </h1>
            <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl mx-auto drop-shadow-md">
                Đặt vé xem phim trực tuyến nhanh chóng, nhận ưu đãi hấp dẫn và tận hưởng những phút giây thư giãn tuyệt vời tại cụm rạp movieGo.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#" class="bg-primary hover:bg-red-700 text-white px-8 py-4 rounded-full font-semibold text-lg transition-all transform hover:scale-105 shadow-xl shadow-red-500/40 flex items-center justify-center gap-2">
                    <i class="fas fa-ticket-alt"></i> Mua Vé Ngay
                </a>
                <a href="#" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/20 px-8 py-4 rounded-full font-semibold text-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-play-circle"></i> Xem Trailer
                </a>
            </div>
        </div>
    </div>

</body>
</html>
