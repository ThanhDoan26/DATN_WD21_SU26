<!-- Seat Map Legend -->
<div class="card mb-3">
    <div class="card-header">
        <i class="fas fa-map"></i> Sơ Đồ Ghế
    </div>
    <div class="card-body">
        <!-- Legend -->
        <div class="seat-map-legend mb-4">
            <div class="legend-item">
                <div class="legend-seat-indicator" style="background-color: #1e3c72; color: white; border: 2px solid #0d1f47;"></div>
                <span><strong>Ghế đặt trong đơn hàng này</strong></span>
            </div>
            <div class="legend-item">
                <div class="legend-seat-indicator" style="background: repeating-linear-gradient(45deg, #d3d3d3, #d3d3d3 10px, #a9a9a9 10px, #a9a9a9 20px); border: 1px solid #808080; color: white; font-weight: 600;"></div>
                <span><strong>Ghế được đặt bởi người khác</strong></span>
            </div>
            <div class="legend-item">
                <div class="legend-seat-indicator" style="background-color: #E3F2FD; border: 1px solid #64B5F6;"></div>
                <span><strong>Ghế Regular</strong></span>
            </div>
            <div class="legend-item">
                <div class="legend-seat-indicator" style="background-color: #FFF3CD; border: 1px solid #FFC107;"></div>
                <span><strong>Ghế VIP</strong></span>
            </div>
            <div class="legend-item">
                <div class="legend-seat-indicator" style="background-color: #FCE4EC; border: 1px solid #EC407A;"></div>
                <span><strong>Ghế Sweetbox</strong></span>
            </div>
        </div>

        <!-- Info -->
        <div class="alert alert-info mb-0">
            <strong>Đã đặt:</strong> {{ $seatMapData['booked_count'] }} ghế / {{ $seatMapData['room_total_seats'] }} ghế trong phòng {{ $seatMapData['room_name'] }}
        </div>
    </div>
</div>
