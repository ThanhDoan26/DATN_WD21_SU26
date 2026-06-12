@extends('admin.layouts.app')

@section('title', 'Chi tiết Phim')
@section('page_title', 'Chi tiết Phim: ' . $movie->title)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Poster</h5>
            </div>
            <div class="card-body text-center">
                @if($movie->poster_url)
                    <img src="{{ Str::startsWith($movie->poster_url, ['http://', 'https://']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="img-fluid rounded shadow" style="max-height: 400px; object-fit: cover;">
                @else
                    <div class="bg-light p-5 text-muted rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                        <span>Chưa có ảnh Poster</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Hành động</h5>
            </div>
            <div class="card-body text-center">
                <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit"></i> Chỉnh sửa phim
                </a>
                <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ phim này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Xóa phim
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thông tin chi tiết</h5>
                @if($movie->status == 'COMING_SOON')
                    <span class="badge bg-warning text-dark fs-6">Sắp chiếu</span>
                @elseif($movie->status == 'NOW_SHOWING')
                    <span class="badge bg-success fs-6">Đang chiếu</span>
                @else
                    <span class="badge bg-danger fs-6">Ngưng chiếu</span>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="200" class="bg-light">Tên phim</th>
                            <td><strong class="fs-5">{{ $movie->title }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Danh mục</th>
                            <td>
                                @forelse($movie->categories as $category)
                                    <span class="badge bg-secondary">{{ $category->name }}</span>
                                @empty
                                    <span class="text-muted">Chưa cập nhật</span>
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Mô tả</th>
                            <td>{{ $movie->description ?: 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Thời lượng</th>
                            <td>{{ $movie->duration }} phút ({{ $movie->getDurationFormatted() }})</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Độ tuổi</th>
                            <td><span class="badge bg-info text-dark">{{ $movie->age_rating ?: 'Chưa cập nhật' }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Đạo diễn</th>
                            <td>{{ $movie->director ?: 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Diễn viên</th>
                            <td>{{ $movie->cast ?: 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Quốc gia</th>
                            <td>{{ $movie->country ?: 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Ngôn ngữ</th>
                            <td>{{ $movie->language ?: 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Trailer URL</th>
                            <td>
                                @if($movie->trailer_url)
                                    <a href="{{ $movie->trailer_url }}" target="_blank">{{ $movie->trailer_url }}</a>
                                @else
                                    Chưa cập nhật
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Chỉnh sửa</a>
    <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>
@endsection
