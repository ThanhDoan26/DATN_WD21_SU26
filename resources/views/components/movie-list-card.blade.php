@props(['movie'])

<div class="bg-slate-800 rounded-xl overflow-hidden hover:shadow-2xl transition-all duration-300 group">
    <!-- Poster Section -->
    <a href="{{ route('movies.show', $movie->id) }}" class="relative h-72 overflow-hidden block">
        @if($movie->poster_url)
            <img src="{{ asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
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
        <div class="absolute top-3 left-3 bg-green-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
            Đang Chiếu
        </div>
    </a>

    <!-- Movie Info -->
    <div class="p-5">
        <a href="{{ route('movies.show', $movie->id) }}">
            <h3 class="font-bold text-lg line-clamp-2 text-white mb-2 group-hover:text-primary transition-colors">
                {{ $movie->title }}
            </h3>
        </a>

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
                    {{ $movie->getDurationFormatted() }} ({{ $movie->duration }} phút)
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

        <!-- Showtimes Preview -->
        @if($movie->showtimes && $movie->showtimes->count() > 0)
            <div class="bg-slate-700/50 rounded-lg p-3 mb-4">
                <p class="text-xs font-semibold text-slate-300 mb-2">
                    <i class="fas fa-calendar-check text-primary mr-1"></i>
                    Suất chiếu ({{ $movie->showtimes->count() }} suất)
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($movie->showtimes->take(3) as $showtime)
                        <span class="text-xs bg-slate-600 px-2 py-1 rounded text-slate-200">
                            {{ $showtime->start_time->format('H:i') }}
                        </span>
                    @endforeach
                    @if($movie->showtimes->count() > 3)
                        <span class="text-xs bg-slate-600 px-2 py-1 rounded text-slate-200">
                            +{{ $movie->showtimes->count() - 3 }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <a href="{{ route('booking.select-cinema', $movie) }}" class="w-full bg-primary hover:bg-red-700 text-white py-3 rounded-lg font-semibold transition-all transform hover:scale-105 flex items-center justify-center gap-2 shadow-lg shadow-red-500/30">
            <i class="fas fa-ticket-alt"></i> Đặt Vé
        </a>
    </div>
</div>
