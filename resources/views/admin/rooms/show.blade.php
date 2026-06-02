@extends('admin.layouts.app')

@section('title', 'View Room - Admin')
@section('page_title', 'Room Details')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">{{ $room->name }}</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-door-open"></i> {{ $room->name }}</h2>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="confirm('Bạn có chắc chắn?') && fetch('{{ route('admin.rooms.destroy', $room->id) }}', {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => window.location = '{{ route('admin.rooms.index') }}')">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Info Card -->
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Room Name</h6>
                <p><strong>{{ $room->name }}</strong></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Cinema</h6>
                <p><a href="{{ route('admin.cinemas.show', $room->cinema->id) }}">{{ $room->cinema->name }}</a></p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Format</h6>
                <p><strong>{{ $room->format }}</strong></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Total Seats</h6>
                <p><strong>{{ $room->total_seats ?? 0 }}</strong> ghế</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Status</h6>
                <p>
                    @if($room->status === 'ACTIVE')
                        <span class="badge bg-success">Active</span>
                    @elseif($room->status === 'MAINTENANCE')
                        <span class="badge bg-warning">Maintenance</span>
                    @elseif($room->status === 'CLOSED')
                        <span class="badge bg-danger">Closed</span>
                    @else
                        <span class="badge bg-secondary">{{ $room->status }}</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Created</h6>
                <p>{{ $room->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Seats in this Room -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-chair"></i> Seats in this Room ({{ $room->seats->count() }})
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Seat Type</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($room->seats as $seat)
                <tr>
                    <td><strong>{{ $seat->row_name }}{{ $seat->seat_number }}</strong></td>
                    <td><span class="badge bg-info">{{ $seat->seat_type }}</span></td>
                    <td>
                        @if($seat->status === 'AVAILABLE')
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-danger">Unavailable</span>
                        @endif
                    </td>
                    <td>{{ $seat->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No seats found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
