@extends('admin.layouts.app')

@section('title', 'Sửa Đơn Hàng - Admin')
@section('page_title', 'Sửa Đơn Hàng')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-edit"></i> Sửa Đơn Hàng</h2>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-pencil"></i> Thông Tin Đơn Hàng ({{ $booking->booking_code }})
    </div>
    <div class="card-body">
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Khách Hàng</label>
                        <select class="form-select @error('user_id') is-invalid @enderror"
                                id="user_id" name="user_id">
                            <option value="">-- Khách Lẻ (Không có tài khoản) --</option>
                            @forelse($users as $user)
                                <option value="{{ $user->id }}" {{ (string)old('user_id', $booking->user_id) === (string)$user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @empty
                                <option disabled>Không có khách hàng nào</option>
                            @endforelse
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="booking_code" class="form-label">Mã Đặt Vé</label>
                        <input type="text" class="form-control" id="booking_code" value="{{ $booking->booking_code }}" disabled>
                        <small class="text-muted">Mã đặt vé không thể thay đổi</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="showtime_id" class="form-label">Suất Chiếu *</label>
                        <select class="form-select @error('showtime_id') is-invalid @enderror"
                                id="showtime_id" name="showtime_id" required>
                            <option value="">-- Chọn Suất Chiếu --</option>
                            @forelse($showtimes as $showtime)
                                <option value="{{ $showtime->id }}" {{ (string)old('showtime_id', $booking->showtime_id) === (string)$showtime->id ? 'selected' : '' }}>
                                    {{ $showtime->movie->title }} - {{ $showtime->start_time->format('d/m H:i') }}
                                    ({{ $showtime->room->name }})
                                </option>
                            @empty
                                <option disabled>Không có suất chiếu nào</option>
                            @endforelse
                        </select>
                        @error('showtime_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="total_price" class="form-label">Tổng Tiền *</label>
                        <input type="number" class="form-control @error('total_price') is-invalid @enderror"
                               id="total_price" name="total_price" value="{{ old('total_price', $booking->total_price) }}"
                               step="1000" min="0" required>
                        @error('total_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="">-- Chọn Trạng thái --</option>
                            <option value="Pending" {{ old('status', $booking->status) === 'Pending' ? 'selected' : '' }}>Chờ Xử Lý</option>
                            <option value="Paid" {{ old('status', $booking->status) === 'Paid' ? 'selected' : '' }}>Đã Thanh Toán</option>
                            <option value="Used" {{ old('status', $booking->status) === 'Used' ? 'selected' : '' }}>Đã Sử Dụng</option>
                            <option value="Cancelled" {{ old('status', $booking->status) === 'Cancelled' ? 'selected' : '' }}>Đã Hủy</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Phương Thức Thanh Toán</label>
                        <input type="text" class="form-control @error('payment_method') is-invalid @enderror"
                               id="payment_method" name="payment_method" value="{{ old('payment_method', $booking->payment_method) }}"
                               placeholder="VNPay, Momo, Tiền mặt...">
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Ghi Chú</label>
                <textarea class="form-control @error('notes') is-invalid @enderror"
                          id="notes" name="notes" rows="3">{{ old('notes', $booking->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3" id="cancellationReasonField" style="display: none;">
                <label for="cancellation_reason" class="form-label">Lý Do Hủy</label>
                <textarea class="form-control @error('cancellation_reason') is-invalid @enderror"
                          id="cancellation_reason" name="cancellation_reason" rows="2"
                          placeholder="Nhập lý do hủy đơn hàng (nếu có)">{{ old('cancellation_reason', $booking->cancellation_reason) }}</textarea>
                @error('cancellation_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="text-muted">Thông Tin Thêm</h6>
                    <ul class="small text-muted">
                        <li>Thời gian đặt: {{ $booking->created_at->format('d/m/Y H:i') }}</li>
                        @if($booking->payment_time)
                            <li>Thời gian thanh toán: {{ $booking->payment_time->format('d/m/Y H:i') }}</li>
                        @endif
                        @if($booking->cancelled_at)
                            <li>Thời gian hủy: {{ $booking->cancelled_at->format('d/m/Y H:i') }}</li>
                        @endif
                    </ul>
                </div>
            </div>

            <hr>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const cancellationField = document.getElementById('cancellationReasonField');

    function toggleCancellationField() {
        if (statusSelect.value === 'Cancelled') {
            cancellationField.style.display = 'block';
        } else {
            cancellationField.style.display = 'none';
        }
    }

    statusSelect.addEventListener('change', toggleCancellationField);
    toggleCancellationField();
});
</script>
@endsection
