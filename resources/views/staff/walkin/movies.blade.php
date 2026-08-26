@extends('layouts.staff')

@section('page_title', 'Tạo Vé Tại Quầy - Chọn Phim')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Action Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4 bg-surface p-3 rounded-4 shadow-sm border border-light">
        <div>
            <h4 class="mb-0 fw-extrabold text-ink font-sora"><i class="fas fa-cash-register text-amber me-2"></i>Bán Vé Tại Quầy POS</h4>
            <small class="text-muted">Chọn phim khách hàng yêu cầu để bắt đầu giữ ghế và xuất vé</small>
        </div>
        <span class="badge bg-amber text-dark px-3 py-2 rounded-pill fw-bold">
            <i class="fas fa-building me-1"></i> {{ Auth::user()->cinema->name ?? 'Quầy Bán Vé' }}
        </span>
    </div>

    @if($movies->count() > 0)
        <div class="row g-4">
            @foreach($movies as $movie)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative transition-all hover-translate-y" style="background: var(--bg-surface);">
                        <div class="position-relative" style="height: 280px;">
                            @if($movie->poster_url)
                                <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" 
                                     alt="{{ $movie->title }}" 
                                     class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-amber">
                                    <i class="fas fa-film fa-4x opacity-50"></i>
                                </div>
                            @endif

                            @if($movie->age_rating)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6 shadow">
                                    {{ $movie->age_rating }}
                                </span>
                            @endif

                            <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.85) 100%);">
                                <span class="badge bg-amber text-dark font-sora fw-bold"><i class="fas fa-clock me-1"></i>{{ $movie->duration }} phút</span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-extrabold font-sora text-ink text-truncate mb-2" title="{{ $movie->title }}">{{ $movie->title }}</h5>
                            <p class="card-text text-muted small mb-4 flex-grow-1">
                                <i class="fas fa-tags text-amber me-1"></i> {{ $movie->genre ?? 'Chiếu rạp' }}
                            </p>
                            
                            <a href="{{ route('staff.walkin.dates', $movie->id) }}" class="btn btn-amber w-100 font-sora fw-bold py-2 rounded-3 shadow-sm">
                                <i class="fas fa-ticket-alt me-2"></i>Chọn Phim Này
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            {{ $movies->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body">
                <i class="fas fa-film text-muted opacity-50 mb-3" style="font-size: 4rem;"></i>
                <h4 class="text-muted font-sora fw-bold">Hiện tại không có phim nào đang chiếu.</h4>
            </div>
        </div>
    @endif
</div>
@endsection

@section('extra_css')
<style>
    .btn-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: none;
    }
    .btn-amber:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
    }
    .hover-translate-y:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
