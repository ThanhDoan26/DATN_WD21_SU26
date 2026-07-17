<!-- Booked Combos Table -->
@if($booking->combos->count() > 0)
<div class="card mb-3">
    <div class="card-header">
        <i class="fas fa-utensils"></i> Chi Tiết Combo Đã Đặt ({{ $booking->combos->sum('pivot.quantity') }} combo)
    </div>
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Tên Combo</th>
                    <th>Hình Ảnh</th>
                    <th>Đơn Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->combos as $combo)
                <tr>
                    <td><strong>{{ $combo->name }}</strong></td>
                    <td>
                        @if($combo->image)
                            <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        @else
                            <span class="text-muted">Không có ảnh</span>
                        @endif
                    </td>
                    <td>{{ number_format($combo->pivot->price, 0, ',', '.') }}đ</td>
                    <td>{{ $combo->pivot->quantity }}</td>
                    <td>{{ number_format($combo->pivot->price * $combo->pivot->quantity, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
