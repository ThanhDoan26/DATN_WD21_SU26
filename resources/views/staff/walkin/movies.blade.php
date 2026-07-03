@extends('layouts.staff')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-ticket-alt text-blue-500 mr-2"></i>Chọn Phim Đặt Tại Quầy</h1>
    </div>

    @if($movies->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($movies as $movie)
                <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">
                    <div class="h-64 overflow-hidden relative">
                        @if($movie->poster_url)
                            <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-film text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                        @if($movie->age_rating)
                            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                {{ $movie->age_rating }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-bold text-lg line-clamp-2 mb-2 text-gray-800">{{ $movie->title }}</h3>
                        <p class="text-sm text-gray-600 mb-4 flex-1">
                            <i class="fas fa-clock text-gray-400 mr-1"></i> {{ $movie->duration }} phút
                        </p>
                        
                        <a href="{{ route('staff.walkin.cinema', $movie->id) }}" class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            Chọn Phim Này
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $movies->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg">Không có phim nào đang chiếu.</p>
        </div>
    @endif
</div>
@endsection
