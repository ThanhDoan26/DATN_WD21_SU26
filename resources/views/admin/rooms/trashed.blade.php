@extends('admin.layouts.app')

@section('title', 'Phòng Đã Xóa - Admin')
@section('page_title', 'Danh sách Phòng Chiếu Đã Xóa')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Phòng Chiếu</a></li>
            <li class="breadcrumb-item active">Đã Xóa</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-trash-alt"></i> Phòng Chiếu Đã Xóa</h2>
        <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">Danh sách các phòng chiếu đã xóa (có thể khôi phục)</p>
    </div>
    <div>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>

<!-- Trashed Rooms Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Phòng Đã Xóa
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
                    <th>Xóa lúc</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms ?? [] as $room)
                <tr style="opacity: 0.7;">
                    <td><strong>#{{ $room->id }}</strong></td>
                    <td>
                        <strong>{{ $room->name }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $room->cinema?->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $room->format }}</span>
                    </td>
                    <td>
                        <strong>{{ $room->total_seats ?? 0 }}</strong> ghế
                    </td>
                    <td>
                        <small class="text-muted">{{ $room->deleted_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Restore Button -->
                            <form action="{{ route('admin.rooms.restore', $room->id) }}" method="POST" style="display:inline;" title="Khôi phục">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Khôi phục">
                                    <i class="fas fa-redo"></i> Khôi Phục
                                </button>
                            </form>

                            <!-- Force Delete Button -->
                            <form action="{{ route('admin.rooms.forceDelete', $room->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Cảnh báo: Điều này sẽ XÓA VĩNH VIỄN phòng này. Không thể hoàn tác!\n\nBạn chắc chắn không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                    <i class="fas fa-times"></i> Xóa Vĩnh Viễn
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Không có phòng nào bị xóa.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($rooms && $rooms->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $rooms->links('pagination::bootstrap-4') }}
</div>
@endif
@endsection
