<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UpdateCinemaRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('cinemas', 'name')->ignore($this->cinema)
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên rạp chiếu phim là bắt buộc.',
            'name.max' => 'Tên rạp không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên rạp chiếu phim này đã tồn tại.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'city.required' => 'Thành phố là bắt buộc.',
            'city.max' => 'Thành phố không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ, chỉ được chọn Hoạt động hoặc Tạm ngưng.',
        ];
    }
}
