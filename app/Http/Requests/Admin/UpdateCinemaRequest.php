<?php

namespace App\Http\Requests\Admin;

use App\Models\Cinema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCinemaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Prepare the data for validation (Sanitize Input).
     */
    protected function prepareForValidation(): void
    {
        $sanitized = [];

        if ($this->has('name') && is_string($this->name)) {
            $sanitized['name'] = preg_replace('/\s+/u', ' ', trim($this->name));
        }

        if ($this->has('address') && is_string($this->address)) {
            $sanitized['address'] = preg_replace('/\s+/u', ' ', trim($this->address));
        }

        if ($this->has('city') && is_string($this->city)) {
            $sanitized['city'] = preg_replace('/\s+/u', ' ', trim($this->city));
        }

        if ($this->has('phone') && is_string($this->phone)) {
            $sanitized['phone'] = trim($this->phone);
        }

        if ($this->has('email') && is_string($this->email)) {
            $sanitized['email'] = trim($this->email);
        }

        $this->merge($sanitized);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cinema = $this->route('cinema') ?? $this->cinema;
        $cinemaId = $cinema instanceof \App\Models\Cinema ? $cinema->id : $cinema;

        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('cinemas', 'name')->whereNull('deleted_at')->ignore($cinemaId),
                function ($attribute, $value, $fail) use ($cinemaId) {
                    $cleanName = preg_replace('/\s+/u', ' ', trim($value));
                    $collapsed = mb_strtolower(preg_replace('/\s+/u', '', $value), 'UTF-8');

                    $query = Cinema::whereNull('deleted_at');
                    if ($cinemaId) {
                        $query->where('id', '!=', $cinemaId);
                    }

                    $exists = $query->where(function ($q) use ($cleanName, $collapsed) {
                        $q->where('name', $cleanName)
                          ->orWhereRaw("REPLACE(LOWER(name), ' ', '') = ?", [$collapsed]);
                    })->exists();

                    if ($exists) {
                        $fail('Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!');
                    }
                },
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
            'name.unique' => 'Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!',
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
