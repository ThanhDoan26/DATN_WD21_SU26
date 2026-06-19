<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phim Đang Chiếu - movieGo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
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
    </style>
</head>
<body class="bg-slate-900 text-white antialiased selection:bg-primary selection:text-white">

    <!-- Navigation Bar -->
    @include('layouts.guest-navigation')

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-film text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Phim Đang Chiếu</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Những bom tấn đang được chiếu tại các rạp movieGo
            </p>
        </div>
    </div>

    <!-- Movies Grid -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            @if($movies->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    @foreach($movies as $movie)
                        <x-movie-list-card :movie="$movie" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-12">
                    {{ $movies->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-20">
                    <i class="fas fa-inbox text-slate-500 text-6xl mb-4"></i>
                    <p class="text-slate-400 text-xl">Không có phim đang chiếu</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-800 border-t border-slate-700 py-12 px-4 mt-16">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span class="font-bold text-xl">movie<span class="text-primary">Go</span></span>
                </div>
                <p class="text-slate-400 text-sm">Nền tảng đặt vé xem phim trực tuyến hàng đầu</p>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Về movieGo</h4>
                <ul class="space-y-2 text-slate-400 text-sm">
                    <li><a href="#" class="hover:text-white transition">Trang chủ</a></li>
                    <li><a href="#" class="hover:text-white transition">Giới thiệu</a></li>
                    <li><a href="#" class="hover:text-white transition">Liên hệ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Phim</h4>
                <ul class="space-y-2 text-slate-400 text-sm">
                    <li><a href="{{ route('movies.current') }}" class="hover:text-white transition">Phim đang chiếu</a></li>
                    <li><a href="{{ route('movies.upcoming') }}" class="hover:text-white transition">Phim sắp chiếu</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Liên kết</h4>
                <ul class="space-y-2 text-slate-400 text-sm">
                    <li><a href="#" class="hover:text-white transition">Điều khoản</a></li>
                    <li><a href="#" class="hover:text-white transition">Chính sách riêng tư</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-700 mt-8 pt-8 text-center text-slate-400 text-sm">
            <p>&copy; 2026 movieGo. Bảo lưu mọi quyền.</p>
        </div>
    </footer>

</body>
</html>
