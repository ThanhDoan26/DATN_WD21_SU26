@extends('admin.layouts.app')

@section('title', 'Showtimes - Admin')
@section('page_title', 'Showtimes Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Showtimes</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-calendar-alt"></i> Danh sách Lịch Chiếu</h2>
        <p class="text-muted" style="margin-top: 5px;">Quản lý lịch chiếu phim</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Lịch Chiếu
        </a>
        <a href="{{ route('admin.showtimes.trashed') }}" class="btn btn-secondary">
            <i class="fas fa-trash-alt"></i> Thùng rác
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
        <form method="GET" action="{{ route('admin.showtimes.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Phim</label>
                    <select name="movie_id" class="form-select">
                        <option value="">Tất cả phim</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phòng</label>
                    <select name="room_id" class="form-select">
                        <option value="">Tất cả phòng</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->cinema->name }} / {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng Thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(\App\Models\Showtime::STATUSES as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst(strtolower($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
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
                    <th>Phim</th>
                    <th>Rạp / Phòng</th>
                    <th>Ngày Giờ</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($showtimes as $showtime)
                    <tr>
                        <td>{{ $showtime->id }}</td>
                        <td>{{ $showtime->movie->title }}</td>
                        <td>{{ $showtime->room->cinema->name }} / {{ $showtime->room->name }}</td>
                        <td>{{ $showtime->start_time->format('d/m/Y H:i') }} - {{ $showtime->end_time->format('H:i') }}</td>
                        <td>
                            @if($showtime->status === \App\Models\Showtime::STATUS_SCHEDULED)
                                <span class="badge bg-info">SCHEDULED</span>
                            @elseif($showtime->status === \App\Models\Showtime::STATUS_ONGOING)
                                <span class="badge bg-success">ONGOING</span>
                            @elseif($showtime->status === \App\Models\Showtime::STATUS_COMPLETED)
                                <span class="badge bg-secondary">COMPLETED</span>
                            @else
                                <span class="badge bg-danger">CANCELLED</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.showtimes.show', $showtime->id) }}" class="btn btn-sm btn-secondary" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.showtimes.edit', $showtime->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.showtimes.destroy', $showtime->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa mềm suất chiếu này?');">
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
                        <td colspan="6" class="text-center py-4">Chưa có suất chiếu nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $showtimes->links() }}
</div>
@endsection
