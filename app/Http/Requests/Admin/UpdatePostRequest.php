<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $postId = $this->route('post') ? $this->route('post')->id : $this->route('post_id');

        return [
            'title' => 'required|string|max:255|unique:posts,title,' . $postId,
            'post_category_id' => 'required|exists:post_categories,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'summary' => 'required|string|max:500',
            'content' => 'required|string',
            'status' => 'required|in:Draft,Published,Hidden',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề bài viết là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'title.unique' => 'Tiêu đề bài viết này đã tồn tại.',
            'post_category_id.required' => 'Danh mục bài viết là bắt buộc.',
            'post_category_id.exists' => 'Danh mục bài viết không hợp lệ.',
            'image.image' => 'Ảnh đại diện phải là định dạng hình ảnh.',
            'image.mimes' => 'Ảnh đại diện chỉ chấp nhận định dạng jpeg, jpg, png, webp.',
            'image.max' => 'Ảnh đại diện dung lượng tối đa 2MB.',
            'banner.image' => 'Banner phải là định dạng hình ảnh.',
            'banner.mimes' => 'Banner chỉ chấp nhận định dạng jpeg, jpg, png, webp.',
            'banner.max' => 'Banner dung lượng tối đa 2MB.',
            'summary.required' => 'Mô tả ngắn là bắt buộc.',
            'summary.max' => 'Mô tả ngắn không vượt quá 500 ký tự.',
            'content.required' => 'Nội dung chi tiết là bắt buộc.',
            'status.required' => 'Trạng thái bài viết là bắt buộc.',
            'status.in' => 'Trạng thái bài viết không hợp lệ.',
            'seo_title.max' => 'SEO Title không vượt quá 255 ký tự.',
            'seo_description.max' => 'SEO Description không vượt quá 500 ký tự.',
            'seo_keywords.max' => 'SEO Keywords không vượt quá 255 ký tự.',
        ];
    }
}
