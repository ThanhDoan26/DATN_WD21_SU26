@extends('admin.layouts.app')

@section('title', 'View Cinema - Admin')
@section('page_title', 'Cinema Details')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.cinemas.index') }}">Cinemas</a></li>
            <li class="breadcrumb-item active">{{ $cinema->name }}</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-building"></i> {{ $cinema->name }}</h2>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.cinemas.edit', $cinema->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="confirm('Bạn có chắc chắn?') && fetch('{{ route('admin.cinemas.destroy', $cinema->id) }}', {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => window.location = '{{ route('admin.cinemas.index') }}')">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Info Card -->
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Cinema Name</h6>
                <p><strong>{{ $cinema->name }}</strong></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">City</h6>
                <p><strong>{{ $cinema->city }}</strong></p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Address</h6>
                <p>{{ $cinema->address }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Phone</h6>
                <p>{{ $cinema->phone ?? 'N/A' }}</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Email</h6>
                <p>{{ $cinema->email ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Status</h6>
                <p>
                    @if($cinema->status === 'ACTIVE')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Created</h6>
                <p>{{ $cinema->created_at?->format('d/m/Y H:i') ?? 'Chưa có dữ liệu' }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Updated</h6>
                <p>{{ $cinema->updated_at?->format('d/m/Y H:i') ?? 'Chưa có dữ liệu' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Rooms in this Cinema -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-door-open"></i> Rooms in this Cinema ({{ $cinema->rooms->count() }})
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Format</th>
                    <th>Total Seats</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cinema->rooms as $room)
                <tr>
                    <td><a href="{{ route('admin.rooms.show', $room->id) }}">{{ $room->name }}</a></td>
                    <td>{{ $room->format }}</td>
                    <td>{{ $room->total_seats ?? 0 }}</td>
                    <td>
                        @if($room->status === 'ACTIVE')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">{{ $room->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No rooms found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
