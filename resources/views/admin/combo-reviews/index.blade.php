@extends('admin.layouts.app')

@section('title', 'Đánh giá Combo')
@section('page_title', 'Thống kê Đánh giá Combo')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Thống kê Đánh giá theo Combo</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th width="100">Hình ảnh</th>
                        <th>Tên Combo</th>
                        <th width="150" class="text-center">Điểm trung bình</th>
                        <th width="150" class="text-center">Lượt đánh giá</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combos as $combo)
                    <tr>
                        <td>{{ $combo->id }}</td>
                        <td>
                            @if($combo->image)
                                <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $combo->name }}</strong>
                        </td>
                        <td class="text-center">
                            @if($combo->total_reviews > 0)
                                <div class="text-warning fw-bold fs-5">
                                    {{ number_format($combo->average_rating, 1) }} <i class="fas fa-star text-sm"></i>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark fs-6">{{ $combo->total_reviews }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.combo-reviews.show', $combo) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu đánh giá Combo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $combos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
