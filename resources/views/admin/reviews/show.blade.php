@extends('admin.layouts.app')

@section('title', 'Chi Tiết Đánh Giá Phim')
@section('page_title', 'Chi Tiết Đánh Giá')

@section('content')
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Đánh Giá</a></li>
            <li class="breadcrumb-item active">Chi Tiết</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <h3 class="mb-0">Chi Tiết Đánh Giá</h3>
            <div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Quay lại</a>
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Người gửi</h5>
                        <p><strong>{{ $review->user->name }}</strong> &lt;{{ $review->user->email }}&gt;</p>

                        <h5 class="card-title mt-3">Phim</h5>
                        <p>{{ $review->movie->title ?? 'N/A' }}</p>

                        <h5 class="card-title mt-3">Đánh giá</h5>
                        <p>
                            @for($i=1;$i<=5;$i++)
                                <i class="fas fa-star text-warning {{ $i <= $review->rating ? '' : 'text-muted' }}"></i>
                            @endfor
                        </p>

                        <h5 class="card-title mt-3">Bình luận</h5>
                        <p>{{ $review->comment }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin</h5>
                        <p><strong>Trạng thái:</strong>
                            @if($review->status === 'ACTIVE')
                                <span class="badge bg-success">ACTIVE</span>
                            @else
                                <span class="badge bg-secondary">HIDDEN</span>
                            @endif
                        </p>
                        <p><strong>Ngày gửi:</strong> {{ $review->created_at->format('d/m/Y H:i') }}</p>
                        

                        <form action="{{ route('admin.reviews.toggle-status', $review->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-{{ $review->status === 'ACTIVE' ? 'warning' : 'success' }} w-100 mb-2">{{ $review->status === 'ACTIVE' ? 'Ẩn' : 'Hiện' }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
