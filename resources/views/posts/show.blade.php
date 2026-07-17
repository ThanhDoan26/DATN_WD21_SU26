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
@endpush

@push('styles')
<style>
    .rich-text-content {
        color: #cbd5e1; /* text-slate-300 */
        font-size: 1.1rem;
        line-height: 1.8;
    }
    .rich-text-content p {
        margin-bottom: 1.5rem;
    }
    .rich-text-content h2, .rich-text-content h3, .rich-text-content h4 {
        color: #ffffff;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .rich-text-content h2 { font-size: 1.75rem; }
    .rich-text-content h3 { font-size: 1.5rem; }
    
    .rich-text-content ul, .rich-text-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    .rich-text-content ul { list-style-type: disc; }
    .rich-text-content ol { list-style-type: decimal; }
    .rich-text-content li { margin-bottom: 0.5rem; }
    
    .rich-text-content blockquote {
        border-left: 4px solid #e50914; /* primary */
        background-color: rgba(30, 41, 59, 0.5); /* slate-800/50 */
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
    .rich-text-content a:hover {
        color: #f87171;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-950 text-white pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back link -->
        <div class="mb-8">
            <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm font-semibold">
                <i class="fas fa-arrow-left"></i> Quay lại Danh sách Tin tức
            </a>
        </div>

        <!-- Banner Image -->
        <div class="relative rounded-3xl overflow-hidden mb-10 shadow-2xl border border-slate-800">
            @if($post->banner)
                <img src="{{ asset('storage/' . $post->banner) }}" alt="{{ $post->title }}" class="w-full h-[350px] md:h-[450px] object-cover">
            @elseif($post->image)
                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-[350px] md:h-[450px] object-cover">
            @else
                <div class="w-full h-[300px] bg-slate-900 flex items-center justify-center text-slate-500">No Image</div>
            @endif
        </div>

        <!-- Meta info -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <a href="{{ route('posts.index', ['category' => $post->category?->slug]) }}" class="bg-primary text-white text-xs font-semibold px-3 py-1 rounded-md transition-colors hover:bg-red-700">
                {{ $post->category?->name }}
            </a>
            <span class="text-slate-500">•</span>
            <span class="text-slate-400 text-sm"><i class="fas fa-calendar-alt me-1"></i>{{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i') }}</span>
            <span class="text-slate-500">•</span>
            <span class="text-slate-400 text-sm"><i class="fas fa-eye me-1"></i>{{ $post->views }} lượt xem</span>
            <span class="text-slate-500">•</span>
            <span class="text-slate-400 text-sm"><i class="fas fa-user me-1"></i>{{ $post->author?->name }}</span>
        </div>

        <!-- Title -->
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white mb-8 leading-tight">
            {{ $post->title }}
        </h1>

        <!-- Highlighted Summary -->
        <div class="bg-slate-900/80 border-l-4 border-primary p-6 rounded-r-2xl mb-10 text-slate-300 font-medium text-lg leading-relaxed shadow-lg">
            {{ $post->summary }}
        </div>

        <!-- Article Content -->
        <div class="rich-text-content mb-16">
            {!! $post->content !!}
        </div>

        <!-- Related Posts Section -->
        @if($relatedPosts->count() > 0)
            <div class="border-t border-slate-800 pt-12">
                <h3 class="text-2xl font-bold mb-8 flex items-center gap-2">
                    <i class="fas fa-newspaper text-primary"></i> Tin tức liên quan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($relatedPosts as $relPost)
                        <a href="{{ route('posts.show', $relPost->slug) }}" class="flex gap-4 p-4 rounded-2xl bg-slate-900/40 border border-slate-850 hover:border-slate-800 transition-all group">
                            <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0">
                                @if($relPost->image)
                                    <img src="{{ asset('storage/' . $relPost->image) }}" alt="{{ $relPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-500 text-xs">No image</div>
                                @endif
                            </div>
                            <div class="flex flex-col justify-between">
                                <h4 class="text-base font-bold text-white group-hover:text-red-400 transition-colors line-clamp-2">
                                    {{ $relPost->title }}
                                </h4>
                                <div class="flex items-center gap-2 text-slate-500 text-xs">
                                    <span>{{ $relPost->published_at ? $relPost->published_at->format('d/m/Y') : '' }}</span>
                                    <span>•</span>
                                    <span>{{ $relPost->views }} lượt xem</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
