@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Phim')
@section('page_title', 'Chỉnh sửa Phim: ' . $movie->title)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thông tin Phim</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tên phim <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $movie->title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục phim</label>
                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                            @php
                                $movieCategories = $movie->categories->pluck('id')->toArray();
                            @endphp
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat_{{ $category->id }}"
                                    {{ (is_array(old('categories')) && in_array($category->id, old('categories'))) || (!old('categories') && in_array($category->id, $movieCategories)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_{{ $category->id }}">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $movie->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="poster" class="form-label">Poster Phim (Upload mới nếu muốn đổi)</label>
                        <input class="form-control @error('poster') is-invalid @enderror" type="file" id="poster" name="poster" accept="image/*" onchange="previewImage(this)">
                        @error('poster') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        
                        <div class="mt-2 text-center">
                            @if($movie->poster_url)
                                <img id="poster_preview" src="{{ Str::startsWith($movie->poster_url, ['http://', 'https://']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="Preview" style="max-width: 100%; height: auto; border-radius: 8px;" class="img-thumbnail">
                            @else
                                <img id="poster_preview" src="#" alt="Preview" style="max-width: 100%; height: auto; display: none; border-radius: 8px;" class="img-thumbnail">
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="COMING_SOON" {{ old('status', $movie->status) == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                            <option value="NOW_SHOWING" {{ old('status', $movie->status) == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                            <option value="ENDED" {{ old('status', $movie->status) == 'ENDED' ? 'selected' : '' }}>Ngưng chiếu</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', $movie->duration) }}" min="1" required>
                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="age_rating" class="form-label">Độ tuổi</label>
                        <input type="text" class="form-control @error('age_rating') is-invalid @enderror" id="age_rating" name="age_rating" value="{{ old('age_rating', $movie->age_rating) }}" placeholder="VD: T18, P, K">
                        @error('age_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="director" class="form-label">Đạo diễn</label>
                        <input type="text" class="form-control @error('director') is-invalid @enderror" id="director" name="director" value="{{ old('director', $movie->director) }}">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="cast" class="form-label">Diễn viên</label>
                        <input type="text" class="form-control @error('cast') is-invalid @enderror" id="cast" name="cast" value="{{ old('cast', $movie->cast) }}" placeholder="Cách nhau bằng dấu phẩy">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="language" class="form-label">Ngôn ngữ</label>
                        <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language', $movie->language) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="country" class="form-label">Quốc gia</label>
                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $movie->country) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="trailer_url" class="form-label">Trailer URL</label>
                        <input type="url" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" placeholder="https://youtube.com/...">
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
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
                document.getElementById('poster_preview').style.display = 'inline-block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
