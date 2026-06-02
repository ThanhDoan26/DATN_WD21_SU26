@extends('admin.layouts.app')

@section('title', 'Cinemas - Admin')
@section('page_title', 'Cinemas Management')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Cinemas</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-building"></i> Danh sách Rạp Chiếu Phim</h2>
        <p class="text-muted" style="margin-top: 5px;">Xem danh sách tất cả các cụm rạp trong hệ thống</p>
    </div>
</div>

<!-- Cinemas Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table"></i> Danh sách Rạp
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Rạp</th>
                    <th>Địa chỉ</th>
                    <th>Thành phố</th>
                    <th>Điện thoại</th>
                    <th>Trạng thái</th>
                    <th>Tạo lúc</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cinemas ?? [] as $cinema)
                <tr>
                    <td><strong>#{{ $cinema->id }}</strong></td>
                    <td>
                        <strong>{{ $cinema->name }}</strong>
                    </td>
                    <td>{{ $cinema->address }}</td>
                    <td>
                        <span class="badge bg-info">{{ $cinema->city }}</span>
                    </td>
                    <td>{{ $cinema->phone ?? 'N/A' }}</td>
                    <td>
                        @if($cinema->status === 'ACTIVE')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $cinema->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Chưa có rạp nào.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
