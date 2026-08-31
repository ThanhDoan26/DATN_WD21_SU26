<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
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
