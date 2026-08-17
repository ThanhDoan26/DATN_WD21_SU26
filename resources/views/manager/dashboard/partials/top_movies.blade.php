<table class="table table-hover align-middle mb-0" id="top-movies-table">
    <thead class="table-light">
        <tr>
            <th width="80" class="text-center">Hạng</th>
            <th>Tên Phim</th>
            <th width="120" class="text-center">Số Vé</th>
            <th width="150" class="text-end">Doanh Thu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topMovies ?? [] as $index => $movie)
        <tr>
            <td class="text-center">
                @if($index == 0)
                    <i class="fas fa-medal text-warning fs-4"></i>
                @elseif($index == 1)
                    <i class="fas fa-medal text-secondary fs-4"></i>
                @elseif($index == 2)
                    <i class="fas fa-medal" style="color: #cd7f32; font-size: 1.5rem;"></i>
                @else
                    <span class="fw-bold text-muted">{{ $index + 1 }}</span>
                @endif
            </td>
            <td>
                <div class="d-flex align-items-center">
                    @if(isset($movie->poster_url) && $movie->poster_url)
                        <img src="{{ Str::startsWith($movie->poster_url, ['http', 'https']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="rounded me-2" style="width: 40px; height: 60px; object-fit: cover;">
                    @else
                        <div class="rounded me-2 bg-light d-flex justify-content-center align-items-center text-muted" style="width: 40px; height: 60px;">
                            <i class="fas fa-film"></i>
                        </div>
                    @endif
                    <span class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="{{ $movie->title }}">{{ $movie->title }}</span>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-success text-white px-2 py-1">{{ number_format($movie->total_tickets) }}</span>
            </td>
            <td class="text-end fw-bold text-primary">
                {{ number_format($movie->total_revenue) }} đ
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center text-muted py-4">
                <div class="empty-state py-3 text-center">
                    <i class="fas fa-film text-muted mb-2" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="text-muted mb-0 small">Chưa có dữ liệu phim bán chạy trong khoảng thời gian này.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
