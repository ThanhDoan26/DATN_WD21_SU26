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

                    <x-genre-select
                        :categories="$categories"
                        :selected="old('categories', $movie->categories->pluck('id')->toArray())"
                        name="categories[]"
                        label="Loại phim / Thể loại phim"
                        id="edit-genre" />

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $movie->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Poster Phim</label>
                        <div class="poster-upload-wrapper">
                            <input class="form-control @error('poster') is-invalid @enderror" type="file" id="poster" name="poster" accept="image/*" onchange="previewImage(this)" style="display: none;">
                            <label for="poster" class="poster-upload-area d-flex flex-column align-items-center justify-content-center border border-2 rounded-3 bg-light text-muted position-relative" style="cursor: pointer; min-height: 280px; border-style: dashed !important; transition: all 0.3s ease; overflow: hidden;">
                                <div id="poster_placeholder" class="text-center p-4" style="{{ $movie->poster_url ? 'display: none;' : '' }}">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary opacity-75"></i>
                                    <h6 class="mb-1 fw-bold text-dark">Nhấn để chọn ảnh mới</h6>
                                    <small class="text-muted">Định dạng: JPG, PNG, GIF (Tối đa 2MB)</small>
                                </div>
                                
                                @if($movie->poster_url)
                                    <img id="poster_preview" src="{{ Str::startsWith($movie->poster_url, ['http://', 'https://']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="Preview" class="position-absolute w-100 h-100" style="object-fit: cover; top: 0; left: 0;">
                                @else
                                    <img id="poster_preview" src="#" alt="Preview" class="position-absolute w-100 h-100" style="object-fit: cover; display: none; top: 0; left: 0;">
                                @endif
                                
                                <div id="poster_overlay" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 text-white fw-bold" style="{{ $movie->poster_url ? 'display: flex;' : 'display: none;' }} top: 0; left: 0; opacity: 0; transition: opacity 0.3s;">
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
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required onchange="handleStatusChange()">
                            <option value="SCHEDULED" {{ old('status', $movie->status) == 'SCHEDULED' ? 'selected' : '' }}>📅 Lên lịch (Scheduled)</option>
                            <option value="PRE_ORDER" {{ old('status', $movie->status) == 'PRE_ORDER' ? 'selected' : '' }}>🎟️ Mở bán sớm (Pre-order)</option>
                            <option value="COMING_SOON" {{ old('status', $movie->status) == 'COMING_SOON' ? 'selected' : '' }}>⏳ Sắp chiếu (Coming Soon)</option>
                            <option value="NOW_SHOWING" {{ old('status', $movie->status) == 'NOW_SHOWING' ? 'selected' : '' }}>🎬 Đang chiếu (Now Showing)</option>
                            <option value="ENDED" {{ old('status', $movie->status) == 'ENDED' ? 'selected' : '' }}>🛑 Ngưng chiếu (Ended)</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="scheduled-status-hint" class="alert alert-info py-2 px-3 mt-2 small" style="display: none;">
                            <i class="fas fa-info-circle me-1"></i> <strong>Lưu ý khi Lên lịch:</strong> Bắt buộc nhập đầy đủ Tên, Poster, Trailer, Thời lượng, Độ tuổi, Thể loại và <strong>Ngày phát hành dự kiến (trong tương lai)</strong>. Suất chiếu tạo cho phim này sẽ ở trạng thái Chờ duyệt (Pending) và chưa mở bán vé.
                        </div>
                        <div id="ended-status-hint" class="alert alert-warning py-2 px-3 mt-2 small" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>Lưu ý khi Ngừng chiếu:</strong> Hệ thống sẽ tự động hủy (CANCELLED) toàn bộ các suất chiếu sắp tới của phim. Nếu đang có suất chiếu tương lai đã được đặt vé, hệ thống sẽ chặn thao tác này.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="release_date_wrapper">
                            <label for="release_date" class="form-label">
                                Ngày phát hành dự kiến <span id="release_date_req_star" class="text-danger" style="display: none;">*</span>
                            </label>
                            <input type="datetime-local" 
                                   class="form-control @error('release_date') is-invalid @enderror" 
                                   id="release_date" 
                                   name="release_date" 
                                   value="{{ old('release_date', $movie->release_date ? $movie->release_date->format('Y-m-d\TH:i') : '') }}">
                            <small class="text-muted d-block" id="release_date_hint">Bắt buộc khi phim ở trạng thái Lên lịch (phải là ngày tương lai).</small>
                            <div id="release_date_client_error" class="text-danger small mt-1 d-none align-items-center gap-1">
                                <i class="fas fa-circle-exclamation"></i> <span id="release_date_error_msg"></span>
                            </div>
                            @error('release_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="presale_date_wrapper">
                            <label for="presale_date" class="form-label">Ngày mở bán sớm (nếu có)</label>
                            <input type="datetime-local" 
                                   class="form-control @error('presale_date') is-invalid @enderror" 
                                   id="presale_date" 
                                   name="presale_date" 
                                   value="{{ old('presale_date', $movie->presale_date ? $movie->presale_date->format('Y-m-d\TH:i') : '') }}">
                            <small class="text-muted d-block">Tự động chuyển "Mở bán sớm" khi đến ngày này.</small>
                            <div id="presale_date_client_error" class="text-danger small mt-1 d-none align-items-center gap-1">
                                <i class="fas fa-circle-exclamation"></i> <span id="presale_date_error_msg"></span>
                            </div>
                            @error('presale_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', $movie->duration) }}" min="1" required>
                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="age_rating" class="form-label">Độ tuổi <i class="fas fa-info-circle text-muted" title="Hiển thị badge màu trên trang khách"></i></label>
                        <select class="form-select @error('age_rating') is-invalid @enderror" id="age_rating" name="age_rating">
                            <option value="">-- Chọn độ tuổi --</option>
                            <option value="P"   {{ old('age_rating', $movie->age_rating) == 'P'   ? 'selected' : '' }}>🟢 P — Phổ biến (mọi độ tuổi)</option>
                            <option value="K"   {{ old('age_rating', $movie->age_rating) == 'K'   ? 'selected' : '' }}>🟢 K — Dành cho trẻ em</option>
                            <option value="T13" {{ old('age_rating', $movie->age_rating) == 'T13' ? 'selected' : '' }}>🟡 T13 — Từ 13 tuổi trở lên</option>
                            <option value="T16" {{ old('age_rating', $movie->age_rating) == 'T16' ? 'selected' : '' }}>🟠 T16 — Từ 16 tuổi trở lên</option>
                            <option value="T18" {{ old('age_rating', $movie->age_rating) == 'T18' ? 'selected' : '' }}>🔴 T18 — Từ 18 tuổi trở lên</option>
                        </select>
                        <small class="text-muted">Badge màu sẽ hiển thị tự động trên trang phím đang chiếu / sắp chiếu.</small>
                        @error('age_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <x-format-select
                        :formats="$formats"
                        :selected="old('format', $movie->format ?? [])"
                        name="format[]"
                        label="Định dạng phim"
                        placeholder="Chọn định dạng phim…"
                        id="edit-format" />
                    <small class="text-muted d-block mt-1">Chọn định dạng công chiếu của phim (ví dụ: 2D, 3D, IMAX...)</small>
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
                        <label for="trailer_url" class="form-label">
                            <i class="fab fa-youtube text-danger"></i> Trailer URL (YouTube) <span id="trailer_req_star" class="text-danger" style="display: none;">*</span>
                        </label>
                        <div class="input-group">
                            <input type="url" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" placeholder="https://youtube.com/watch?v=..." oninput="previewTrailer(this.value)">
                            <button type="button" class="btn btn-outline-danger" onclick="testTrailer()" title="Xem thử trailer">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                        @error('trailer_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="trailer-preview" class="mt-2" style="display:none;">
                            <img id="trailer-thumb" src="" alt="Trailer thumbnail" class="img-fluid rounded shadow-sm" style="max-height:120px; object-fit:cover; width:100%;">
                            <small class="text-success d-block mt-1"><i class="fas fa-check-circle"></i> Đã nhận diện video YouTube</small>
                        </div>
                        <div id="trailer-error" class="mt-1" style="display:none;">
                            <small class="text-danger"><i class="fas fa-exclamation-circle"></i> URL không hợp lệ hoặc không phải YouTube</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary" id="btn-save-movie">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const preview = document.getElementById('poster_preview');
            const placeholder = document.getElementById('poster_placeholder');
            const overlay = document.getElementById('poster_overlay');

            preview.src = window.URL.createObjectURL(input.files[0]);
            preview.style.setProperty('display', 'block', 'important');
            
            if (placeholder) {
                placeholder.style.setProperty('display', 'none', 'important');
            }
            if (overlay) {
                overlay.style.setProperty('display', 'flex', 'important');
            }
        }
    }

    function extractYoutubeId(url) {
        if (!url) return null;
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/shorts\/([^&\n?#]+)/
        ];
        for (const p of patterns) { const m = url.match(p); if (m) return m[1]; }
        return null;
    }

    function previewTrailer(url) {
        const ytId = extractYoutubeId(url);
        const previewEl = document.getElementById('trailer-preview');
        const errorEl   = document.getElementById('trailer-error');
        const thumbEl   = document.getElementById('trailer-thumb');
        if (ytId) {
            thumbEl.src = `https://img.youtube.com/vi/${ytId}/mqdefault.jpg`;
            previewEl.style.display = 'block';
            errorEl.style.display   = 'none';
        } else if (url.length > 10) {
            previewEl.style.display = 'none';
            errorEl.style.display   = 'block';
        } else {
            previewEl.style.display = 'none';
            errorEl.style.display   = 'none';
        }
    }

    function testTrailer() {
        const url = document.getElementById('trailer_url').value.trim();
        const ytId = extractYoutubeId(url);
        if (ytId) window.open(`https://www.youtube.com/watch?v=${ytId}`, '_blank');
        else if (url) window.open(url, '_blank');
        else alert('Vui lòng nhập URL trailer trước.');
    }

    function validateMovieDatesRealTime() {
        const status = document.getElementById('status').value;
        const releaseInput = document.getElementById('release_date');
        const presaleInput = document.getElementById('presale_date');
        const relErrEl = document.getElementById('release_date_client_error');
        const relErrMsg = document.getElementById('release_date_error_msg');
        const preErrEl = document.getElementById('presale_date_client_error');
        const preErrMsg = document.getElementById('presale_date_error_msg');

        let isValid = true;
        const now = new Date();

        function setReleaseError(msg) {
            releaseInput.classList.add('is-invalid');
            if (relErrEl && relErrMsg) {
                relErrMsg.textContent = msg;
                relErrEl.classList.remove('d-none');
                relErrEl.classList.add('d-flex');
            }
            isValid = false;
        }

        function clearReleaseError() {
            releaseInput.classList.remove('is-invalid');
            if (relErrEl) {
                relErrEl.classList.add('d-none');
                relErrEl.classList.remove('d-flex');
            }
        }

        function setPresaleError(msg) {
            presaleInput.classList.add('is-invalid');
            if (preErrEl && preErrMsg) {
                preErrMsg.textContent = msg;
                preErrEl.classList.remove('d-none');
                preErrEl.classList.add('d-flex');
            }
            isValid = false;
        }

        function clearPresaleError() {
            presaleInput.classList.remove('is-invalid');
            if (preErrEl) {
                preErrEl.classList.add('d-none');
                preErrEl.classList.remove('d-flex');
            }
        }

        clearReleaseError();
        clearPresaleError();

        if (status === 'SCHEDULED') {
            // 1. SCHEDULED: release_date required & > now
            if (releaseInput.value) {
                const relDate = new Date(releaseInput.value);
                if (relDate <= now) {
                    setReleaseError('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                }
            }
            // presale_date: optional. If present: now < presale_date <= release_date
            if (presaleInput.value) {
                const preDate = new Date(presaleInput.value);
                if (preDate <= now) {
                    setPresaleError('Ngày mở bán sớm phải là thời gian trong tương lai.');
                } else if (releaseInput.value && preDate > new Date(releaseInput.value)) {
                    setPresaleError('Ngày mở bán sớm phải trước hoặc bằng Ngày phát hành dự kiến.');
                }
            }
        } else if (status === 'PRE_ORDER') {
            // 2. PRE_ORDER: release_date required & > now
            if (releaseInput.value) {
                const relDate = new Date(releaseInput.value);
                if (relDate <= now) {
                    setReleaseError('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                }
            }
            // presale_date: optional. If present: presale_date <= release_date
            if (presaleInput.value && releaseInput.value) {
                const preDate = new Date(presaleInput.value);
                if (preDate > new Date(releaseInput.value)) {
                    setPresaleError('Ngày mở bán sớm phải trước hoặc bằng Ngày phát hành dự kiến.');
                }
            }
        } else if (status === 'COMING_SOON') {
            // 3. COMING_SOON: release_date optional. If present: release_date > now
            if (releaseInput.value) {
                const relDate = new Date(releaseInput.value);
                if (relDate <= now) {
                    setReleaseError('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                }
            }
            // presale_date: optional. If present: presale_date <= release_date
            if (presaleInput.value && releaseInput.value) {
                const preDate = new Date(presaleInput.value);
                if (preDate > new Date(releaseInput.value)) {
                    setPresaleError('Ngày mở bán sớm phải trước hoặc bằng Ngày phát hành dự kiến.');
                }
            }
        } else if (status === 'NOW_SHOWING') {
            // 4. NOW_SHOWING: release_date optional, allow past dates. presale_date ignored.
            clearReleaseError();
            clearPresaleError();
        } else if (status === 'ENDED') {
            // 5. ENDED: ignore validations
            clearReleaseError();
            clearPresaleError();
        }

        return isValid;
    }

    function handleStatusChange() {
        const status = document.getElementById('status').value;
        const hintEl = document.getElementById('scheduled-status-hint');
        const releaseStar = document.getElementById('release_date_req_star');
        const trailerStar = document.getElementById('trailer_req_star');
        const releaseInput = document.getElementById('release_date');
        const presaleInput = document.getElementById('presale_date');
        const releaseWrapper = document.getElementById('release_date_wrapper');
        const presaleWrapper = document.getElementById('presale_date_wrapper');
        const releaseHint = document.getElementById('release_date_hint');

        const nowIso = new Date().toISOString().slice(0, 16);

        // 1. Show/Hide & toggle width of presale_date vs release_date
        if (status === 'NOW_SHOWING' || status === 'ENDED') {
            // Hide presale_date completely & Auto-reset value
            if (presaleWrapper) presaleWrapper.style.display = 'none';
            if (presaleInput) {
                presaleInput.value = ''; // Auto-reset value to avoid sending invalid background data
                presaleInput.disabled = true;
            }
            if (releaseWrapper) {
                releaseWrapper.className = 'col-md-12 mb-3';
            }
            if (releaseStar) releaseStar.style.display = 'none';
            if (releaseHint) {
                releaseHint.textContent = status === 'NOW_SHOWING' 
                    ? 'Tùy chọn: Ngày phim đã công chiếu (cho phép chọn ngày trong quá khứ).' 
                    : 'Tùy chọn: Ngày phim từng phát hành.';
            }
            releaseInput.removeAttribute('min');
            presaleInput.removeAttribute('min');
        } else if (status === 'SCHEDULED' || status === 'PRE_ORDER') {
            // Show both fields
            if (presaleWrapper) presaleWrapper.style.display = 'block';
            if (presaleInput) presaleInput.disabled = false;
            if (releaseWrapper) {
                releaseWrapper.className = 'col-md-6 mb-3';
            }
            if (releaseStar) releaseStar.style.display = 'inline';
            if (releaseHint) {
                releaseHint.textContent = status === 'SCHEDULED'
                    ? 'Bắt buộc khi phim ở trạng thái Lên lịch (phải là ngày tương lai).'
                    : 'Bắt buộc khi phim ở trạng thái Mở bán sớm (phải là ngày tương lai).';
            }
            releaseInput.min = nowIso;
            if (status === 'SCHEDULED') {
                presaleInput.min = nowIso;
            } else {
                presaleInput.removeAttribute('min');
            }
        } else if (status === 'COMING_SOON') {
            // COMING_SOON: Show both fields, keep optional (no red *)
            if (presaleWrapper) presaleWrapper.style.display = 'block';
            if (presaleInput) presaleInput.disabled = false;
            if (releaseWrapper) {
                releaseWrapper.className = 'col-md-6 mb-3';
            }
            if (releaseStar) releaseStar.style.display = 'none';
            if (releaseHint) {
                releaseHint.textContent = 'Tùy chọn: Ngày dự kiến khởi chiếu để hiển thị cho khán giả (phải là ngày tương lai nếu nhập).';
            }
            releaseInput.min = nowIso;
            presaleInput.removeAttribute('min');
        }

        // Toggle scheduled banner & trailer star
        const endedHintEl = document.getElementById('ended-status-hint');
        if (status === 'SCHEDULED') {
            if (hintEl) hintEl.style.display = 'block';
            if (trailerStar) trailerStar.style.display = 'inline';
        } else {
            if (hintEl) hintEl.style.display = 'none';
            if (trailerStar) trailerStar.style.display = 'none';
        }

        if (status === 'ENDED') {
            if (endedHintEl) endedHintEl.style.display = 'block';
        } else {
            if (endedHintEl) endedHintEl.style.display = 'none';
        }

        validateMovieDatesRealTime();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const url = document.getElementById('trailer_url').value;
        if (url) previewTrailer(url);
        handleStatusChange();

        const releaseInput = document.getElementById('release_date');
        const presaleInput = document.getElementById('presale_date');
        const statusSelect = document.getElementById('status');

        ['input', 'change'].forEach(evt => {
            releaseInput.addEventListener(evt, validateMovieDatesRealTime);
            presaleInput.addEventListener(evt, validateMovieDatesRealTime);
        });
        statusSelect.addEventListener('change', handleStatusChange);

        const form = document.querySelector('form[action="{{ route('admin.movies.update', $movie) }}"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const status = document.getElementById('status').value;
                const releaseDate = document.getElementById('release_date').value;
                const presaleDate = document.getElementById('presale_date').value;
                const errors = [];
                const now = new Date();

                if (status === 'SCHEDULED') {
                    const title = document.getElementById('title').value.trim();
                    const poster = document.getElementById('poster');
                    const hasExistingPoster = {{ $movie->poster_url ? 'true' : 'false' }};
                    const trailerUrl = document.getElementById('trailer_url').value.trim();
                    const duration = parseInt(document.getElementById('duration').value);
                    const ageRating = document.getElementById('age_rating').value;
                    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:checked');

                    if (!title) errors.push('Tên phim là bắt buộc khi Lên lịch.');
                    if (!hasExistingPoster && (!poster || !poster.files || poster.files.length === 0)) {
                        errors.push('Poster phim là bắt buộc khi Lên lịch.');
                    }
                    if (!trailerUrl) errors.push('Trailer URL là bắt buộc khi Lên lịch.');
                    if (!duration || duration <= 0) errors.push('Thời lượng phim phải lớn hơn 0.');
                    if (!ageRating) errors.push('Độ tuổi là bắt buộc khi Lên lịch.');
                    if (categoryCheckboxes.length === 0) errors.push('Vui lòng chọn ít nhất một thể loại phim.');
                    if (!releaseDate) {
                        errors.push('Ngày phát hành dự kiến là bắt buộc khi Lên lịch.');
                    } else {
                        const relDateObj = new Date(releaseDate);
                        if (relDateObj <= now) {
                            errors.push('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                        }
                        if (presaleDate) {
                            const preDateObj = new Date(presaleDate);
                            if (preDateObj <= now) {
                                errors.push('Ngày mở bán sớm phải là thời gian trong tương lai.');
                            }
                            if (preDateObj > relDateObj) {
                                errors.push('Ngày mở bán sớm phải trước hoặc bằng ngày phát hành dự kiến.');
                            }
                        }
                    }
                } else if (status === 'PRE_ORDER') {
                    if (!releaseDate) {
                        errors.push('Ngày phát hành dự kiến là bắt buộc khi Mở bán sớm.');
                    } else {
                        const relDateObj = new Date(releaseDate);
                        if (relDateObj <= now) {
                            errors.push('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                        }
                        if (presaleDate) {
                            const preDateObj = new Date(presaleDate);
                            if (preDateObj > relDateObj) {
                                errors.push('Ngày mở bán sớm phải trước hoặc bằng ngày phát hành dự kiến.');
                            }
                        }
                    }
                } else if (status === 'COMING_SOON') {
                    if (releaseDate) {
                        const relDateObj = new Date(releaseDate);
                        if (relDateObj <= now) {
                            errors.push('Ngày phát hành dự kiến phải là thời gian trong tương lai (lớn hơn thời điểm hiện tại).');
                        }
                        if (presaleDate) {
                            const preDateObj = new Date(presaleDate);
                            if (preDateObj > relDateObj) {
                                errors.push('Ngày mở bán sớm phải trước hoặc bằng ngày phát hành dự kiến.');
                            }
                        }
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    alert("⚠️ Vui lòng kiểm tra lại thông tin phim:\n\n- " + errors.join("\n- "));
                    return false;
                }
            });
        }
    });
</script>
@endsection
