<div class="bg-slate-800/30 p-6 rounded-2xl border border-slate-700/30 flex gap-4 review-item" data-user-id="{{ $review->user_id }}">
    <div class="w-12 h-12 bg-slate-700 rounded-full flex items-center justify-center text-xl font-bold text-slate-300 flex-shrink-0">
        {{ substr($review->user->name, 0, 1) }}
    </div>
    <div class="flex-1">
        <div class="flex items-center justify-between mb-2">
            <h4 class="font-bold text-white">{{ $review->user->name }}</h4>
            <span class="text-xs text-slate-500">{{ $review->created_at->diffForHumans() }}</span>
        </div>
        <div class="flex text-yellow-400 text-sm mb-3">
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-slate-600' }}"></i>
            @endfor
        </div>
        <p class="text-slate-300 leading-relaxed">{{ $review->comment }}</p>
    </div>
</div>
