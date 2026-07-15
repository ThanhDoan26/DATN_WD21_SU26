<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of posts for frontend users.
     */
    public function index(Request $request)
    {
        // 1. Lấy danh sách danh mục để hiển thị tab lọc
        $categories = PostCategory::withCount(['posts' => function ($q) {
            $q->where('status', 'Published');
        }])->get();

        // 2. Lấy các tin tức nổi bật (Featured)
        $featuredPosts = Post::where('status', 'Published')
            ->where('is_featured', true)
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        // 3. Khởi tạo query danh sách tin tức chính
        $query = Post::where('status', 'Published')
            ->with(['category', 'author']);

        // Lọc theo danh mục nếu được chọn
        if ($request->filled('category')) {
            $categorySlug = $request->category;
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Tìm kiếm từ khóa nếu có
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('summary', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Lấy danh sách bài viết phân trang (6 bài mỗi trang)
        $posts = $query->orderBy('published_at', 'desc')
            ->paginate(6)
            ->withQueryString();

        return view('posts.index', compact('posts', 'categories', 'featuredPosts'));
    }

    /**
     * Display the specified post for frontend users.
     */
    public function show(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'Published')
            ->with(['category', 'author'])
            ->firstOrFail();

        // Xử lý tăng lượt xem (views) - Sử dụng session để chống tăng liên tục khi refresh
        $viewedPosts = session()->get('viewed_posts', []);

        if (!in_array($post->id, $viewedPosts)) {
            $post->increment('views');
            session()->push('viewed_posts', $post->id);
        }

        // Lấy tin liên quan (cùng danh mục, loại trừ bài hiện tại)
        $relatedPosts = Post::where('status', 'Published')
            ->where('post_category_id', $post->post_category_id)
            ->where('id', '!=', $post->id)
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }
}
