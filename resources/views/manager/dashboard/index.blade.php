@extends('layouts.manager')

@section('title', 'Manager Dashboard')
@section('page_title', 'Tổng quan (Manager)')

@section('extra_css')
<style>
    /* Styling for metric cards */
    .metric-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        transition: transform 0.3s ease;
    }
    .metric-card:hover {
        transform: translateY(-5px);
    }
    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-right: 20px;
    }
    .metric-icon.rooms { background: #e0f2fe; color: #0284c7; }
    .metric-icon.shows { background: #fef3c7; color: #d97706; }
    .metric-icon.seats { background: #dcfce7; color: #16a34a; }
    .metric-icon.revenue { background: #fee2e2; color: #dc2626; }
    
    .metric-info h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }
    .metric-info p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Cặp Card Placeholder -->
    <div class="row">
        <!-- Tổng số phòng -->
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon rooms">
                    <i class="fas fa-door-closed"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Tổng số phòng</p>
                </div>
            </div>
        </div>

        <!-- Tổng số suất chiếu hôm nay -->
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon shows">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Suất chiếu hôm nay</p>
                </div>
            </div>
        </div>

        <!-- Tổng số ghế hoạt động -->
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon seats">
                    <i class="fas fa-chair"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Số ghế hoạt động</p>
                </div>
            </div>
        </div>

        <!-- Doanh thu hôm nay -->
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon revenue">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Doanh thu hôm nay</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i> Thông báo nội bộ</h5>
                </div>
                <div class="card-body text-center py-5">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" width="150" alt="empty" class="mb-3 opacity-50">
                    <h5 class="text-muted">Chưa có chức năng ở đây</h5>
                    <p class="text-muted mb-0">Các tính năng báo cáo hoặc biểu đồ sẽ được phát triển sau này.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
