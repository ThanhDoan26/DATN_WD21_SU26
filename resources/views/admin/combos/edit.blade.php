@extends('admin.layouts.app')

@section('title', 'Cập nhật Combo')
@section('page_title', 'Cập Nhật Combo')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Thông tin Combo</h5>
        <a href="{{ route('admin.combos.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.combos.update', $combo) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên Combo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $combo->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', (int)$combo->price) }}" min="0" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $combo->description) }}</textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ACTIVE" {{ old('status', $combo->status) == 'ACTIVE' ? 'selected' : '' }}>Đang bán</option>
                            <option value="INACTIVE" {{ old('status', $combo->status) == 'INACTIVE' ? 'selected' : '' }}>Ngừng bán</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Hình ảnh</label>
                        @if($combo->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $combo->image) }}" alt="{{ $combo->name }}" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="mt-2 text-muted small">Định dạng hỗ trợ: JPG, PNG, GIF. Kích thước tối đa 2MB. Bỏ trống nếu không muốn đổi ảnh.</div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
