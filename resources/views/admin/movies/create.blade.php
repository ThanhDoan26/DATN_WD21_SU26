@extends('admin.layouts.app')

@section('title', 'Thêm mới Phim')
@section('page_title', 'Thêm mới Phim')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thông tin Phim</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tên phim <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục phim</label>
                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat_{{ $category->id }}"
                                    {{ (is_array(old('categories')) && in_array($category->id, old('categories'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_{{ $category->id }}">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Poster Phim</label>
                        <div class="poster-upload-wrapper">
                            <input class="form-control @error('poster') is-invalid @enderror" type="file" id="poster" name="poster" accept="image/*" onchange="previewImage(this)" style="display: none;">
                            <label for="poster" class="poster-upload-area d-flex flex-column align-items-center justify-content-center border border-2 rounded-3 bg-light text-muted position-relative" style="cursor: pointer; min-height: 280px; border-style: dashed !important; transition: all 0.3s ease; overflow: hidden;">
                                <div id="poster_placeholder" class="text-center p-4">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary opacity-75"></i>
                                    <h6 class="mb-1 fw-bold text-dark">Nhấn để chọn ảnh poster</h6>
                                    <small class="text-muted">Định dạng: JPG, PNG, GIF (Tối đa 2MB)</small>
                                </div>
                                <img id="poster_preview" src="#" alt="Preview" class="position-absolute w-100 h-100" style="object-fit: cover; display: none; top: 0; left: 0;">
                                <div id="poster_overlay" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 text-white fw-bold" style="display: none; top: 0; left: 0; opacity: 0; transition: opacity 0.3s;">
                                    <i class="fas fa-sync-alt me-2"></i> Đổi ảnh khác
                                </div>
                            </label>
                            @error('poster') <div class="invalid-feedback d-block mt-2">{{ $message }}</div> @enderror
                        </div>
                        <style>
                            .poster-upload-area:hover {
                                background-color: #f1f3f5 !important;
                                border-color: #0d6efd !important;
                            }
                            .poster-upload-area:hover #poster_overlay {
                                opacity: 1 !important;
                            }
                        </style>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="COMING_SOON" {{ old('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                            <option value="NOW_SHOWING" {{ old('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                            <option value="ENDED" {{ old('status') == 'ENDED' ? 'selected' : '' }}>Ngưng chiếu</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration') }}" min="1" required>
                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="age_rating" class="form-label">Độ tuổi</label>
                        <input type="text" class="form-control @error('age_rating') is-invalid @enderror" id="age_rating" name="age_rating" value="{{ old('age_rating') }}" placeholder="VD: T18, P, K">
                        @error('age_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="director" class="form-label">Đạo diễn</label>
                        <input type="text" class="form-control @error('director') is-invalid @enderror" id="director" name="director" value="{{ old('director') }}">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="cast" class="form-label">Diễn viên</label>
                        <input type="text" class="form-control @error('cast') is-invalid @enderror" id="cast" name="cast" value="{{ old('cast') }}" placeholder="Cách nhau bằng dấu phẩy">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="language" class="form-label">Ngôn ngữ</label>
                        <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="country" class="form-label">Quốc gia</label>
                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="trailer_url" class="form-label">Trailer URL</label>
                        <input type="url" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url') }}" placeholder="https://youtube.com/...">
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu bộ phim</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('poster_preview').src = e.target.result;
                document.getElementById('poster_preview').style.display = 'block';
                document.getElementById('poster_placeholder').style.display = 'none';
                document.getElementById('poster_overlay').style.display = 'flex';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
