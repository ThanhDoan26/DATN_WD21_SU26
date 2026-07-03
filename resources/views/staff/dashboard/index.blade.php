@extends('layouts.staff')

@section('title', 'Staff Dashboard')
@section('page_title', 'Tổng quan (Staff)')

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
    
    .metric-icon.checked { background: #dcfce7; color: #16a34a; }
    .metric-icon.unused { background: #fef3c7; color: #d97706; }
    .metric-icon.used { background: #e0f2fe; color: #0284c7; }
    
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
        <!-- Vé đã check-in hôm nay -->
        <div class="col-md-4 col-sm-12">
            <div class="metric-card">
                <div class="metric-icon checked">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Vé check-in hôm nay</p>
                </div>
            </div>
        </div>

        <!-- Vé chưa sử dụng -->
        <div class="col-md-4 col-sm-12">
            <div class="metric-card">
                <div class="metric-icon unused">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Vé chưa sử dụng</p>
                </div>
            </div>
        </div>

        <!-- Vé đã sử dụng -->
        <div class="col-md-4 col-sm-12">
            <div class="metric-card">
                <div class="metric-icon used">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="metric-info">
                    <h3>...</h3>
                    <p>Vé đã sử dụng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Placeholder -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-qrcode text-warning me-2"></i> Quét mã nhanh</h5>
                </div>
                <div class="card-body text-center py-5 bg-light">
                    <div style="width: 200px; height: 200px; border: 3px dashed #cbd5e1; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 20px;">
                        <i class="fas fa-camera text-secondary" style="font-size: 40px;"></i>
                    </div>
                    <h5 class="mt-4 text-dark">Khu vực Camera (Coming Soon)</h5>
                    <p class="text-muted">Chức năng quét vé tự động sẽ sớm được cập nhật tại đây.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
