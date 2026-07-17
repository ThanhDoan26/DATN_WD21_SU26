<!-- Seat Map Grid -->
<div class="d-none d-lg-block" id="seatMapContainer">
    <div class="seat-map-grid">
        <!-- Row labels and seats -->
        @foreach($seatMapData['all_seats_grouped'] as $rowName => $seats)
            <div class="seat-row-label">{{ $rowName }}</div>
            <div class="seat-row-cells">
                @foreach($seats as $seat)
                    @php
                        $isBookedCurrent = in_array($seat->id, $seatMapData['booked_by_current_booking']);
                        $isBookedOther = in_array($seat->id, $seatMapData['booked_by_others']);
                        $statusClass = '';
                        $typeClass = 'seat-type-' . strtolower(str_replace(' ', '-', $seat->seat_type));

                        if ($isBookedCurrent) {
                            $statusClass = 'seat-booked-current';
                        } elseif ($isBookedOther) {
                            $statusClass = 'seat-booked-other';
                        }

                        if ($seat->status === 'UNAVAILABLE') {
                            $statusClass = 'seat-unavailable';
                        }
                    @endphp
                    <div class="seat-cell {{ $typeClass }} {{ $statusClass }} seat-{{ $seat->id }}">
                        {{ $seat->row_name }}{{ $seat->seat_number }}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

<style>
/* Seat Map Container */
.seat-map-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: auto 1fr;
    padding: 10px 0;
    overflow-x: auto;
}

.seat-row-label {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    font-weight: 600;
    color: #666;
    font-size: 13px;
}

.seat-row-cells {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

/* Seat Cells */
.seat-cell {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid #ddd;
    transition: all 0.2s ease;
    user-select: none;
}

.seat-cell:hover:not(.seat-unavailable):not(.seat-booked-other) {
    box-shadow: 0 2px 8px rgba(30, 60, 114, 0.2);
    transform: translateY(-2px);
}

/* Seat Types - Colors */
.seat-type-regular {
    background-color: #E3F2FD;
    border-color: #64B5F6;
    color: #333;
}

.seat-type-vip {
    background-color: #FFF3CD;
    border-color: #FFC107;
    color: #333;
}

.seat-type-sweetbox {
    background-color: #FCE4EC;
    border-color: #EC407A;
    color: #333;
}

/* Seat Status - Booked in Current Order */
.seat-booked-current {
    background-color: var(--primary-color) !important;
    color: white !important;
    border: 2px solid var(--sidebar-bg) !important;
    font-weight: 600;
    box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.2);
}

/* Seat Status - Booked by Others */
.seat-booked-other {
    background: repeating-linear-gradient(
        45deg,
        #d3d3d3,
        #d3d3d3 10px,
        #a9a9a9 10px,
        #a9a9a9 20px
    ) !important;
    border-color: #808080 !important;
    cursor: not-allowed;
    color: #ffffff !important;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Seat Status - Unavailable */
.seat-unavailable {
    background: repeating-linear-gradient(
        45deg,
        #f0f0f0,
        #f0f0f0 10px,
        #e0e0e0 10px,
        #e0e0e0 20px
    ) !important;
    cursor: not-allowed;
    opacity: 0.6;
    border-color: #999 !important;
    color: #999 !important;
}

/* Seat Map Legend */
.seat-map-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 15px 0;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
}

.legend-seat-indicator {
    width: 24px;
    height: 24px;
    border-radius: 3px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive */
@media (max-width: 1200px) {
    .seat-cell {
        width: 38px;
        height: 38px;
        font-size: 10px;
    }
}

@media (max-width: 768px) {
    #seatMapContainer {
        display: none !important;
    }
}
</style>
