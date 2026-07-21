@extends('admin.layouts.app')

@section('title', 'Seats - Admin')
@section('page_title', 'Seats Management')

@section('extra_css')
<style>
    .seat-map-wrapper {
        background: #ffffff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 20px 0;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
    }

    .cinema-screen {
        width: 80%;
        max-width: 600px;
        margin: 0 auto 40px auto;
        padding: 12px 0;
        text-align: center;
        background: linear-gradient(180deg, rgba(147, 51, 234, 0.12) 0%, rgba(147, 51, 234, 0.02) 100%);
        border-top: 6px solid var(--primary-color);
        border-radius: 8px 8px 120px 120px;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 8px;
        color: var(--primary-color);
        box-shadow: 0 8px 25px -8px rgba(147, 51, 234, 0.25);
        text-transform: uppercase;
        font-family: 'Sora', sans-serif;
    }

    .seat-layout-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-width: 580px;
        padding: 10px 0;
    }

    .seat-row {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        gap: 8px;
    }

    .row-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #94a3b8;
        width: 30px;
        user-select: none;
    }

    .row-label.left {
        text-align: right;
        margin-right: 15px;
    }

    .row-label.right {
        text-align: left;
        margin-left: 15px;
    }

    .seat-row-seats {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seat {
        width: 42px;
        height: 42px;
        border: 2px solid transparent;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        user-select: none;
    }

    .seat:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        filter: brightness(1.1);
    }

    /* Curated Seat type colors */
    .seat.regular {
        background-color: #0ea5e9; /* Sleek Sky Blue */
        border-color: #0284c7;
    }

    .seat.vip {
        background-color: #f59e0b; /* Golden Amber */
        color: #1e293b;
        border-color: #d97706;
    }

    .seat.sweetbox {
        background-color: #ec4899; /* Pink Rose */
        width: 90px; /* Double width couple seat! */
        border-color: #db2777;
    }

    /* Seat state modifiers */
    .seat.unavailable {
        background-color: #cbd5e1 !important;
        border-color: #94a3b8 !important;
        color: #64748b !important;
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.75;
    }

    .seat.unavailable:hover {
        transform: none;
        box-shadow: none;
        filter: none;
    }

    /* Active Selection */
    .seat.selected-active {
        background-color: #22c55e !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
        outline: none !important;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.4);
<<<<<<< HEAD
        outline: 3px solid var(--primary-color);
        outline-offset: 2px;
=======
>>>>>>> 9730541d563131ed93072a9122ca8bda6ec5f09b
        animation: pulseSelection 1.5s infinite;
    }

    @keyframes pulseSelection {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        0% { outline-color: rgba(147, 51, 234, 0.8); }
        50% { outline-color: rgba(147, 51, 234, 0.1); }
        100% { outline-color: rgba(147, 51, 234, 0.8); }

    }

    .seat-legend {
        display: flex;
        gap: 20px;
        margin: 10px 0 30px 0;
        flex-wrap: wrap;
        justify-content: center;
        background-color: var(--bg-base);
        padding: 15px 25px;
        border-radius: 12px;
        border: 1px solid var(--border-light);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #475569;
    }

    .legend-box {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .filter-section {
        background: var(--bg-surface);
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-light);
    }

    .filter-section select {
        padding: 10px 14px;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        font-size: 0.92rem;
        color: var(--text-ink);
        transition: all 0.2s;
        background-color: var(--bg-base);
    }
    
    .filter-section select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.12);
        outline: none;
        background-color: #fff;
    }

    /* Loading Spinner Styling */
    .loading-overlay {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px 0;
        width: 100%;
    }

    /* Custom color badges for dynamic table */
    .bg-sky {
        background-color: #0ea5e9 !important;
        color: #ffffff;
    }
    
    .bg-pink {
        background-color: #ec4899 !important;
        color: #ffffff;
    }

    .bg-gold {
        background-color: #f59e0b !important;
        color: #1e293b;
    }
</style>
@endsection

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Seats</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <div>
        <h2><i class="fas fa-chair"></i> Sơ đồ Ghế Ngồi</h2>
        <p class="text-muted" style="margin-top: 5px;">Xem sơ đồ ghế vật lý của các phòng chiếu</p>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row align-items-end">
        <div class="col-md-5 mb-3 mb-md-0">
            <label for="cinemaFilter" class="form-label fw-bold text-secondary"><i class="fas fa-building text-primary me-1"></i> Lọc theo Rạp:</label>
            <select id="cinemaFilter" class="form-select" onchange="filterByCinema(this.value)">
                <option value="">-- Chọn Rạp --</option>
                @forelse(($cinemas ?? []) as $cinema)
                    <option value="{{ $cinema->id }}">{{ $cinema->name }}</option>
                @empty
                    <option disabled>Không có rạp nào</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-5">
            <label for="roomFilter" class="form-label fw-bold text-secondary"><i class="fas fa-door-open text-primary me-1"></i> Lọc theo Phòng:</label>
            <select id="roomFilter" class="form-select" onchange="filterByRoom(this.value)">
                <option value="">-- Chọn Phòng --</option>
                @forelse(($rooms ?? []) as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @empty
                    <option disabled>Không có phòng nào</option>
                @endforelse
            </select>
        </div>
    </div>
</div>

<!-- Seats Map -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-th"></i> Sơ đồ Ghế vật lý</span>
        <span id="selectedRoomLabel" class="badge bg-light text-primary fw-bold px-3 py-2">Chưa chọn phòng</span>
    </div>
    <div class="card-body text-center">
        <!-- Legend -->
        <div class="seat-legend">
            <div class="legend-item">
                <div class="legend-box" style="background: #0ea5e9;">R</div>
                <span>Regular (Thường)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #f59e0b; color: #1e293b;">V</div>
                <span>VIP (Cao cấp)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #ec4899; width: 45px;">S</div>
                <span>Sweetbox (Đôi)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #10b981;"><i class="fas fa-check"></i></div>
                <span>Available (Trống)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #94a3b8;"><i class="fas fa-wrench"></i></div>
                <span>Unavailable (Hỏng)</span>
            </div>
        </div>

        <!-- Seats Map Wrapper -->
        <div class="seat-map-wrapper">
            <div class="cinema-screen" id="screenLabel">Màn hình chiếu</div>
            
            <div id="seatsGrid" class="w-100">
                <div class="text-center py-5">
                    <i class="fas fa-mouse-pointer text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="text-muted mt-3 fw-semibold">Vui lòng chọn Rạp và Phòng chiếu ở bộ lọc phía trên để hiển thị sơ đồ ghế.</p>
                </div>
            </div>

            <!-- Seat Detail Card Inside Map -->
            <div class="w-100 mt-4" id="seatDetailContainer" style="display: none; max-width: 700px; text-align: left;">
                <div class="card border-primary" style="background-color: #f8fafc; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary m-0 fw-bold"><i class="fas fa-info-circle me-1"></i> Chi tiết Ghế được chọn</h6>
                            <button type="button" class="btn-close" aria-label="Close" onclick="closeSeatDetail()"></button>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-2 col-3 text-center">
                                <div id="detailSeatIcon" class="seat regular" style="width: 55px; height: 55px; font-size: 0.95rem; margin: 0 auto; pointer-events: none;">A1</div>
                            </div>
                            <div class="col-md-10 col-9">
                                <div class="row">
                                    <div class="col-sm-3 col-6 mb-2">
                                        <div class="text-muted small">Mã Ghế ID</div>
                                        <strong id="detailSeatId">#123</strong>
                                    </div>
                                    <div class="col-sm-3 col-6 mb-2">
                                        <div class="text-muted small">Vị trí</div>
                                        <span id="detailSeatPos" class="badge bg-primary text-white fw-bold">A1</span>
                                    </div>
                                    <div class="col-sm-3 col-6 mb-2">
                                        <div class="text-muted small">Loại Ghế</div>
                                        <span id="detailSeatType" class="badge bg-sky">Regular</span>
                                    </div>
                                    <div class="col-sm-3 col-6 mb-2">
                                        <div class="text-muted small">Trạng thái</div>
                                        <span id="detailSeatStatus" class="badge bg-success">Available</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mt-4">
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="fas fa-chair" style="font-size: 1.5rem; color: #0ea5e9;"></i>
                    <div class="stat-number" id="regularCount">0</div>
                    <div class="stat-label">Regular Seats</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="fas fa-crown" style="font-size: 1.5rem; color: #f59e0b;"></i>
                    <div class="stat-number" id="vipCount">0</div>
                    <div class="stat-label">VIP Seats</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="fas fa-heart" style="font-size: 1.5rem; color: #ec4899;"></i>
                    <div class="stat-number" id="sweetboxCount">0</div>
                    <div class="stat-label">Sweetbox Seats</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #10b981;"></i>
                    <div class="stat-number" id="availableCount">0</div>
                    <div class="stat-label">Available Seats</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seats Table -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-list"></i> Danh sách Ghế Chi tiết
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Phòng</th>
                    <th>Vị trí</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th>Tạo lúc</th>
                </tr>
            </thead>
            <tbody id="seatsTableBody">
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Vui lòng chọn phòng chiếu.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center" id="paginationContainer" style="display: none !important;">
        <div class="text-muted small" id="paginationInfo">Hiển thị 0 đến 0 của 0 mục</div>
        <ul class="pagination pagination-sm m-0" id="paginationControls">
            <!-- Pagination items will be injected here -->
        </ul>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    // Nhúng toàn bộ phòng chiếu để thực hiện lọc động
    const rooms = @json($rooms);
    let selectedSeatElement = null;
    let currentSeats = [];
    let currentRoomName = '';
    let currentPage = 1;
    const itemsPerPage = 10;

    function filterByCinema(cinemaId) {
        const roomSelect = document.getElementById('roomFilter');
        
        // Reset dropdown phòng
        roomSelect.innerHTML = '<option value="">-- Chọn Phòng --</option>';
        
        if (!cinemaId) {
            // Hiển thị tất cả phòng nếu không chọn rạp
            rooms.forEach(room => {
                const opt = document.createElement('option');
                opt.value = room.id;
                opt.textContent = room.name;
                roomSelect.appendChild(opt);
            });
            clearSeatMap("Vui lòng chọn Rạp và Phòng chiếu ở bộ lọc phía trên để hiển thị sơ đồ ghế.");
            return;
        }
        
        // Lọc phòng thuộc rạp đã chọn
        const filteredRooms = rooms.filter(r => r.cinema_id == cinemaId);
        
        filteredRooms.forEach(room => {
            const opt = document.createElement('option');
            opt.value = room.id;
            opt.textContent = room.name;
            roomSelect.appendChild(opt);
        });
        
        // Tự động chọn phòng đầu tiên
        if (filteredRooms.length > 0) {
            roomSelect.value = filteredRooms[0].id;
            filterByRoom(filteredRooms[0].id);
        } else {
            clearSeatMap("Rạp này chưa cấu hình phòng chiếu nào.");
        }
    }

    function filterByRoom(roomId) {
        const roomSelect = document.getElementById('roomFilter');
        const roomLabel = document.getElementById('selectedRoomLabel');
        
        if (!roomId) {
            roomLabel.textContent = "Chưa chọn phòng";
            clearSeatMap("Vui lòng chọn Phòng chiếu để xem sơ đồ ghế.");
            return;
        }
        
        // Lấy tên phòng đang chọn
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const roomName = selectedOption ? selectedOption.text : "N/A";
        roomLabel.textContent = roomName;
        
        showLoading();
        
        // Gọi AJAX đến route có sẵn: /admin/seats/by-room/{roomId}
        fetch(`/admin/seats/by-room/${roomId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể kết nối đến hệ thống hoặc tải danh sách ghế thất bại.');
                }
                return response.json();
            })
            .then(seats => {
                renderSeatMap(seats);
                currentSeats = seats;
                currentRoomName = roomName;
                currentPage = 1;
                renderSeatsTable();
            })
            .catch(error => {
                console.error('Lỗi khi tải danh sách ghế:', error);
                showError(error.message);
            });
    }

    function renderSeatMap(seats) {
        const grid = document.getElementById('seatsGrid');
        grid.innerHTML = '';
        
        if (seats.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="text-muted mt-3 fw-semibold">Phòng chiếu này chưa được thiết lập ghế vật lý.</p>
                </div>
            `;
            updateStats([]);
            return;
        }
        
        // Gom nhóm các ghế theo row_name (Hàng ghế)
        const rows = {};
        seats.forEach(seat => {
            if (!rows[seat.row_name]) {
                rows[seat.row_name] = [];
            }
            rows[seat.row_name].push(seat);
        });
        
        // Sắp xếp các hàng chữ cái (A, B, C, D...)
        const sortedRowNames = Object.keys(rows).sort();
        
        const container = document.createElement('div');
        container.className = 'seat-layout-container';
        
        sortedRowNames.forEach(rowName => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'seat-row';
            
            // Nhãn bên trái
            const leftLabel = document.createElement('div');
            leftLabel.className = 'row-label left';
            leftLabel.textContent = rowName;
            rowDiv.appendChild(leftLabel);
            
            // Wrapper chứa các ghế của hàng
            const seatsWrapper = document.createElement('div');
            seatsWrapper.className = 'seat-row-seats';
            
            // Sắp xếp các ghế trong hàng theo seat_number (1, 2, 3...)
            const sortedSeats = rows[rowName].sort((a, b) => parseInt(a.seat_number) - parseInt(b.seat_number));
            
            sortedSeats.forEach(seat => {
                const seatDiv = document.createElement('div');
                seatDiv.className = `seat ${seat.seat_type.toLowerCase()} ${seat.status.toLowerCase()}`;
                
                // Hiển thị nội dung dựa trên trạng thái
                if (seat.status === 'UNAVAILABLE') {
                    seatDiv.innerHTML = `<i class="fas fa-wrench" title="Ghế Hỏng"></i>`;
                } else {
                    seatDiv.textContent = `${seat.row_name}${seat.seat_number}`;
                }
                
                // Thêm tooltip thông tin cơ bản
                seatDiv.title = `Ghế ${seat.row_name}${seat.seat_number} - Loại: ${seat.seat_type} - Trạng thái: ${seat.status}`;
                
                // Event click chọn ghế
                seatDiv.addEventListener('click', function() {
                    selectSeat(this, seat);
                });
                
                seatsWrapper.appendChild(seatDiv);
            });
            
            rowDiv.appendChild(seatsWrapper);
            
            // Nhãn bên phải
            const rightLabel = document.createElement('div');
            rightLabel.className = 'row-label right';
            rightLabel.textContent = rowName;
            rowDiv.appendChild(rightLabel);
            
            container.appendChild(rowDiv);
        });
        
        grid.appendChild(container);
        updateStats(seats);
    }

    function renderSeatsTable() {
        const tableBody = document.getElementById('seatsTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        tableBody.innerHTML = '';
        
        if (currentSeats.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Không có ghế nào cho phòng chiếu này.</p>
                    </td>
                </tr>
            `;
            if (paginationContainer) {
                paginationContainer.style.setProperty('display', 'none', 'important');
            }
            return;
        }
        
        if (paginationContainer) {
            paginationContainer.style.setProperty('display', 'flex', 'important');
        }
        
        const totalItems = currentSeats.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const paginatedSeats = currentSeats.slice(startIndex, endIndex);
        
        paginatedSeats.forEach(seat => {
            const row = document.createElement('tr');
            
            // ID
            const tdId = document.createElement('td');
            tdId.innerHTML = `<strong>#${seat.id}</strong>`;
            row.appendChild(tdId);
            
            // Room Name
            const tdRoom = document.createElement('td');
            tdRoom.textContent = currentRoomName;
            row.appendChild(tdRoom);
            
            // Position
            const tdPos = document.createElement('td');
            tdPos.innerHTML = `<span class="badge bg-primary text-white fw-bold">${seat.row_name}${seat.seat_number}</span>`;
            row.appendChild(tdPos);
            
            // Type Badge
            const tdType = document.createElement('td');
            let typeClass = 'bg-secondary';
            if (seat.seat_type === 'Regular') typeClass = 'bg-sky';
            else if (seat.seat_type === 'VIP') typeClass = 'bg-gold';
            else if (seat.seat_type === 'Sweetbox') typeClass = 'bg-pink';
            
            tdType.innerHTML = `<span class="badge ${typeClass}">${seat.seat_type}</span>`;
            row.appendChild(tdType);
            
            // Status Badge
            const tdStatus = document.createElement('td');
            if (seat.status === 'AVAILABLE') {
                tdStatus.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Available</span>';
            } else {
                tdStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Unavailable</span>';
            }
            row.appendChild(tdStatus);
            
            // Created At
            const tdCreated = document.createElement('td');
            let dateStr = 'N/A';
            if (seat.created_at) {
                const date = new Date(seat.created_at);
                dateStr = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
            }
            tdCreated.innerHTML = `<small class="text-muted">${dateStr}</small>`;
            row.appendChild(tdCreated);
            
            tableBody.appendChild(row);
        });
        
        renderPaginationControls(totalItems, totalPages, startIndex, endIndex);
    }

    function renderPaginationControls(totalItems, totalPages, startIndex, endIndex) {
        document.getElementById('paginationInfo').textContent = `Hiển thị ${startIndex + 1} đến ${endIndex} của ${totalItems} mục`;
        
        const controls = document.getElementById('paginationControls');
        controls.innerHTML = '';
        
        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="${currentPage === 1 ? '' : `changePage(${currentPage - 1})`}">Trước</a>`;
        controls.appendChild(prevLi);
        
        // Page numbers
        let startPage = 1;
        let endPage = totalPages;
        
        if (totalPages > 7) {
            if (currentPage <= 4) {
                startPage = 1;
                endPage = 5;
            } else if (currentPage >= totalPages - 3) {
                startPage = totalPages - 4;
                endPage = totalPages;
            } else {
                startPage = currentPage - 2;
                endPage = currentPage + 2;
            }
        }
        
        if (startPage > 1) {
            controls.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="changePage(1)">1</a></li>`;
            if (startPage > 2) {
                controls.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === i ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="changePage(${i})">${i}</a>`;
            controls.appendChild(li);
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                controls.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            controls.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="changePage(${totalPages})">${totalPages}</a></li>`;
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="${currentPage === totalPages ? '' : `changePage(${currentPage + 1})`}">Sau</a>`;
        controls.appendChild(nextLi);
    }

    function changePage(page) {
        currentPage = page;
        renderSeatsTable();
    }

    function selectSeat(element, seat) {
        // Hủy chọn ghế trước đó
        if (selectedSeatElement) {
            selectedSeatElement.classList.remove('selected-active');
        }
        
        // Đặt ghế hiện tại làm active
        selectedSeatElement = element;
        element.classList.add('selected-active');
        
        // Hiển thị khung thông tin ghế
        document.getElementById('seatDetailContainer').style.display = 'block';
        
        // Đổ dữ liệu vào card chi tiết
        document.getElementById('detailSeatId').textContent = `#${seat.id}`;
        document.getElementById('detailSeatPos').textContent = `${seat.row_name}${seat.seat_number}`;
        
        const typeBadge = document.getElementById('detailSeatType');
        typeBadge.textContent = seat.seat_type;
        typeBadge.className = 'badge';
        if (seat.seat_type === 'Regular') typeBadge.classList.add('bg-sky');
        else if (seat.seat_type === 'VIP') typeBadge.classList.add('bg-gold');
        else if (seat.seat_type === 'Sweetbox') typeBadge.classList.add('bg-pink');
        else typeBadge.classList.add('bg-secondary');
        
        const statusBadge = document.getElementById('detailSeatStatus');
        statusBadge.textContent = seat.status;
        statusBadge.className = 'badge';
        if (seat.status === 'AVAILABLE') {
            statusBadge.classList.add('bg-success');
        } else {
            statusBadge.classList.add('bg-danger');
        }
        
        // Icon xem trước ở card chi tiết
        const detailIcon = document.getElementById('detailSeatIcon');
        detailIcon.textContent = `${seat.row_name}${seat.seat_number}`;
        detailIcon.className = `seat ${seat.seat_type.toLowerCase()}`;
        if (seat.status === 'UNAVAILABLE') {
            detailIcon.classList.add('unavailable');
            detailIcon.innerHTML = `<i class="fas fa-wrench"></i>`;
        } else {
            detailIcon.innerHTML = `${seat.row_name}${seat.seat_number}`;
        }
    }

    function closeSeatDetail() {
        document.getElementById('seatDetailContainer').style.display = 'none';
        if (selectedSeatElement) {
            selectedSeatElement.classList.remove('selected-active');
            selectedSeatElement = null;
        }
    }

    function updateStats(seats) {
        const regular = seats.filter(s => s.seat_type === 'Regular').length;
        const vip = seats.filter(s => s.seat_type === 'VIP').length;
        const sweetbox = seats.filter(s => s.seat_type === 'Sweetbox').length;
        const available = seats.filter(s => s.status === 'AVAILABLE').length;
        
        document.getElementById('regularCount').textContent = regular;
        document.getElementById('vipCount').textContent = vip;
        document.getElementById('sweetboxCount').textContent = sweetbox;
        document.getElementById('availableCount').textContent = available;
    }

    function showLoading() {
        const grid = document.getElementById('seatsGrid');
        grid.innerHTML = `
            <div class="loading-overlay">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3 fw-semibold">Đang tải cấu trúc sơ đồ ghế vật lý...</p>
            </div>
        `;
        closeSeatDetail();
    }

    function showError(message) {
        const grid = document.getElementById('seatsGrid');
        grid.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <p class="text-danger mt-3 fw-semibold">${message}</p>
            </div>
        `;
        closeSeatDetail();
    }

    function clearSeatMap(message) {
        const grid = document.getElementById('seatsGrid');
        grid.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-info-circle text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                <p class="text-muted mt-3 fw-semibold">${message}</p>
            </div>
        `;
        document.getElementById('seatsTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">Vui lòng chọn phòng chiếu hợp lệ.</p>
                </td>
            </tr>
        `;
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) {
            paginationContainer.style.setProperty('display', 'none', 'important');
        }
        updateStats([]);
        closeSeatDetail();
    }

    // Khởi tạo mặc định khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const cinemaSelect = document.getElementById('cinemaFilter');
        
        // Nếu đã có rạp được thêm, chọn rạp đầu tiên mặc định
        if (cinemaSelect && cinemaSelect.options.length > 1 && !cinemaSelect.value) {
            cinemaSelect.selectedIndex = 1;
        }
        
        if (cinemaSelect) {
            filterByCinema(cinemaSelect.value);
        }
    });
</script>
@endsection
