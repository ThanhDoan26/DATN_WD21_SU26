@extends('layouts.frontend')

@section('content')

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

@endsection
