@props(['movie'])

<a href="{{ route('movies.show', $movie->id) }}" class="group block overflow-hidden rounded-xl bg-slate-800 hover:bg-slate-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
    <!-- Poster Image -->
    <div class="relative overflow-hidden h-56 sm:h-64">
        @if($movie->poster_url)
            <img src="{{ asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
        @else
            <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                <i class="fas fa-image text-slate-500 text-4xl"></i>
            </div>
        @endif

        <!-- Age Rating Badge -->
        @if($movie->age_rating)
            <div class="absolute top-2 right-2 bg-primary text-white px-3 py-1 rounded-full text-xs font-bold">
                {{ $movie->age_rating }}
            </div>
        @endif

        <!-- Play Button Overlay -->
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform">
                    <i class="fas fa-play text-white text-xl ml-1"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Movie Info -->
    <div class="p-4">
        <h3 class="font-bold text-base line-clamp-2 text-white group-hover:text-primary transition-colors">
            {{ $movie->title }}
        </h3>

        <!-- Duration & Language -->
        <div class="flex items-center gap-2 mt-2 text-xs text-slate-400">
            @if($movie->duration)
                <span class="flex items-center gap-1">
                    <i class="fas fa-clock"></i>
                    {{ $movie->getDurationFormatted() }}
                </span>
            @endif
            @if($movie->language)
                <span class="flex items-center gap-1">
                    <i class="fas fa-globe"></i>
                    {{ $movie->language }}
                </span>
            @endif
        </div>

        <!-- Description -->
        @if($movie->description)
            <p class="text-xs text-slate-400 line-clamp-2 mt-2">
                {{ $movie->description }}
            </p>
        @endif

        <!-- Book Button -->
        <button class="w-full mt-4 bg-primary hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition-colors text-sm">
            Đặt vé
        </button>
    </div>
</a>
