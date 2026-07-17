<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if(Auth::user()->isManager() && Auth::user()->cinema_id)
        <!-- Dashboard Thống Kê Dành Riêng Cho Quản Lý Rạp -->
        <style>
            /* Progress Bar Animation (Cho phần Thống kê) */
            .progress { border-radius: 12px; background-color: #f1f5f9; overflow: visible; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); height: 16px; width: 100%; display: flex; }
            .progress-bar { border-radius: 12px; position: relative; box-shadow: 0 3px 8px rgba(0,0,0,0.15); transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1); height: 100%; display: flex; flex-direction: column; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; }
            .progress-bar::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(255,255,255,0.25) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.25) 50%, rgba(255,255,255,0.25) 75%, transparent 75%, transparent); background-size: 1.5rem 1.5rem; animation: progress-stripes 1s linear infinite; border-radius: 12px; }
            @keyframes progress-stripes { from { background-position: 1.5rem 0; } to { background-position: 0 0; } }
            
            /* Table hover effect (Cho phần Thống kê) */
            .table-hover tbody tr { transition: all 0.25s ease; border-bottom: 1px solid #f1f5f9; }
            .table-hover tbody tr:hover { background-color: #ffffff; transform: scale(1.015); box-shadow: 0 8px 25px rgba(0,0,0,0.06); border-radius: 12px; z-index: 2; position: relative; }
            .table-hover tbody tr:hover td { border-color: transparent; }
            
            /* Badges */
            .badge { padding: 0.55em 0.9em; font-weight: 700; letter-spacing: 0.5px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: inline-block; text-align: center; }
            .bg-primary { background-color: #3b82f6; color: white; }
            .bg-warning { background-color: #f59e0b; color: white; }
            .bg-success { background-color: #10b981; color: white; }
            .bg-danger { background-color: #ef4444; color: white; }
            
            .text-success { color: #10b981; }
            .text-danger { color: #ef4444; }
            .text-warning { color: #f59e0b; }
            .text-info { color: #3b82f6; }
            
            .table-wrapper { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; overflow: hidden; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
            .table th { background-color: #f8fafc; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; padding: 16px; border-bottom: 2px solid #e2e8f0; text-align: left; }
            .table td { padding: 16px; vertical-align: middle; color: #334155; }
        </style>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="text-xl font-bold text-gray-800" style="font-size: 1.25rem; font-weight: 700;">Thống kê lấp đầy phòng chiếu ({{ Auth::user()->cinema->name ?? 'Rạp' }})</h3>
                    <form method="GET" action="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 10px;">
                        <input type="date" name="date" value="{{ $date }}" class="form-control rounded-md border-gray-300 shadow-sm" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 8px;">
                        <button type="submit" style="padding: 0.5rem 1rem; background-color: #3b82f6; color: white; border-radius: 8px; font-weight: bold; border: none; cursor: pointer;">Lọc</button>
                    </form>
                </div>

                <div class="table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Phim</th>
                                    <th>Phòng</th>
                                    <th>Thời Gian</th>
                                    <th>Trạng Thái</th>
                                    <th>Số Ghế (Đặt / Tổng)</th>
                                    <th width="30%">Tỷ Lệ Lấp Đầy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($showtimes as $st)
                                    @php
                                        $booked = $st->getBookedSeatsCount();
                                        $total = $st->room->total_seats ?? 0;
                                        $rate = $st->getOccupancyRate();
                                        
                                        // Màu thanh progress
                                        if($rate >= 80) $progressColor = 'linear-gradient(135deg, #10b981, #059669)'; // Xanh lá
                                        elseif($rate >= 50) $progressColor = 'linear-gradient(135deg, #3b82f6, #2563eb)'; // Xanh dương
                                        elseif($rate >= 20) $progressColor = 'linear-gradient(135deg, #f59e0b, #d97706)'; // Vàng
                                        else $progressColor = 'linear-gradient(135deg, #ef4444, #dc2626)'; // Đỏ

                                        $statusClass = match($st->status) {
                                            'SCHEDULED' => 'bg-primary',
                                            'ONGOING' => 'bg-warning',
                                            'COMPLETED' => 'bg-success',
                                            'CANCELLED' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #1e293b;">{{ $st->movie->title }}</div>
                                        </td>
                                        <td><span style="font-weight: 600;">{{ $st->room->name }}</span></td>
                                        <td>
                                            <div style="font-size: 0.9rem; font-weight: 600; color: #0ea5e9;">{{ $st->start_time->format('H:i') }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $st->start_time->format('d/m/Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ $st->status }}</span>
                                        </td>
                                        <td>
                                            <div style="font-size: 1.1rem; font-weight: 800; display: flex; align-items: baseline; gap: 4px;">
                                                <span class="text-info">{{ $booked }}</span>
                                                <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 500;">/ {{ $total }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                                                <span style="font-size: 0.85rem; font-weight: 700; color: #475569;">Lấp đầy</span>
                                                <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">{{ $rate }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $rate }}%; background: {{ $progressColor }};"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                                            Không có suất chiếu nào trong ngày {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if(isset($showtimes) && $showtimes->hasPages())
                    <div class="mt-4" style="margin-top: 1rem;">
                        {{ $showtimes->appends(['date' => $date])->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Dashboard Thường Cho User/Customer -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
