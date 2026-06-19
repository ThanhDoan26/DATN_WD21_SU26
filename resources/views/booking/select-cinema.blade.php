<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chọn Cụm Rạp - movieGo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    @include('layouts.guest-navigation')

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-map-marker-alt text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Chọn Cụm Rạp</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Bước 1: Chọn rạp chiếu phim {{ $movie->title }}
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="py-16 px-4 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Movie Info Bar -->
            <div class="bg-slate-800 rounded-lg p-6 mb-8 flex items-center gap-4">
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-20 h-28 rounded-lg object-cover">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-2">{{ $movie->title }}</h2>
                    <p class="text-slate-300">{{ $movie->description }}</p>
                </div>
            </div>

            <!-- Cinemas Grid -->
            @if($cinemas->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cinemas as $cinema)
                        <div class="bg-slate-800 rounded-lg overflow-hidden hover:bg-slate-700 transition-all duration-300 cursor-pointer group"
                             onclick="selectCinema({{ $cinema->id }}, '{{ $cinema->name }}')">
                            <div class="p-6">
                                <!-- Cinema Header -->
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-building text-primary text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold group-hover:text-primary transition">{{ $cinema->name }}</h3>
                                        <p class="text-slate-400 text-sm">{{ $cinema->city }}</p>
                                    </div>
                                </div>

                                <!-- Cinema Details -->
                                <div class="space-y-2 mb-4 text-slate-300 text-sm">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-pin text-slate-500 w-4"></i>
                                        <span>{{ $cinema->address }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-phone text-slate-500 w-4"></i>
                                        <span>{{ $cinema->phone }}</span>
                                    </div>
                                </div>

                                <!-- Room Count -->
                                <div class="bg-slate-900 rounded-lg p-3 mb-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-300">{{ $cinema->rooms->count() }} phòng chiếu</span>
                                        <span class="text-primary font-bold">{{ $cinema->rooms->count() }}</span>
                                    </div>
                                </div>

                                <!-- Select Button -->
                                <button class="w-full bg-primary hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 group-hover:shadow-lg group-hover:shadow-primary/50">
                                    <i class="fas fa-arrow-right mr-2"></i>Chọn Rạp Này
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <i class="fas fa-inbox text-slate-500 text-6xl mb-4"></i>
                    <p class="text-slate-400 text-xl mb-6">Không có cụm rạp nào có suất chiếu phim này</p>
                    <a href="{{ route('movies.current') }}" class="inline-block bg-primary hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách phim
                    </a>
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
                    <li><a href="/" class="hover:text-white transition">Trang chủ</a></li>
                    <li><a href="{{ route('movies.current') }}" class="hover:text-white transition">Phim Đang Chiếu</a></li>
                    <li><a href="{{ route('movies.upcoming') }}" class="hover:text-white transition">Phim Sắp Chiếu</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        function selectCinema(cinemaId, cinemaName) {
            const movieId = {{ $movie->id }};
            // Chuyển đến bước chọn ngày và suất chiếu
            // URL: /booking/movie/{movie}/cinema/{cinema}/dates
            window.location.href = `/booking/movie/${movieId}/cinema/${cinemaId}/dates`;
        }
    </script>
</body>
</html>
