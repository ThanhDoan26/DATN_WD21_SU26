@extends('admin.layouts.app')

@section('title', $movie->title . ' - Admin')
@section('page_title', 'Movie Details')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.movies.index') }}">Movies</a></li>
            <li class="breadcrumb-item active">{{ $movie->title }}</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-video"></i> {{ $movie->title }}</h2>
        <p class="text-muted">Chi tiết phim</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord('{{ route('admin.movies.destroy', $movie->id) }}')">
            <i class="fas fa-trash"></i> Xóa
        </button>
    </div>
</div>

<div class="row">
    <!-- Left Column - Poster -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($movie->poster_url)
                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="img-fluid rounded" style="max-height: 400px;">
                @else
                    <div style="height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 0.25rem;">
                        <i class="fas fa-image" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Card -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Thông Tin Trạng Thái
            </div>
            <div class="card-body">
                @if($movie->status === 'ACTIVE')
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> <strong>Đang Chiếu</strong>
                    </div>
                @elseif($movie->status === 'COMING_SOON')
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-clock"></i> <strong>Sắp Chiếu</strong>
                    </div>
                @else
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-times-circle"></i> <strong>Ngừng Chiếu</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Details -->
    <div class="col-md-8">
        <!-- General Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-clapperboard"></i> Thông Tin Cơ Bản
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Thời Lượng</h6>
                        <p><strong>{{ $movie->getDurationFormatted() }}</strong> ({{ $movie->duration }} phút)</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Độ Tuổi Phù Hợp</h6>
                        <p><strong>{{ $movie->age_rating ?? 'N/A' }}</strong></p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Ngôn Ngữ</h6>
                        <p><strong>{{ $movie->language ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Nước Sản Xuất</h6>
                        <p><strong>{{ $movie->country ?? 'N/A' }}</strong></p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Đạo Diễn</h6>
                        <p><strong>{{ $movie->director ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Diễn Viên</h6>
                        <p><strong>{{ $movie->cast ?? 'N/A' }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($movie->description)
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-align-left"></i> Mô Tả Phim
            </div>
            <div class="card-body">
                <p>{{ $movie->description }}</p>
            </div>
        </div>
        @endif

        <!-- URLs -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-link"></i> Liên Kết
            </div>
            <div class="card-body">
                @if($movie->poster_url)
                <div class="mb-3">
                    <h6 class="text-muted mb-1">URL Poster</h6>
                    <a href="{{ $movie->poster_url }}" target="_blank" class="text-break">
                        {{ $movie->poster_url }}
                    </a>
                </div>
                @endif

                @if($movie->trailer_url)
                <div class="mb-0">
                    <h6 class="text-muted mb-1">URL Trailer</h6>
                    <a href="{{ $movie->trailer_url }}" target="_blank" class="text-break">
                        {{ $movie->trailer_url }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Metadata -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Thông Tin Khác
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Tạo Lúc</h6>
                        <p><strong>{{ $movie->created_at->format('d/m/Y H:i:s') }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Cập Nhật Lúc</h6>
                        <p><strong>{{ $movie->updated_at->format('d/m/Y H:i:s') }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Showtimes Section -->
@if($movie->showtimes && $movie->showtimes->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-clock"></i> Lịch Chiếu ({{ $movie->showtimes->count() }})
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Rạp</th>
                    <th>Phòng</th>
                    <th>Ngày Chiếu</th>
                    <th>Giờ Chiếu</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movie->showtimes as $showtime)
                <tr>
                    <td>
                        <a href="{{ route('admin.cinemas.show', $showtime->room->cinema_id) }}">
                            {{ $showtime->room->cinema->name }}
                        </a>
                    </td>
                    <td>{{ $showtime->room->name }}</td>
                    <td>{{ $showtime->show_date->format('d/m/Y') }}</td>
                    <td>{{ $showtime->show_time->format('H:i') }}</td>
                    <td>
                        @if($showtime->status === 'ACTIVE')
                            <span class="badge bg-success">Hoạt Động</span>
                        @else
                            <span class="badge bg-danger">Ngừng</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<script>
function deleteRecord(deleteUrl) {
    if (confirm('Bạn có chắc chắn muốn xóa phim này?')) {
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        }).then(response => {
            if (response.ok) {
                window.location = '{{ route('admin.movies.index') }}';
            } else {
                alert('Lỗi xóa phim!');
            }
        }).catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
