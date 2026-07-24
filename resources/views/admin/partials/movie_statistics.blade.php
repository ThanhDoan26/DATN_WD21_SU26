<div class="table-responsive px-4 py-3">
    <table class="table table-hover align-middle mb-0" id="movie-statistics-table">
        <thead class="table-light">
            <tr>
                <th width="70" class="text-center">STT</th>
                <th>Phim</th>
                <th width="120" class="text-center">Suất chiếu</th>
                <th width="120" class="text-center">Số vé</th>
                <th width="140" class="text-end">Doanh Thu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movieStatistics ?? [] as $index => $movie)
                <tr>
                    <td class="text-center text-muted small">{{ $index + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if(isset($movie->poster_url) && $movie->poster_url)
                                <img src="{{ Str::startsWith($movie->poster_url, ['http', 'https']) ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="rounded me-2" style="width: 40px; height: 60px; object-fit: cover;">
                            @else
                                <div class="rounded me-2 bg-light d-flex justify-content-center align-items-center text-muted" style="width: 40px; height: 60px;">
                                    <i class="fas fa-film"></i>
                                </div>
                            @endif
                            <div class="text-truncate" style="max-width: 260px;">
                                <div class="fw-bold text-dark" title="{{ $movie->title }}">{{ $movie->title }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center text-secondary fw-semibold">{{ number_format($movie->total_showtimes ?? 0) }}</td>
                    <td class="text-center text-success fw-semibold">{{ number_format($movie->total_tickets ?? 0) }}</td>
                    <td class="text-end fw-bold text-primary">{{ number_format($movie->total_revenue ?? 0) }} đ</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <div class="empty-state py-3 text-center">
                            <i class="fas fa-film text-muted mb-2" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="text-muted mb-0 small">Chưa có dữ liệu thống kê phim cho khoảng thời gian này.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
