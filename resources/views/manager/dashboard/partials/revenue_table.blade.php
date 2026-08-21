<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="revenue-details-table" style="table-layout: auto;">
        <thead class="table-light">
            <tr>
                <th width="50" class="text-center">STT</th>
                <th width="110">Thời gian</th>
                <th width="125">Mã hóa đơn</th>
                <th width="120">Cụm rạp</th>
                <th>Phim</th>
                <th width="100">Phòng</th>
                <th width="70" class="text-center">Số vé</th>
                <th width="100">Thanh toán</th>
                <th width="120" class="text-end">Doanh thu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailedBookings ?? [] as $index => $booking)
            <tr>
                <td class="text-center text-muted small">{{ $index + 1 }}</td>
                <td>
                    <div class="fw-semibold text-dark" style="font-size: 0.85rem;">
                        {{ $booking->payment_time ? $booking->payment_time->format('d/m/Y') : $booking->created_at->format('d/m/Y') }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.75rem;">
                        <i class="far fa-clock me-1 text-secondary"></i>{{ $booking->payment_time ? $booking->payment_time->format('H:i') : $booking->created_at->format('H:i') }}
                    </div>
                </td>
                <td>
                    <code class="text-primary fw-bold font-monospace bg-light px-2 py-1 rounded" style="font-size: 0.82rem; cursor: pointer; border: 1px solid rgba(0,0,0,0.05);" title="Nhấn để sao chép mã: {{ $booking->booking_code }}" onclick="navigator.clipboard.writeText('{{ $booking->booking_code }}'); alert('Đã sao chép mã đơn hàng!')">
                        {{ substr($booking->booking_code, 0, 6) }}...{{ substr($booking->booking_code, -4) }}
                    </code>
                </td>
                <td>
                    <div class="text-truncate fw-medium" style="max-width: 120px; font-size: 0.85rem;" title="{{ $booking->showtime->room->cinema->name ?? 'N/A' }}">
                        {{ $booking->showtime->room->cinema->name ?? 'N/A' }}
                    </div>
                </td>
                <td>
                    <div class="text-truncate fw-bold text-dark" style="max-width: 140px; font-size: 0.85rem;" title="{{ $booking->showtime->movie->title ?? 'N/A' }}">
                        {{ $booking->showtime->movie->title ?? 'N/A' }}
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark border" style="font-size: 0.8rem;">
                        <i class="fas fa-door-open me-1 text-secondary"></i>{{ $booking->showtime->room->name ?? 'N/A' }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary px-2 py-1" style="font-size: 0.8rem;">{{ $booking->bookedSeats->count() }}</span>
                </td>
                <td>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.78rem; background-color: rgba(25, 135, 84, 0.1);">
                        {{ $booking->payment_method ?? 'Khác' }}
                    </span>
                </td>
                <td class="text-end fw-bold text-success" style="font-size: 0.88rem;">
                    {{ number_format($booking->total_price, 0, ',', '.') }} ₫
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-5">
                    <div class="empty-state py-4 text-center">
                        <i class="fas fa-search text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                        <h6 class="text-muted fw-bold">Không có dữ liệu doanh thu</h6>
                        <p class="text-muted small mb-0">Hãy thử thay đổi bộ lọc.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
