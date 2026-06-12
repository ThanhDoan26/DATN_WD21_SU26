<!-- Booked Seats Table -->
@if($booking->bookedSeats->count() > 0)
<div class="card mb-3">
    <div class="card-header">
        <i class="fas fa-chair"></i> Chi Tiết Ghế Được Đặt ({{ $booking->bookedSeats->count() }} ghế)
    </div>
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Vị Trí</th>
                    <th>Loại Ghế</th>
                    <th>Giá</th>
                    <th>Trạng Thái</th>
                    <th>Check-in</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->bookedSeats as $bookedSeat)
                <tr>
                    <td><strong>{{ $bookedSeat->seat->row_name }}{{ $bookedSeat->seat->seat_number }}</strong></td>
                    <td>{{ $bookedSeat->seat->seat_type }}</td>
                    <td>{{ number_format($bookedSeat->price_at_booking, 0, ',', '.') }}đ</td>
                    <td>
                        @if($bookedSeat->status === 'PAID')
                            <span class="badge bg-success">Đã Thanh Toán</span>
                        @elseif($bookedSeat->status === 'RESERVED')
                            <span class="badge bg-warning">Chờ Thanh Toán</span>
                        @elseif($bookedSeat->status === 'USED')
                            <span class="badge bg-info">Đã Sử Dụng</span>
                        @elseif($bookedSeat->status === 'CANCELLED')
                            <span class="badge bg-danger">Đã Hủy</span>
                        @endif
                    </td>
                    <td>
                        @if($bookedSeat->checked_in_at)
                            <small class="text-success"><i class="fas fa-check-circle"></i> {{ $bookedSeat->checked_in_at->format('d/m H:i') }}</small>
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>
                    <td>
                        @if($bookedSeat->qr_code)
                            <code class="small">{{ substr($bookedSeat->qr_code, 0, 10) }}...</code>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
