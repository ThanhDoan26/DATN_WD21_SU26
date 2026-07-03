@extends('layouts.staff')

@section('title', 'Staff Dashboard')
@section('page_title', 'Tổng quan')

@section('extra_css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; }

    /* ── KPI Cards ─────────────────────────────────── */
    .kpi-card {
        border: none;
        border-radius: 16px;
        padding: 22px 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: default;
    }
    .kpi-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.15) !important;
    }
    .kpi-card .kpi-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        background: rgba(255,255,255,0.25);
        color: #fff;
        margin-bottom: 14px;
    }
    .kpi-card .kpi-value {
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin-bottom: 4px;
    }
    .kpi-card .kpi-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: rgba(255,255,255,0.85);
        letter-spacing: 0.02em;
    }
    .kpi-card .kpi-decor {
        position: absolute;
        right: -20px; bottom: -20px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
    }
    .kpi-card .kpi-decor2 {
        position: absolute;
        right: 20px; bottom: -40px;
        width: 70px; height: 70px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .kpi-green  { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 25px rgba(16,185,129,0.35); }
    .kpi-amber  { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 8px 25px rgba(245,158,11,0.35); }
    .kpi-blue   { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 8px 25px rgba(59,130,246,0.35); }
    .kpi-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 8px 25px rgba(139,92,246,0.35); }
    .kpi-teal   { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 8px 25px rgba(6,182,212,0.35); }

    /* ── Chart Card ─────────────────────────────────── */
    .chart-card {
        background: #fff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        padding: 20px 22px;
    }
    .chart-card .chart-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .chart-card .chart-sub {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-bottom: 16px;
    }

    /* ── Showtime Table ─────────────────────────────── */
    .showtime-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 11px 14px;
        border-radius: 10px;
        transition: background 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }
    .showtime-item:last-child { border-bottom: none; }
    .showtime-item:hover { background: #f8fafc; }
    .showtime-time {
        min-width: 52px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        text-align: center;
        background: #f1f5f9;
        border-radius: 8px;
        padding: 4px 6px;
    }
    .showtime-movie {
        flex: 1;
        font-size: 0.87rem;
        font-weight: 600;
        color: #1e293b;
    }
    .showtime-room {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .st-badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .st-badge.scheduled { background: #dbeafe; color: #1d4ed8; }
    .st-badge.ongoing   { background: #dcfce7; color: #15803d; }
    .st-badge.completed { background: #f1f5f9; color: #64748b; }
    .st-badge.cancelled { background: #fee2e2; color: #dc2626; }

    /* ── Activity Feed ───────────────────────────────── */
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .activity-info { flex: 1; min-width: 0; }
    .activity-name { font-size: 0.85rem; font-weight: 600; color: #1e293b; }
    .activity-detail { font-size: 0.75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .activity-time { font-size: 0.72rem; color: #94a3b8; white-space: nowrap; margin-top: 2px; }

    /* ── Quick Actions ───────────────────────────────── */
    .qa-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 22px 10px;
        border-radius: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .qa-btn i { font-size: 1.8rem; }
    .qa-btn:hover { transform: translateY(-4px); }
    .qa-primary {
        background: linear-gradient(135deg, #a16207, #ca8a04);
        color: #fff;
        box-shadow: 0 6px 20px rgba(161,98,7,0.35);
    }
    .qa-primary:hover { color: #fff; box-shadow: 0 10px 30px rgba(161,98,7,0.45); }
    .qa-secondary {
        background: #fff;
        color: #475569;
        box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        border-color: #e2e8f0;
    }
    .qa-secondary:hover { color: #1e293b; box-shadow: 0 8px 25px rgba(0,0,0,0.12); border-color: #ca8a04; }

    /* ── Expiring Warning ────────────────────────────── */
    .expiring-badge {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 1px solid #fbbf24;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.8rem;
    }

    /* ── Section Header ──────────────────────────────── */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* ── Empty State ─────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 30px 20px;
        color: #94a3b8;
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 10px; display: block; }
    .empty-state p { font-size: 0.85rem; margin: 0; }

    /* ── Counter Animation ───────────────────────────── */
    @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .kpi-value { animation: countUp 0.6s ease forwards; }
    .kpi-card:nth-child(1) .kpi-value { animation-delay: 0.0s; }
    .kpi-card:nth-child(2) .kpi-value { animation-delay: 0.1s; }
    .kpi-card:nth-child(3) .kpi-value { animation-delay: 0.2s; }
    .kpi-card:nth-child(4) .kpi-value { animation-delay: 0.3s; }
    .kpi-card:nth-child(5) .kpi-value { animation-delay: 0.4s; }

    /* ── Responsive ──────────────────────────────────── */
    @media (max-width: 768px) {
        .kpi-value { font-size: 1.6rem; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 1: KPI Cards                         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Check-in hôm nay --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-green">
                <div class="kpi-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="kpi-value" data-target="{{ $checkedInToday }}">{{ number_format($checkedInToday) }}</div>
                <div class="kpi-label">Check-in hôm nay</div>
                <div class="kpi-decor"></div><div class="kpi-decor2"></div>
            </div>
        </div>

        {{-- Vé sẵn sàng --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-amber">
                <div class="kpi-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="kpi-value">{{ number_format($unusedTickets) }}</div>
                <div class="kpi-label">Vé sẵn sàng check-in</div>
                <div class="kpi-decor"></div><div class="kpi-decor2"></div>
            </div>
        </div>

        {{-- Booking hôm nay --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="kpi-value">{{ number_format($bookingsToday) }}</div>
                <div class="kpi-label">Booking hôm nay</div>
                <div class="kpi-decor"></div><div class="kpi-decor2"></div>
            </div>
        </div>

        {{-- Doanh thu hôm nay --}}
        <div class="col-xl col-md-6 col-sm-6">
            <div class="kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="kpi-value">{{ $revenueToday >= 1000000 ? number_format($revenueToday/1000000, 1).'M' : number_format($revenueToday/1000, 0).'K' }}</div>
                <div class="kpi-label">Doanh thu hôm nay (VNĐ)</div>
                <div class="kpi-decor"></div><div class="kpi-decor2"></div>
            </div>
        </div>

        {{-- Tỷ lệ check-in --}}
        <div class="col-xl col-md-6 col-sm-12">
            <div class="kpi-card kpi-teal">
                <div class="kpi-icon"><i class="fas fa-percentage"></i></div>
                <div class="kpi-value">{{ $checkinRate }}%</div>
                <div class="kpi-label">Tỷ lệ check-in hôm nay</div>
                <div class="kpi-decor"></div><div class="kpi-decor2"></div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 2: Charts                            --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Biểu đồ doanh thu 7 ngày --}}
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-title"><i class="fas fa-chart-area text-warning me-2"></i>Doanh thu 7 ngày gần nhất</div>
                <div class="chart-sub">Tổng doanh thu theo ngày (VNĐ)</div>
                <canvas id="revenueChart" height="90"></canvas>
            </div>
        </div>

        {{-- Tỷ lệ check-in donut --}}
        <div class="col-lg-4">
            <div class="chart-card h-100 d-flex flex-column">
                <div class="chart-title"><i class="fas fa-circle-notch text-success me-2"></i>Check-in hôm nay</div>
                <div class="chart-sub">Tỷ lệ đã / chưa check-in</div>
                <div class="d-flex align-items-center justify-content-center flex-grow-1">
                    <canvas id="checkinChart" style="max-height: 180px;"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <div class="text-center">
                        <div style="font-size:1.3rem; font-weight:800; color:#10b981;">{{ $checkedInToday }}</div>
                        <div style="font-size:0.72rem; color:#94a3b8;">Đã check-in</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.3rem; font-weight:800; color:#f59e0b;">{{ $unusedTickets }}</div>
                        <div style="font-size:0.72rem; color:#94a3b8;">Chưa check-in</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 3: Showtimes + Quick Actions         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- Suất chiếu hôm nay --}}
        <div class="col-lg-7">
            <div class="chart-card h-100">
                <div class="section-header">
                    <span class="section-title"><i class="fas fa-film text-warning me-2"></i>Suất chiếu hôm nay</span>
                    <span class="badge" style="background:#fef3c7; color:#92400e; font-size:0.72rem;">{{ today()->format('d/m/Y') }}</span>
                </div>

                @if($todayShowtimes->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>Không có suất chiếu nào hôm nay</p>
                    </div>
                @else
                    <div style="max-height: 300px; overflow-y: auto;">
                        @foreach($todayShowtimes as $show)
                        <div class="showtime-item">
                            <div class="showtime-time">{{ $show->start_time->format('H:i') }}</div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="showtime-movie">{{ $show->movie->title ?? 'N/A' }}</div>
                                <div class="showtime-room">
                                    <i class="fas fa-door-open fa-xs me-1"></i>{{ $show->room->name ?? 'N/A' }}
                                    @if($show->end_time)
                                        &nbsp;·&nbsp; Kết thúc {{ $show->end_time->format('H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div>
                                @php
                                    $stMap = [
                                        'SCHEDULED' => ['scheduled', 'Sắp chiếu'],
                                        'ONGOING'   => ['ongoing',   'Đang chiếu'],
                                        'COMPLETED' => ['completed', 'Kết thúc'],
                                        'CANCELLED' => ['cancelled', 'Đã hủy'],
                                    ];
                                    [$cls, $lbl] = $stMap[$show->status] ?? ['scheduled', $show->status];
                                @endphp
                                <span class="st-badge {{ $cls }}">{{ $lbl }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions + Expiring --}}
        <div class="col-lg-5 d-flex flex-column gap-3">
            {{-- Quick Actions --}}
            <div class="chart-card">
                <div class="section-title mb-3"><i class="fas fa-bolt text-warning me-2"></i>Thao tác nhanh</div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('staff.ticket.search') }}" id="qa-search" class="qa-btn qa-primary w-100">
                            <i class="fas fa-search"></i>
                            <span>Tra cứu vé</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('staff.ticket.search', ['scan' => 1]) }}" id="qa-qr" class="qa-btn qa-primary w-100">
                            <i class="fas fa-qrcode"></i>
                            <span>Quét QR</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Vé sắp hết hạn --}}
            <div class="chart-card flex-grow-1">
                <div class="section-header">
                    <span class="section-title"><i class="fas fa-clock text-danger me-2"></i>Vé sắp hết hạn</span>
                    <span style="font-size:0.72rem; color:#94a3b8;">Trong 2 giờ tới</span>
                </div>
                @if($expiringSoon->isEmpty())
                    <div class="empty-state" style="padding: 20px;">
                        <i class="fas fa-check-circle" style="color:#10b981; font-size:1.8rem;"></i>
                        <p style="margin-top:8px;">Không có vé sắp hết hạn</p>
                    </div>
                @else
                    @foreach($expiringSoon as $es)
                    <div class="expiring-badge mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-weight:600; font-size:0.82rem; color:#92400e;">
                                {{ $es->booking->showtime->movie->title ?? 'N/A' }}
                            </div>
                            <div style="font-size:0.72rem; color:#a16207;">
                                <i class="fas fa-clock fa-xs me-1"></i>
                                {{ $es->booking->showtime->start_time->format('H:i') }}
                                · {{ $es->booking->showtime->room->name ?? '' }}
                            </div>
                        </div>
                        <a href="{{ route('staff.ticket.search', ['code' => $es->qr_code]) }}" class="btn btn-sm" style="background:#f59e0b; color:#fff; font-size:0.7rem; padding:3px 8px; border-radius:6px;">
                            Check-in
                        </a>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- ROW 4: Recent Check-in Activity          --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="chart-card">
                <div class="section-header">
                    <span class="section-title"><i class="fas fa-history text-warning me-2"></i>Hoạt động check-in gần đây</span>
                    <a href="{{ route('staff.ticket.search') }}" style="font-size:0.8rem; color:#ca8a04; text-decoration:none; font-weight:600;">
                        Tra cứu thêm <i class="fas fa-arrow-right fa-xs"></i>
                    </a>
                </div>

                @if($recentCheckIns->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có hoạt động check-in nào</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($recentCheckIns as $ci)
                        @php
                            $customerName = $ci->booking->user->name ?? 'Khách vãng lai';
                            $initials = strtoupper(substr($customerName, 0, 1));
                            $movieTitle = $ci->booking->showtime->movie->title ?? 'N/A';
                            $seatCode = $ci->seat ? ($ci->seat->row_name . $ci->seat->seat_number) : 'N/A';
                            $checkinTime = $ci->checked_in_at ? $ci->checked_in_at->diffForHumans() : '';
                        @endphp
                        <div class="col-lg-6">
                            <div class="activity-item">
                                <div class="activity-avatar">{{ $initials }}</div>
                                <div class="activity-info">
                                    <div class="activity-name">{{ $customerName }}</div>
                                    <div class="activity-detail" title="{{ $movieTitle }} · Ghế {{ $seatCode }}">
                                        <i class="fas fa-film fa-xs me-1"></i>{{ $movieTitle }}
                                        &nbsp;·&nbsp;
                                        <i class="fas fa-chair fa-xs me-1"></i>Ghế {{ $seatCode }}
                                    </div>
                                    <div class="activity-time"><i class="fas fa-clock fa-xs me-1"></i>{{ $checkinTime }}</div>
                                </div>
                                <div>
                                    <span style="font-size:0.68rem; font-weight:700; background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:50px;">✓ OK</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Revenue Chart ──────────────────────────────────────────
    const revenueLabels  = @json(array_column($revenueChart, 'date'));
    const revenueData    = @json(array_column($revenueChart, 'revenue'));

    const ctxRevenue = document.getElementById('revenueChart')?.getContext('2d');
    if (ctxRevenue) {
        const gradient = ctxRevenue.createLinearGradient(0, 0, 0, 250);
        gradient.addColorStop(0, 'rgba(202,138,4,0.3)');
        gradient.addColorStop(1, 'rgba(202,138,4,0.01)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenueData,
                    borderColor: '#ca8a04',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ca8a04',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed.y) + ' đ'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 11 },
                            callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K'
                        }
                    }
                }
            }
        });
    }

    // ── Check-in Donut ─────────────────────────────────────────
    const checkedIn  = {{ $checkedInToday }};
    const notChecked = {{ $unusedTickets }};
    const ctxDonut = document.getElementById('checkinChart')?.getContext('2d');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Đã check-in', 'Chưa check-in'],
                datasets: [{
                    data: [checkedIn, notChecked],
                    backgroundColor: ['#10b981', '#f59e0b'],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed + ' vé' } }
                }
            }
        });
    }

    // ── QR Hover effect ────────────────────────────────────────
    document.querySelectorAll('.qa-btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => btn.style.filter = 'brightness(1.08)');
        btn.addEventListener('mouseleave', () => btn.style.filter = '');
    });

});
</script>
@endsection
