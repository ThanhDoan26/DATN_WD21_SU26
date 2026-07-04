@extends('layouts.staff')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <h2 class="fw-bold text-primary mb-0"><i class="fas fa-ticket-alt me-2"></i>Chọn Phim (Tạo vé tại quầy)</h2>
    </div>

    @if($movies->count() > 0)
        <div class="row g-4">
            @foreach($movies as $movie)
                <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="position-relative" style="height: 300px;">
                        @if($movie->poster_url)
                            <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" 
                                 alt="{{ $movie->title }}" 
                                 class="w-100 h-100 object-fit-cover" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 bg-secondary d-flex align-items-center justify-content-center text-white">
                                <i class="fas fa-film fa-4x text-light opacity-50"></i>
                            </div>
                        @endif
                        @if($movie->age_rating)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6">
                                {{ $movie->age_rating }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="card-body d-flex flex-column p-4">
                        <h5 class="card-title fw-bold text-dark text-truncate mb-2" title="{{ $movie->title }}">{{ $movie->title }}</h5>
                        <p class="card-text text-muted mb-4 flex-grow-1">
                            <i class="fas fa-clock me-1"></i> {{ $movie->duration }} phút
                        </p>
                        
                        <a href="{{ route('staff.walkin.dates', $movie->id) }}" class="btn btn-primary w-100 fw-bold py-2 rounded-3">
                            Chọn Phim Này
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            {{ $movies->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="fas fa-film text-muted mb-3" style="font-size: 5rem;"></i>
            <h4 class="text-muted">Không có phim nào đang chiếu.</h4>
        </div>
    @endif
</div>
@endsection
