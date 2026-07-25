@extends('layouts.frontend')

@section('title', ($post->seo_title ?: $post->title) . ' - movieGo')

@push('meta')
    @if($post->seo_description)
        <meta name="description" content="{{ $post->seo_description }}">
    @else
        <meta name="description" content="{{ Str::limit(strip_tags($post->summary), 160) }}">
    @endif
    @if($post->seo_keywords)
        <meta name="keywords" content="{{ $post->seo_keywords }}">
    @endif
    {{-- Open Graph for social sharing --}}
    <meta property="og:title" content="{{ $post->seo_title ?: $post->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($post->summary), 200) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($post->image)
    <meta property="og:image" content="{{ asset('storage/' . $post->image) }}">
    @endif
@endpush

@push('styles')
<style>
    /* === READING PROGRESS BAR === */
    #reading-progress-bar {
        position: fixed;
        top: 0; left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #e50914, #fbbf24);
        z-index: 9999;
        transition: width 0.1s linear;
        box-shadow: 0 0 8px rgba(229,9,20,0.5);
    }

    /* === ARTICLE LAYOUT === */
    .article-hero-wrap {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 40px;
        border: 1px solid rgba(255,255,255,0.07);
        box-shadow: 0 30px 80px rgba(0,0,0,0.5);
    }
    .article-hero-wrap img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        display: block;
    }
    @media(max-width: 768px) {
        .article-hero-wrap img { height: 240px; }
    }
    .article-hero-gradient {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 50%;
        background: linear-gradient(to top, rgba(6,11,20,0.8), transparent);
        pointer-events: none;
    }

    /* === BREADCRUMB === */
    .breadcrumb-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #475569;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .breadcrumb-wrap a { color: #64748b; text-decoration: none; transition: color 0.2s; }
    .breadcrumb-wrap a:hover { color: #fbbf24; }
    .breadcrumb-sep { color: #334155; }

    /* === RICH TEXT === */
    .rich-text-content {
        color: #cbd5e1;
        font-size: 1.05rem;
        line-height: 1.85;
    }
    .rich-text-content p {
        margin-bottom: 1.5rem;
    }
    .rich-text-content h2, .rich-text-content h3, .rich-text-content h4 {
        color: #ffffff;
        font-weight: 700;
        margin-top: 2.2rem;
        margin-bottom: 1rem;
    }
    .rich-text-content h2 { font-size: 1.75rem; }
    .rich-text-content h3 { font-size: 1.4rem; }
    .rich-text-content ul, .rich-text-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    .rich-text-content ul { list-style-type: disc; }
    .rich-text-content ol { list-style-type: decimal; }
    .rich-text-content li { margin-bottom: 0.5rem; }
    .rich-text-content blockquote {
        border-left: 4px solid #e50914;
        background-color: rgba(30, 41, 59, 0.5);
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border-radius: 0 0.75rem 0.75rem 0;
        font-style: italic;
    }
    .rich-text-content a {
        color: #e50914;
        text-decoration: underline;
        transition: color 0.2s;
    }
    .rich-text-content a:hover { color: #f87171; }
    .rich-text-content img {
        max-width: 100%;
        border-radius: 12px;
        margin: 1.5rem 0;
        border: 1px solid rgba(255,255,255,0.07);
    }

    /* === SUMMARY BLOCK === */
    .summary-block {
        background: linear-gradient(135deg, rgba(30,41,59,0.6), rgba(15,23,42,0.8));
        border-left: 4px solid #e50914;
        border-radius: 0 16px 16px 0;
        padding: 20px 24px;
        margin-bottom: 36px;
        color: #cbd5e1;
        font-size: 1.05rem;
        font-weight: 500;
        line-height: 1.7;
        box-shadow: inset 0 0 30px rgba(229,9,20,0.03);
    }

    /* === SOCIAL SHARE === */
    .share-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 20px 0;
        border-top: 1px solid rgba(255,255,255,0.06);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        margin-bottom: 36px;
    }
    .share-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        margin-right: 4px;
    }
    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }
    .share-btn-fb {
        background: rgba(24,119,242,0.12);
        border: 1px solid rgba(24,119,242,0.3);
        color: #60a5fa;
    }
    .share-btn-fb:hover { background: rgba(24,119,242,0.22); box-shadow: 0 4px 14px rgba(24,119,242,0.2); }
    .share-btn-x {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.12);
        color: #e2e8f0;
    }
    .share-btn-x:hover { background: rgba(255,255,255,0.1); }
    .share-btn-zalo {
        background: rgba(0,113,206,0.12);
        border: 1px solid rgba(0,113,206,0.3);
        color: #38bdf8;
    }
    .share-btn-zalo:hover { background: rgba(0,113,206,0.22); box-shadow: 0 4px 14px rgba(0,113,206,0.2); }
    .share-btn-copy {
        background: rgba(234,179,8,0.08);
        border: 1px solid rgba(234,179,8,0.2);
        color: #fbbf24;
    }
    .share-btn-copy:hover { background: rgba(234,179,8,0.15); }

    /* === RELATED POSTS === */
    .related-card {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 16px;
        background: rgba(255,255,255,0.025);
        border: 1px solid rgba(255,255,255,0.06);
        text-decoration: none;
        transition: all 0.25s;
    }
    .related-card:hover {
        background: rgba(234,179,8,0.04);
        border-color: rgba(234,179,8,0.2);
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
    }
    .related-card img {
        width: 80px; height: 80px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .related-title {
        font-size: 14px;
        font-weight: 600;
        color: white;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s;
        margin-bottom: 6px;
    }
    .related-card:hover .related-title { color: #fbbf24; }
    .related-meta { font-size: 11px; color: #475569; }

    /* === AUTHOR CARD === */
    .author-card {
        background: rgba(255,255,255,0.025);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 36px;
    }
    .author-avatar {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(229,9,20,0.3), rgba(234,179,8,0.3));
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: white;
        flex-shrink: 0;
        border: 2px solid rgba(229,9,20,0.3);
    }

    /* === COPY TOAST === */
    #copy-toast {
        position: fixed;
        bottom: 32px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #0f172a;
        border: 1px solid rgba(234,179,8,0.3);
        color: #fbbf24;
        padding: 10px 22px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 600;
        z-index: 9999;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
        white-space: nowrap;
    }
    #copy-toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
</style>
@endpush

@section('content')

{{-- Reading Progress Bar --}}
<div id="reading-progress-bar"></div>

{{-- Copy Link Toast --}}
<div id="copy-toast"><i class="fas fa-check me-2"></i>Đã sao chép liên kết!</div>

<div class="min-h-screen bg-slate-950 text-white pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="breadcrumb-wrap">
            <a href="{{ route('posts.index') }}"><i class="fas fa-newspaper me-1"></i>Tin Tức</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right text-[9px]"></i></span>
            @if($post->category)
                <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right text-[9px]"></i></span>
            @endif
            <span class="text-slate-300 line-clamp-1 max-w-xs">{{ Str::limit($post->title, 50) }}</span>
        </div>

        {{-- Category badge --}}
        <div class="mb-5">
            @if($post->category)
            <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}"
               class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all hover:opacity-80"
               style="background:rgba(229,9,20,0.12);border:1px solid rgba(229,9,20,0.3);color:#f87171;">
                <i class="fas fa-tag text-[10px]"></i>
                {{ $post->category->name }}
            </a>
            @endif
        </div>

        {{-- Title --}}
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white mb-6 leading-tight">
            {{ $post->title }}
        </h1>

        {{-- Meta info --}}
        <div class="mb-8 flex flex-wrap items-center gap-3">
            @php
                $wordCount = str_word_count(strip_tags($post->content ?? ''));
                $readMin = max(1, ceil($wordCount / 200));
            @endphp
            <span class="text-slate-400 text-sm flex items-center gap-1.5">
                <i class="fas fa-user-circle text-slate-500"></i>
                {{ $post->author?->name ?? 'movieGo' }}
            </span>
            <span class="text-slate-700">•</span>
            <span class="text-slate-400 text-sm flex items-center gap-1.5">
                <i class="fas fa-calendar-alt text-slate-500"></i>
                {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i') }}
            </span>
            <span class="text-slate-700">•</span>
            <span class="text-slate-400 text-sm flex items-center gap-1.5">
                <i class="fas fa-clock text-slate-500"></i>
                ~{{ $readMin }} phút đọc
            </span>
            <span class="text-slate-700">•</span>
            <span class="text-slate-400 text-sm flex items-center gap-1.5">
                <i class="fas fa-eye text-slate-500"></i>
                {{ number_format($post->views) }} lượt xem
            </span>
        </div>

        {{-- Banner Image --}}
        <div class="article-hero-wrap">
            @if($post->banner)
                <img src="{{ asset('storage/' . $post->banner) }}" alt="{{ $post->title }}">
            @elseif($post->image)
                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}">
            @else
                <div class="w-full bg-slate-900 flex items-center justify-center text-slate-500" style="height:300px;">
                    <i class="fas fa-newspaper text-5xl"></i>
                </div>
            @endif
            <div class="article-hero-gradient"></div>
        </div>

        {{-- Highlighted Summary --}}
        <div class="summary-block">
            {{ $post->summary }}
        </div>

        {{-- Article Content --}}
        <div class="rich-text-content mb-10">
            {!! $post->content !!}
        </div>

        {{-- Author Card --}}
        @if($post->author)
        <div class="author-card">
            <div class="author-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 uppercase tracking-wider mb-0.5">Tác giả</div>
                <div class="font-bold text-white">{{ $post->author->name }}</div>
                <div class="text-xs text-slate-500 mt-1">Đội ngũ biên tập movieGo</div>
            </div>
        </div>
        @endif

        {{-- Social Share Bar --}}
        <div class="share-bar">
            <span class="share-label"><i class="fas fa-share-alt me-1"></i>Chia sẻ</span>
            {{-- Facebook --}}
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
               target="_blank" rel="noopener" class="share-btn share-btn-fb">
                <i class="fab fa-facebook-f"></i> Facebook
            </a>
            {{-- X (Twitter) --}}
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}"
               target="_blank" rel="noopener" class="share-btn share-btn-x">
                <i class="fab fa-x-twitter"></i> X
            </a>
            {{-- Zalo --}}
            <a href="https://zalo.me/share/url?url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}"
               target="_blank" rel="noopener" class="share-btn share-btn-zalo">
                <span style="font-weight:800;font-size:11px;">Zalo</span>
            </a>
            {{-- Copy link --}}
            <button onclick="copyLink()" class="share-btn share-btn-copy" id="copy-btn">
                <i class="fas fa-link text-[11px]"></i> Sao chép link
            </button>
        </div>

        {{-- Related Posts Section --}}
        @if($relatedPosts->count() > 0)
            <div class="border-t border-slate-800 pt-12">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-newspaper text-primary"></i> Tin tức liên quan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($relatedPosts as $relPost)
                        <a href="{{ route('posts.show', $relPost->slug) }}" class="related-card">
                            @if($relPost->image)
                                <img src="{{ asset('storage/' . $relPost->image) }}" alt="{{ $relPost->title }}" loading="lazy">
                            @else
                                <div class="related-card-img bg-slate-800 flex items-center justify-center text-slate-500" style="width:80px;height:80px;border-radius:12px;flex-shrink:0;">
                                    <i class="fas fa-newspaper text-lg"></i>
                                </div>
                            @endif
                            <div class="flex flex-col justify-between min-w-0">
                                <div class="related-title">{{ $relPost->title }}</div>
                                <div class="related-meta flex items-center gap-2">
                                    <span>{{ $relPost->published_at ? $relPost->published_at->format('d/m/Y') : '' }}</span>
                                    <span>·</span>
                                    <span><i class="fas fa-eye me-1"></i>{{ number_format($relPost->views) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Back to list --}}
        <div class="mt-12 pt-8 border-t border-slate-800/50 flex items-center justify-between">
            <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm font-semibold">
                <i class="fas fa-arrow-left"></i> Quay lại Danh sách Tin tức
            </a>
            <button onclick="window.scrollTo({top:0,behavior:'smooth'})"
                    class="inline-flex items-center gap-2 text-slate-500 hover:text-amber-400 transition-colors text-sm">
                <i class="fas fa-arrow-up text-xs"></i> Lên đầu
            </button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// =====================================================================
// READING PROGRESS BAR
// =====================================================================
(function() {
    const bar = document.getElementById('reading-progress-bar');
    if (!bar) return;
    function updateBar() {
        const scrollTop   = window.scrollY || document.documentElement.scrollTop;
        const docHeight   = document.documentElement.scrollHeight - window.innerHeight;
        const pct         = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        bar.style.width   = Math.min(pct, 100) + '%';
    }
    window.addEventListener('scroll', updateBar, { passive: true });
})();

// =====================================================================
// COPY LINK
// =====================================================================
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const toast = document.getElementById('copy-toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = window.location.href;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        const toast = document.getElementById('copy-toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    });
}
</script>
@endpush
