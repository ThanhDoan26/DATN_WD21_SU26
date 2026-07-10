@extends('admin.layouts.app')

@section('title', 'Trashed Showtimes - Admin')
@section('page_title', 'Thùng Rác Suất Chiếu')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.showtimes.index') }}">Showtimes</a></li>
            <li class="breadcrumb-item active">Thùng rác</li>
        </ol>
    </nav>
</div>


<div class="page-title mb-4">
    <div>
        <h2><i class="fas fa-trash-alt"></i> Suất Chiếu Đã Xóa</h2>
        <p class="text-muted" style="margin-top: 5px;">Danh sách suất chiếu đã bị xóa tạm thời</p>
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
                    <th>Đã xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($showtimes as $showtime)
                    <tr>
                        <td>{{ $showtime->id }}</td>
                        <td>{{ $showtime->movie->title }}</td>
                        <td>{{ $showtime->room?->cinema?->name ?? 'N/A' }} / {{ $showtime->room?->name ?? 'N/A' }}</td>
                        <td>{{ $showtime->start_time->format('d/m/Y H:i') }} - {{ $showtime->end_time->format('H:i') }}</td>
                        <td>{{ $showtime->deleted_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.showtimes.restore', $showtime->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" type="submit" title="Khôi phục">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.showtimes.forceDelete', $showtime->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn suất chiếu này?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit" title="Xóa vĩnh viễn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">Chưa có suất chiếu bị xóa tạm thời</td>
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
