<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use Illuminate\Support\Str;

class PostCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = PostCategory::withCount('posts')
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('admin.post-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.post-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        PostCategory::create($validated);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Danh mục bài viết đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PostCategory $postCategory)
    {
        return view('admin.post-categories.edit', compact('postCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $postCategory->update($validated);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Danh mục bài viết đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostCategory $postCategory)
    {
        // Kiểm tra xem danh mục có bài viết nào đang sử dụng không
        if ($postCategory->posts()->exists()) {
            return redirect()->route('admin.post-categories.index')
                ->with('error', 'Không thể xóa danh mục "' . $postCategory->name . '" vì vẫn còn bài viết đang sử dụng danh mục này.');
        }

        $postCategory->delete();

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Danh mục bài viết đã được xóa thành công.');
    }
}
