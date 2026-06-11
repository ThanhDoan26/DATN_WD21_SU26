@extends('admin.layouts.app')

@section('title', 'Movies - Admin')
@section('page_title', 'Movies Management')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Movies</li>
        </ol>
    </nav>
</div>

<div class="page-title">
    <div>
        <h2><i class="fas fa-video"></i> Danh sách Phim</h2>
        <p class="text-muted" style="margin-top: 5px;">Quản lý danh sách phim hiển thị trong hệ thống</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Phim Mới
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.movies.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên phim...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(App\Models\Movie::STATUSES as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary w-100">Xóa lọc</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tiêu đề</th>
                    <th>Trạng thái</th>
                    <th>Thời lượng</th>
                    <th>Ngôn ngữ / Quốc gia</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movies as $movie)
                    <tr>
                        <td>{{ $movie->id }}</td>
                        <td>
                            <strong>{{ $movie->title }}</strong><br>
                            <small class="text-muted">{{ $movie->director ?: 'Chưa có đạo diễn' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">{{ ucfirst(strtolower(str_replace('_', ' ', $movie->status))) }}</span>
                        </td>
                        <td>{{ intdiv($movie->duration, 60) }}h {{ $movie->duration % 60 }}m</td>
                        <td>{{ $movie->language ?: 'N/A' }} / {{ $movie->country ?: 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.movies.show', $movie->id) }}" class="btn btn-sm btn-secondary" title="Xem">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa phim này?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">Chưa có phim nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $movies->links() }}
</div>
@endsection
