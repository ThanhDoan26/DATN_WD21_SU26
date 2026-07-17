@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa bài viết - Admin')
@section('page_title', 'Chỉnh Sửa Bài Viết')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-custom">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Bài viết</a></li>
            <li class="breadcrumb-item active">Chỉnh sửa</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Form Column (Left) -->
        <div class="col-lg-8">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-1"></i> Nội Dung Bài Viết</h5>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Summary -->
                    <div class="mb-3">
                        <label for="summary" class="form-label fw-bold">Mô tả ngắn <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="3" maxlength="500" required>{{ old('summary', $post->summary) }}</textarea>
                        <div class="form-text text-end" id="summaryHelp">{{ strlen($post->summary) }}/500 ký tự</div>
                        @error('summary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Content (CKEditor) -->
                    <div class="mb-3">
                        <label for="editor" class="form-label fw-bold">Nội dung chi tiết <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="editor" name="content" rows="15">{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SEO Settings Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-search me-1"></i> Cấu Hình SEO (Tùy Chọn)</h5>
                </div>
                <div class="card-body">
                    <!-- SEO Title -->
                    <div class="mb-3">
                        <label for="seo_title" class="form-label fw-bold">SEO Title</label>
                        <input type="text" class="form-control @error('seo_title') is-invalid @enderror" id="seo_title" name="seo_title" value="{{ old('seo_title', $post->seo_title) }}" placeholder="Tiêu đề hiển thị trên Google..." maxlength="255">
                        @error('seo_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SEO Keywords -->
                    <div class="mb-3">
                        <label for="seo_keywords" class="form-label fw-bold">SEO Keywords</label>
                        <input type="text" class="form-control @error('seo_keywords') is-invalid @enderror" id="seo_keywords" name="seo_keywords" value="{{ old('seo_keywords', $post->seo_keywords) }}" placeholder="Từ khóa ngăn cách bằng dấu phẩy..." maxlength="255">
                        @error('seo_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SEO Description -->
                    <div class="mb-3">
                        <label for="seo_description" class="form-label fw-bold">SEO Description</label>
                        <textarea class="form-control @error('seo_description') is-invalid @enderror" id="seo_description" name="seo_description" rows="3" placeholder="Mô tả tóm tắt..." maxlength="500">{{ old('seo_description', $post->seo_description) }}</textarea>
                        @error('seo_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options Column (Right) -->
        <div class="col-lg-4">
            <!-- Publishing Meta Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-1"></i> Xuất Bản & Phân Loại</h5>
                </div>
                <div class="card-body">
                    <!-- Category -->
                    <div class="mb-3">
                        <label for="post_category_id" class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select @error('post_category_id') is-invalid @enderror" id="post_category_id" name="post_category_id" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('post_category_id', $post->post_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('post_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="Draft" {{ old('status', $post->status) === 'Draft' ? 'selected' : '' }}>Bản nháp (Draft)</option>
                            <option value="Published" {{ old('status', $post->status) === 'Published' ? 'selected' : '' }}>Đã xuất bản (Published)</option>
                            <option value="Hidden" {{ old('status', $post->status) === 'Hidden' ? 'selected' : '' }}>Đang ẩn (Hidden)</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Published At -->
                    <div class="mb-3">
                        <label for="published_at" class="form-label fw-bold">Ngày đăng tin</label>
                        <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" id="published_at" name="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                        <div class="form-text">Bỏ trống để sử dụng thời điểm hiện tại khi xuất bản.</div>
                        @error('published_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Featured checkbox -->
                    <div class="mb-3 form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_featured">Đánh dấu Tin nổi bật (Featured)</label>
                    </div>
                </div>
            </div>

            <!-- Media Upload Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-image me-1"></i> Tải Lên Hình Ảnh</h5>
                </div>
                <div class="card-body">
                    <!-- Image / Thumbnail -->
                    <div class="mb-3">
                        <label for="image" class="form-label fw-bold">Ảnh đại diện</label>
                        @if($post->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $post->image) }}" alt="Thumbnail Preview" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        @endif
                        <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*">
                        <div class="form-text">Chọn tập tin mới nếu bạn muốn thay đổi ảnh đại diện. Dung lượng tối đa: 2MB.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Banner -->
                    <div class="mb-3">
                        <label for="banner" class="form-label fw-bold">Banner bài viết (Nếu có)</label>
                        @if($post->banner)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $post->banner) }}" alt="Banner Preview" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        @endif
                        <input class="form-control @error('banner') is-invalid @enderror" type="file" id="banner" name="banner" accept="image/*">
                        <div class="form-text">Chọn tập tin mới nếu bạn muốn thay đổi banner. Dung lượng tối đa: 2MB.</div>
                        @error('banner')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-body p-0 d-flex gap-2">
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary w-50 py-3 fw-bold"><i class="fas fa-times me-1"></i> Hủy</a>
                    <button type="submit" class="btn btn-success w-50 py-3 fw-bold"><i class="fas fa-save me-1"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>
</form>

@section('extra_js')
<!-- Rich Text Editor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize CKEditor 5
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
            })
            .catch(error => {
                console.error(error);
            });

        // Summary char count
        const summaryArea = document.getElementById('summary');
        const summaryHelp = document.getElementById('summaryHelp');
        if (summaryArea && summaryHelp) {
            summaryArea.addEventListener('input', function() {
                const remains = 500 - this.value.length;
                summaryHelp.textContent = `${this.value.length}/500 ký tự (còn lại ${remains})`;
                if (remains < 50) {
                    summaryHelp.classList.add('text-danger');
                } else {
                    summaryHelp.classList.remove('text-danger');
                }
            });
        }
    });
</script>
@endsection
@endsection
