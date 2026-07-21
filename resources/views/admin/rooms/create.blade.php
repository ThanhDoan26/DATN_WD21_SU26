@extends('admin.layouts.app')

@section('title', 'Create Room - Admin')
@section('page_title', 'Create New Room')

@section('extra_css')
<style>
    .seat-preview-wrapper { background: var(--bg-surface); padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; margin: 15px 0; border: 1px solid var(--border-light); overflow-x: auto; }
    .preview-screen { width: 70%; max-width: 500px; margin: 0 auto 30px auto; padding: 10px 0; text-align: center; background: linear-gradient(180deg, rgba(147, 51, 234, 0.12) 0%, rgba(147, 51, 234, 0.02) 100%); border-top: 5px solid var(--primary-color); border-radius: 8px 8px 100px 100px; font-size: 0.75rem; font-weight: 700; letter-spacing: 6px; color: var(--primary-color); text-transform: uppercase; font-family: 'Sora', sans-serif; }
    .preview-layout { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; }
    .preview-row { display: flex; align-items: center; justify-content: center; gap: 5px; }
    .preview-row-label { font-size: 0.7rem; font-weight: 700; color: #94a3b8; width: 22px; text-align: center; }
    .preview-seat { width: 28px; height: 28px; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; font-weight: 600; color: #ffffff; transition: all 0.2s; }
    .preview-seat.regular { background-color: #0ea5e9; }
    .preview-seat.vip { background-color: #f59e0b; color: #1e293b; }
    .preview-seat.sweetbox { background-color: #ec4899; width: 58px; }
    .preview-legend { display: flex; gap: 16px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
    .preview-legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #475569; }
    .preview-legend-box { width: 18px; height: 18px; border-radius: 4px; }
    .calc-info { background: var(--bg-base); border: 1px solid var(--border-light); border-radius: 10px; padding: 15px; margin-top: 15px; }
    .calc-info .calc-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 0.9rem; }
    .calc-info .calc-total { font-size: 1.1rem; font-weight: 700; color: var(--primary-color); border-top: 2px solid var(--border-light); padding-top: 8px; margin-top: 4px; font-family: 'Sora', sans-serif; }
</style>
@endsection

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<!-- Page Title -->
<div class="page-title">
    <h2><i class="fas fa-door-open"></i> Thêm Phòng Mới</h2>
</div>

<div class="row">
    <!-- Cột trái: Form nhập liệu -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> Thông Tin Phòng Chiếu
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rooms.store') }}" method="POST" id="createRoomForm">
                    @csrf

                    <div class="mb-3">
                        <label for="cinema_id" class="form-label">Rạp *</label>
                        <select class="form-select @error('cinema_id') is-invalid @enderror"
                                id="cinema_id" name="cinema_id" required onchange="showCinemaInfo()">
                            <option value="">-- Chọn Rạp --</option>
                            @forelse($cinemas as $cinema)
                                <option value="{{ $cinema->id }}" 
                                        data-address="{{ $cinema->address }}"
                                        data-city="{{ $cinema->city }}"
                                        data-phone="{{ $cinema->phone }}"
                                        {{ old('cinema_id') === (string)$cinema->id ? 'selected' : '' }}>
                                    {{ $cinema->name }}
                                </option>
                            @empty
                                <option disabled>Không có rạp nào</option>
                            @endforelse
                        </select>
                        @error('cinema_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Hiển thị thông tin rạp tương ứng -->
                        <div id="cinema_info" class="mt-2 p-2 border rounded bg-light" style="display: none; font-size: 0.9em;">
                            <strong>Địa chỉ:</strong> <span id="c_address"></span>, <span id="c_city"></span><br>
                            <strong>SĐT:</strong> <span id="c_phone"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Phòng *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" placeholder="vd: Cinema 1, IMAX 2" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="format" class="form-label">Phân loại phòng (Format) *</label>
                        <select class="form-select @error('format') is-invalid @enderror" id="format" name="format" required>
                            <option value="">-- Chọn phân loại --</option>
                            <option value="2D" {{ old('format') == '2D' ? 'selected' : '' }}>2D</option>
                            <option value="3D" {{ old('format') == '3D' ? 'selected' : '' }}>3D</option>
                            <option value="IMAX" {{ old('format') == 'IMAX' ? 'selected' : '' }}>IMAX</option>
                            <option value="4DX" {{ old('format') == '4DX' ? 'selected' : '' }}>4DX</option>
                        </select>
                        @error('format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="total_rows" class="form-label">Số Hàng (Rows) *</label>
                                <input type="number" class="form-control @error('total_rows') is-invalid @enderror"
                                       id="total_rows" name="total_rows" value="{{ old('total_rows', 8) }}" min="1" max="26" required>
                                <div class="form-text">Từ 1 đến 26 hàng (A-Z)</div>
                                @error('total_rows')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="total_cols" class="form-label">Số Cột (Columns) *</label>
                                <input type="number" class="form-control @error('total_cols') is-invalid @enderror"
                                       id="total_cols" name="total_cols" value="{{ old('total_cols', 12) }}" min="1" max="30" required>
                                <div class="form-text">Từ 1 đến 30 ghế/hàng</div>
                                @error('total_cols')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Hidden total_seats - sẽ tự tính -->
                    <input type="hidden" name="total_seats" id="total_seats" value="{{ old('total_seats', 96) }}">

                    <!-- Thông tin tính toán -->
                    <div class="calc-info">
                        <div class="calc-row">
                            <span><i class="fas fa-chair text-info"></i> Regular (3 hàng đầu)</span>
                            <span id="calcRegular">36</span>
                        </div>
                        <div class="calc-row">
                            <span><i class="fas fa-crown text-warning"></i> VIP (hàng giữa)</span>
                            <span id="calcVip">48</span>
                        </div>
                        <div class="calc-row">
                            <span><i class="fas fa-heart text-danger"></i> Sweetbox (hàng cuối)</span>
                            <span id="calcSweetbox">12</span>
                        </div>
                        <div class="calc-row calc-total">
                            <span><i class="fas fa-calculator"></i> Tổng ghế</span>
                            <span id="calcTotal">96</span>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="MAINTENANCE" {{ old('status') === 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                            <option value="CLOSED" {{ old('status') === 'CLOSED' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo Phòng & Ghế Tự Động
                        </button>
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột phải: Preview sơ đồ ghế -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-dark">
                <i class="fas fa-eye"></i> Xem Trước Sơ Đồ Ghế
            </div>
            <div class="card-body">
                <div class="seat-preview-wrapper">
                    <div class="preview-screen">
                        <i class="fas fa-tv"></i> MÀN HÌNH
                    </div>
                    <div id="seatPreview" class="preview-layout"></div>
                    <div class="preview-legend">
                        <div class="preview-legend-item">
                            <div class="preview-legend-box" style="background:#0ea5e9;"></div>
                            <span>Regular</span>
                        </div>
                        <div class="preview-legend-item">
                            <div class="preview-legend-box" style="background:#f59e0b;"></div>
                            <span>VIP</span>
                        </div>
                        <div class="preview-legend-item">
                            <div class="preview-legend-box" style="background:#ec4899;"></div>
                            <span>Sweetbox</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    const rowsInput = document.getElementById('total_rows');
    const colsInput = document.getElementById('total_cols');
    const totalSeatsInput = document.getElementById('total_seats');

    function updatePreview() {
        const rows = parseInt(rowsInput.value) || 0;
        const cols = parseInt(colsInput.value) || 0;
        const total = rows * cols;

        totalSeatsInput.value = total;

        // Tính toán phân bổ ghế
        let rCount = 0, vCount = 0, sCount = 0;
        for (let r = 1; r <= rows; r++) {
            if (r === rows && rows > 1) {
                sCount += Math.floor(cols / 2); // Sweetbox bằng một nửa số cột
            }
            else if (r <= 3) rCount += cols;
            else vCount += cols;
        }
        if (rows === 1) { rCount = cols; sCount = 0; }
        
        // Tổng số ghế thực tế
        const actualTotal = rCount + vCount + sCount;
        totalSeatsInput.value = actualTotal;

        document.getElementById('calcRegular').textContent = rCount;
        document.getElementById('calcVip').textContent = vCount;
        document.getElementById('calcSweetbox').textContent = sCount;
        document.getElementById('calcTotal').textContent = actualTotal;

        // Render preview
        const preview = document.getElementById('seatPreview');
        preview.innerHTML = '';

        if (rows === 0 || cols === 0) {
            preview.innerHTML = '<p class="text-muted">Nhập số hàng và cột để xem trước</p>';
            return;
        }

        for (let r = 1; r <= rows; r++) {
            const rowName = String.fromCharCode(64 + r);
            let seatType;
            let rowCols = cols;
            
            if (r === rows && rows > 1) {
                seatType = 'sweetbox';
                rowCols = Math.floor(cols / 2);
            }
            else if (r <= 3) seatType = 'regular';
            else seatType = 'vip';

            const rowDiv = document.createElement('div');
            rowDiv.className = 'preview-row';

            const leftLabel = document.createElement('div');
            leftLabel.className = 'preview-row-label';
            leftLabel.textContent = rowName;
            rowDiv.appendChild(leftLabel);

            for (let c = 1; c <= rowCols; c++) {
                const seatDiv = document.createElement('div');
                seatDiv.className = `preview-seat ${seatType}`;
                seatDiv.textContent = `${rowName}${c}`;
                seatDiv.title = `${rowName}${c} - ${seatType.charAt(0).toUpperCase() + seatType.slice(1)}`;
                rowDiv.appendChild(seatDiv);
            }

            const rightLabel = document.createElement('div');
            rightLabel.className = 'preview-row-label';
            rightLabel.textContent = rowName;
            rowDiv.appendChild(rightLabel);

            preview.appendChild(rowDiv);
        }
    }

    rowsInput.addEventListener('input', updatePreview);
    colsInput.addEventListener('input', updatePreview);

    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endsection

@push('scripts')
<script>
    function showCinemaInfo() {
        const select = document.getElementById('cinema_id');
        const selectedOption = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('cinema_info');
        
        if (selectedOption.value) {
            const address = selectedOption.getAttribute('data-address');
            const city = selectedOption.getAttribute('data-city');
            const phone = selectedOption.getAttribute('data-phone');
            
            document.getElementById('c_address').innerText = address || 'N/A';
            document.getElementById('c_city').innerText = city || 'N/A';
            document.getElementById('c_phone').innerText = phone || 'N/A';
            
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }
    
    // Gọi hàm ngay khi load trang để xử lý trường hợp có old('cinema_id')
    document.addEventListener('DOMContentLoaded', function() {
        showCinemaInfo();
    });
</script>
@endpush
