<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with(['category', 'author']);

        // Tìm kiếm theo tiêu đề, nội dung, danh mục, tác giả
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('author', function ($authorQ) use ($search) {
                      $authorQ->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Lọc theo danh mục
        if ($request->filled('post_category_id')) {
            $query->where('post_category_id', $request->post_category_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo tác giả
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Lọc theo tin nổi bật
        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        // Lọc theo khoảng ngày (published_at)
        if ($request->filled('start_date')) {
            $query->whereDate('published_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('published_at', '<=', $request->end_date);
        }

        // Sắp xếp
        $sortBy = $request->input('sort_by', 'published_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if (in_array($sortBy, ['title', 'published_at', 'views', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('published_at', 'desc');
        }

        $posts = $query->paginate(10)->withQueryString();
        
        $categories = PostCategory::all();
        // Lấy danh sách admin/staff có bài viết hoặc có quyền đăng bài
        $authors = User::whereHas('role', function ($q) {
            $q->whereIn('role_name', ['ADMIN', 'STAFF']);
        })->get();

        return view('admin.posts.index', compact('posts', 'categories', 'authors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = PostCategory::all();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();
        $validated['is_featured'] = $request->has('is_featured') ? true : false;

        // Nếu trạng thái là Published và không chọn ngày đăng thì mặc định là thời điểm hiện tại
        if ($validated['status'] === 'Published') {
            $validated['published_at'] = $request->filled('published_at') ? $request->published_at : now();
        } else {
            $validated['published_at'] = $request->filled('published_at') ? $request->published_at : null;
        }

        // Upload Thumbnail
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_thumb_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $validated['image'] = $file->storeAs('posts', $filename, 'public');
        }

        // Upload Banner (nếu có)
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = time() . '_banner_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $validated['banner'] = $file->storeAs('posts', $filename, 'public');
        }

        Post::create($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Bài viết đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load(['category', 'author']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = PostCategory::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->has('is_featured') ? true : false;

        // Xử lý ngày đăng
        if ($validated['status'] === 'Published') {
            $validated['published_at'] = $request->filled('published_at') ? $request->published_at : ($post->published_at ?? now());
        } else {
            $validated['published_at'] = $request->filled('published_at') ? $request->published_at : null;
        }

        // Upload Thumbnail mới và xóa ảnh cũ
        if ($request->hasFile('image')) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            $file = $request->file('image');
            $filename = time() . '_thumb_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $validated['image'] = $file->storeAs('posts', $filename, 'public');
        }

        // Upload Banner mới và xóa banner cũ
        if ($request->hasFile('banner')) {
            if ($post->banner && Storage::disk('public')->exists($post->banner)) {
                Storage::disk('public')->delete($post->banner);
            }
            $file = $request->file('banner');
            $filename = time() . '_banner_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $validated['banner'] = $file->storeAs('posts', $filename, 'public');
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Bài viết đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Bài viết đã được xóa tạm thời (đưa vào thùng rác).');
    }

    /**
     * Display a listing of trashed resources.
     */
    public function trashed(Request $request)
    {
        $query = Post::onlyTrashed()->with(['category', 'author']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }

        $posts = $query->orderBy('deleted_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.posts.trashed', compact('posts'));
    }

    /**
     * Restore a trashed resource.
     */
    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Bài viết đã được khôi phục thành công.');
    }

    /**
     * Permanently delete a resource.
     */
    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        // Xóa hình ảnh từ ổ đĩa
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        if ($post->banner && Storage::disk('public')->exists($post->banner)) {
            Storage::disk('public')->delete($post->banner);
        }

        $post->forceDelete();

        return redirect()->route('admin.posts.trashed')
            ->with('success', 'Bài viết đã được xóa vĩnh viễn khỏi hệ thống.');
    }

    /**
     * Toggle status quick action.
     */
    public function toggleStatus(Post $post)
    {
        if ($post->status === 'Published') {
            $post->status = 'Hidden';
        } else {
            $post->status = 'Published';
            if (!$post->published_at) {
                $post->published_at = now();
            }
        }
        $post->save();

        return back()->with('success', 'Trạng thái bài viết đã được cập nhật.');
    }

    /**
     * Toggle featured status quick action.
     */
    public function toggleFeatured(Post $post)
    {
        $post->is_featured = !$post->is_featured;
        $post->save();

        return back()->with('success', 'Đã thay đổi trạng thái nổi bật bài viết.');
    }
}
