@extends('admin.layouts.app')

@section('title', 'Room Details - Admin')
@section('page_title', 'Room Details')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-eye"></i> Chi tiết Phòng Chiếu: #{{ $room->id }}</h2>
        <p class="text-muted">Thông tin tổng quan về phòng chiếu</p>
    </div>
    <div>
        <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa Phòng
        </a>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Thông tin cơ bản -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white border-0 rounded-top">
                <i class="fas fa-info-circle"></i> Thông Tin Cơ Bản
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 35%;">Mã Phòng</th>
                            <td>#{{ $room->id }}</td>
                        </tr>
                        <tr>
                            <th>Tên Phòng</th>
                            <td><strong>{{ $room->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Thuộc Rạp</th>
                            <td>
                                @if($room->cinema)
                                    <span class="badge bg-secondary">{{ $room->cinema->name }}</span>
                                @else
                                    <span class="text-muted">Không xác định</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Định Dạng (Format)</th>
                            <td><span class="badge bg-info">{{ $room->format }}</span></td>
                        </tr>
                        <tr>
                            <th>Tổng Ghế (Khai báo)</th>
                            <td>{{ $room->total_seats ?? 0 }} ghế</td>
                        </tr>
                        <tr>
                            <th>Trạng Thái</th>
                            <td>
                                @if($room->status === 'ACTIVE')
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                                @elseif($room->status === 'INACTIVE')
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                                @elseif($room->status === 'MAINTENANCE')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-tools"></i> Maintenance</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-lock"></i> Closed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày Tạo</th>
                            <td>{{ $room->created_at ? $room->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Cập Nhật Lần Cuối</th>
                            <td>{{ $room->updated_at ? $room->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thống kê & Mở rộng -->
    <div class="col-md-6 mb-4">
        <div class="row">
            <!-- Box 1 -->
            <div class="col-6 mb-3">
                <div class="stat-box border h-100">
                    <div class="stat-number text-primary">{{ $room->seats->count() }}</div>
                    <div class="stat-label">Ghế đã thiết lập</div>
                    <a href="{{ route('admin.seats.by-room', $room->id) }}" class="btn btn-sm btn-outline-primary mt-2">Xem Sơ đồ ghế</a>
                </div>
            </div>
            
            <!-- Box 2 -->
            <div class="col-6 mb-3">
                <div class="stat-box border h-100">
                    <div class="stat-number text-success">{{ $room->showtimes->count() }}</div>
                    <div class="stat-label">Lịch Chiếu (Tất cả)</div>
                    <!-- Assuming showtimes.index takes room_id in future implementation -->
                    <a href="{{ route('admin.showtimes.index') }}?room_id={{ $room->id }}" class="btn btn-sm btn-outline-success mt-2">Lọc Lịch Chiếu</a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-light text-dark">
                <i class="fas fa-cogs"></i> Hành Động Nhanh
            </div>
            <div class="card-body text-center">
                <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng chiếu này? Mọi ghế và lịch chiếu liên quan có thể bị ảnh hưởng.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa Phòng Chiếu Này
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
