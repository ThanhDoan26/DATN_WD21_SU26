<table class="table table-hover align-middle mb-0" id="top-combos-table">
    <thead class="table-light">
        <tr>
            <th width="80" class="text-center">Hạng</th>
            <th>Tên Combo</th>
            <th width="150" class="text-center">Điểm TB</th>
            <th width="150" class="text-center">Lượt ĐG</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topCombos ?? [] as $index => $combo)
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
            <td class="fw-bold">{{ $combo->name }}</td>
            <td class="text-center text-warning fw-bold">
                {{ number_format($combo->average_rating, 1) }} <i class="fas fa-star small"></i>
            </td>
            <td class="text-center">
                <span class="badge bg-info text-dark">{{ $combo->total_reviews }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center text-muted py-4">
                <div class="empty-state py-3 text-center">
                    <i class="fas fa-utensils text-muted mb-2" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="text-muted mb-0 small">Chưa có đánh giá nào cho các Combo trong cụm rạp này.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
