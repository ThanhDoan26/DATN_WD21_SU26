<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Giả sử middleware `admin` đã lo phần quyền
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'cinema_id' => [
                'nullable',
                'exists:cinemas,id',
                Rule::requiredIf(function () {
                    $role = Role::find($this->input('role_id'));
                    return $role && in_array(strtoupper($role->role_name), ['MANAGER', 'STAFF']);
                }),
            ],
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }

    public function messages(): array
    {
        return [
            'cinema_id.required' => 'Vui lòng chọn rạp làm việc cho vai trò Quản lý (Manager) hoặc Nhân viên (Staff).',
            'cinema_id.exists'   => 'Rạp được chọn không tồn tại trong hệ thống.',
        ];
    }
}
