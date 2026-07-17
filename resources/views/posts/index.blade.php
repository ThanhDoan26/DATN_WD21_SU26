@extends('layouts.frontend')

@section('title', 'Tin Tức & Sự Kiện - movieGo')

@section('content')
<div class="min-h-screen bg-slate-950 text-white pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Hero Section -->
        <div class="relative rounded-3xl overflow-hidden mb-16 shadow-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-md">
            <div class="absolute inset-0 bg-gradient-to-r from-red-600/20 via-slate-950/80 to-slate-950 z-0"></div>
            <div class="relative z-10 py-16 px-8 md:px-16 max-w-3xl">
                <span class="inline-block py-1.5 px-4 rounded-full bg-primary/20 text-primary border border-primary/30 font-semibold text-xs uppercase tracking-wider mb-4">
                    Bản tin điện ảnh
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white leading-tight">
                    Tin Tức & Sự Kiện Nổi Bật
                </h1>
                <p class="text-slate-400 text-base md:text-lg mb-8">
                    Cập nhật nhanh nhất tin tức điện ảnh mới nhất, sự kiện rạp chiếu phim, các hoạt động hấp dẫn và chương trình khuyến mãi ngập tràn quà tặng dành riêng cho bạn.
                </p>

                <!-- Search form -->
                <form action="{{ route('posts.index') }}" method="GET" class="flex gap-2 max-w-md">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="relative flex-grow">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm bài viết..." class="w-full pl-11 pr-4 py-3 bg-slate-800/80 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                    <button type="submit" class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-xl font-medium transition-all shadow-lg shadow-red-500/20">
                        Tìm
                    </button>
                </form>
            </div>
        </div>

        <!-- Featured Section (Spotlight) -->
        @if($featuredPosts->count() > 0 && !request()->filled('search') && !request()->filled('category'))
            <div class="mb-16">
                <h2 class="text-2xl md:text-3xl font-bold mb-8 flex items-center gap-2">
                    <i class="fas fa-star text-primary"></i> Tiêu Điểm Tin Tức
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Big Featured Post -->
                    @if(isset($featuredPosts[0]))
                        @php $first = $featuredPosts[0]; @endphp
                        <div class="lg:col-span-2 relative rounded-2xl overflow-hidden group border border-slate-800 bg-slate-900 shadow-xl flex flex-col h-[450px]">
                            <img src="{{ asset('storage/' . $first->image) }}" alt="{{ $first->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 z-0">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent z-10"></div>
                            
                            <div class="relative z-20 mt-auto p-6 md:p-8">
                                <span class="bg-primary text-white text-xs font-semibold px-3 py-1 rounded-md mb-3 inline-block">
                                    {{ $first->category?->name }}
                                </span>
                                <h3 class="text-2xl md:text-3xl font-bold mb-3 text-white group-hover:text-red-400 transition-colors">
                                    <a href="{{ route('posts.show', $first->slug) }}">{{ $first->title }}</a>
                                </h3>
                                <p class="text-slate-300 text-sm md:text-base mb-4 line-clamp-2">
                                    {{ $first->summary }}
                                </p>
                                <div class="flex items-center gap-4 text-slate-400 text-xs md:text-sm">
                                    <span><i class="fas fa-user-circle me-1 text-slate-400"></i>{{ $first->author?->name }}</span>
                                    <span><i class="fas fa-calendar-alt me-1 text-slate-400"></i>{{ $first->published_at ? $first->published_at->format('d/m/Y') : '' }}</span>
                                    <span><i class="fas fa-eye me-1 text-slate-400"></i>{{ $first->views }} lượt xem</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Sub Featured Posts -->
                    <div class="flex flex-col gap-6">
                        @foreach($featuredPosts->skip(1) as $subPost)
                            <div class="relative rounded-2xl overflow-hidden group border border-slate-800 bg-slate-900 shadow-xl flex flex-col h-[212px]">
                                <img src="{{ asset('storage/' . $subPost->image) }}" alt="{{ $subPost->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 z-0">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent z-10"></div>
                                
                                <div class="relative z-20 mt-auto p-5">
                                    <span class="bg-primary text-white text-[10px] font-semibold px-2 py-0.5 rounded-md mb-2 inline-block">
                                        {{ $subPost->category?->name }}
                                    </span>
                                    <h4 class="text-lg font-bold mb-2 text-white group-hover:text-red-400 transition-colors line-clamp-2">
                                        <a href="{{ route('posts.show', $subPost->slug) }}">{{ $subPost->title }}</a>
                                    </h4>
                                    <div class="flex items-center gap-3 text-slate-400 text-xs">
                                        <span>{{ $subPost->published_at ? $subPost->published_at->format('d/m/Y') : '' }}</span>
                                        <span><i class="fas fa-eye me-1"></i>{{ $subPost->views }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Categories & Main Blog List -->
        <div>
            <!-- Categories Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 mb-10 pb-4 border-b border-slate-800">
                <a href="{{ route('posts.index', request()->only('search')) }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all {{ !request('category') ? 'bg-primary text-white shadow-lg shadow-red-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Tất cả tin tức
                </a>
                @foreach($categories as $cat)
                    @if($cat->posts_count > 0 || request('category') === $cat->slug)
                        <a href="{{ route('posts.index', array_merge(request()->only('search'), ['category' => $cat->slug])) }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all flex items-center gap-2 {{ request('category') === $cat->slug ? 'bg-primary text-white shadow-lg shadow-red-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                            {{ $cat->name }}
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request('category') === $cat->slug ? 'bg-red-700 text-red-100' : 'bg-slate-800 text-slate-500' }}">{{ $cat->posts_count }}</span>
                        </a>
                    @endif
                @endforeach
            </div>

            <!-- Feed Header -->
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl md:text-2xl font-bold">
                    @if(request('category'))
                        Mục: {{ $categories->firstWhere('slug', request('category'))?->name }}
                    @elseif(request('search'))
                        Kết quả cho: "{{ request('search') }}"
                    @else
                        Tin mới cập nhật
                    @endif
                </h3>
                @if(request('search') || request('category'))
                    <a href="{{ route('posts.index') }}" class="text-slate-400 hover:text-white text-sm flex items-center gap-1 bg-slate-900 border border-slate-850 px-3 py-1.5 rounded-lg transition-colors">
                        Xoá bộ lọc <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>

            <!-- Articles Grid -->
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <article class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden flex flex-col group hover:border-slate-700/80 transition-all duration-300 shadow-lg">
                            <div class="relative h-48 overflow-hidden">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-500">No Image</div>
                                @endif
                                <span class="absolute top-4 left-4 bg-slate-950/80 backdrop-blur-sm text-primary text-xs font-semibold px-3 py-1 rounded-md border border-slate-800">
                                    {{ $post->category?->name }}
                                </span>
                            </div>

                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center gap-3 text-slate-400 text-xs mb-3">
                                    <span><i class="fas fa-calendar-alt me-1"></i>{{ $post->published_at ? $post->published_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span><i class="fas fa-eye me-1"></i>{{ $post->views }} lượt xem</span>
                                </div>

                                <h4 class="text-lg font-bold text-white mb-2 group-hover:text-red-400 transition-colors line-clamp-2">
                                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                                </h4>

                                <p class="text-slate-400 text-sm mb-6 line-clamp-3">
                                    {{ $post->summary }}
                                </p>

                                <div class="mt-auto pt-4 border-t border-slate-800/80 flex items-center justify-between">
                                    <span class="text-xs text-slate-500 font-medium">Tác giả: {{ $post->author?->name }}</span>
                                    <a href="{{ route('posts.show', $post->slug) }}" class="text-primary hover:text-red-400 text-sm font-semibold flex items-center gap-1 transition-colors">
                                        Đọc tiếp <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Tailwind Pagination -->
                <div class="mt-12">
                    {{ $posts->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-20 bg-slate-900/30 rounded-2xl border border-slate-850">
                    <i class="fas fa-newspaper text-slate-600 text-5xl mb-4"></i>
                    <h4 class="text-xl font-bold mb-1">Không tìm thấy bài viết nào!</h4>
                    <p class="text-slate-500">Hãy thử tìm kiếm từ khóa khác hoặc quay lại sau.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
