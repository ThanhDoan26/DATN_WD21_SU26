@extends('layouts.frontend')

@section('content')

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-calendar-alt text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Phim Sắp Chiếu</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Những phim được mong đợi sắp ra mắt tại movieGo
            </p>
        </div>
    </div>

    <!-- Movies Grid -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            @if($movies->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    @foreach($movies as $movie)
                        <div class="bg-slate-800 rounded-xl overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                            <!-- Poster Section -->
                            <div class="relative h-72 overflow-hidden">
                                @if($movie->poster_url)
                                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                        <i class="fas fa-film text-slate-500 text-5xl"></i>
                                    </div>
                                @endif

                                <!-- Age Rating Badge -->
                                @if($movie->age_rating)
                                    <div class="absolute top-3 right-3 bg-primary text-white px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                                        {{ $movie->age_rating }}
                                    </div>
                                @endif

                                <!-- Status Badge -->
                                <div class="absolute top-3 left-3 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    Sắp Chiếu
                                </div>
                            </div>

                            <!-- Movie Info -->
                            <div class="p-5">
                                <h3 class="font-bold text-lg line-clamp-2 text-white mb-2 group-hover:text-primary transition-colors">
                                    {{ $movie->title }}
                                </h3>

                                <!-- Director & Duration -->
                                <div class="space-y-2 mb-4 text-sm text-slate-300">
                                    @if($movie->director)
                                        <p class="flex items-center gap-2">
                                            <i class="fas fa-user text-primary w-4"></i>
                                            {{ $movie->director }}
                                        </p>
                                    @endif

                                    @if($movie->duration)
                                        <p class="flex items-center gap-2">
                                            <i class="fas fa-clock text-primary w-4"></i>
                                            {{ $movie->getDurationFormatted() }}
                                        </p>
                                    @endif

                                    @if($movie->language)
                                        <p class="flex items-center gap-2">
                                            <i class="fas fa-globe text-primary w-4"></i>
                                            {{ $movie->language }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Description -->
                                @if($movie->description)
                                    <p class="text-sm text-slate-400 line-clamp-3 mb-4 leading-relaxed">
                                        {{ $movie->description }}
                                    </p>
                                @endif

                                <!-- First Showtime -->
                                @if($movie->showtimes && $movie->showtimes->count() > 0)
                                    <div class="bg-slate-700/50 rounded-lg p-3 mb-4">
                                        <p class="text-xs font-semibold text-slate-300 mb-2">
                                            <i class="fas fa-calendar-check text-primary mr-1"></i>
                                            Suất chiếu đầu tiên
                                        </p>
                                        <p class="text-sm text-slate-200">
                                            {{ $movie->showtimes->first()->start_time->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Book Button -->
                                <button onclick="alert('Tính năng đặt vé cho phim sắp chiếu sẽ được kích hoạt khi phim có lịch chiếu')" class="w-full bg-primary hover:bg-red-700 text-white py-3 rounded-lg font-semibold transition-all transform hover:scale-105 flex items-center justify-center gap-2 shadow-lg shadow-red-500/30">
                                    <i class="fas fa-bell"></i> Thông Báo
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-12">
                    {{ $movies->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-20">
                    <i class="fas fa-inbox text-slate-500 text-6xl mb-4"></i>
                    <p class="text-slate-400 text-xl">Không có phim sắp chiếu</p>
                </div>
            @endif
        </div>
    </section>

@endsection
