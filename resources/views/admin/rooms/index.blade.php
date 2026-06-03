@extends('admin.layouts.app')

@section('title', 'Rooms - Admin')
@section('page_title', 'Rooms Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Rooms</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-door-open"></i> Danh sách Phòng Chiếu</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Xem danh sách tất cả các phòng chiếu trong hệ thống</p>
    </div>
    <div>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Thêm Mới
        </a>
    </div>
</div>

<!-- Rooms Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Phòng Chiếu
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Phòng</th>
                    <th>Rạp</th>
                    <th>Format</th>
                    <th>Tổng Ghế</th>
                    <th>Trạng thái</th>
                    <th>Tạo lúc</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms ?? [] as $room)
                <tr>
                    <td><strong>#{{ $room->id }}</strong></td>
                    <td>
                        <strong>{{ $room->name }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $room->cinema->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $room->format }}</span>
                    </td>
                    <td>
                        <strong>{{ $room->total_seats ?? 0 }}</strong> ghế
                    </td>
                    <td>
                        @if($room->status === 'ACTIVE')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $room->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có phòng nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
