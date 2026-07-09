@extends('layouts.frontend')

@section('content')

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
                <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="w-20 h-28 rounded-lg object-cover shadow-lg border border-slate-700">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-2">{{ $movie->title }}</h2>
                    <p class="text-slate-300">{{ $movie->description }}</p>
                </div>
            </div>

            <!-- Cinemas Grid -->
            @if($cinemas->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cinemas as $cinema)
                        <div class="bg-slate-800 rounded-xl overflow-hidden hover:bg-slate-700 transition-all duration-300 cursor-pointer group hover:-translate-y-2 hover:shadow-xl hover:shadow-primary/20 border border-slate-700 hover:border-primary"
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

@endsection

@push('scripts')
    <script>
        function selectCinema(cinemaId, cinemaName) {
            const movieId = {{ $movie->id }};
            // Chuyển đến bước chọn ngày và suất chiếu
            // URL: /booking/movie/{movie}/cinema/{cinema}/dates
            window.location.href = `/booking/movie/${movieId}/cinema/${cinemaId}/dates`;
        }
    </script>
@endpush
