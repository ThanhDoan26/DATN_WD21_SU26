<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('default app locale is configured as vietnamese', function () {
    expect(app()->getLocale())->toBe('vi');
});

test('validation required rule returns vietnamese message with mapped attribute', function () {
    $validator = Validator::make([], [
        'name' => 'required',
        'email' => 'required',
        'password' => 'required',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('name'))->toBe('Vui lòng nhập họ tên.');
    expect($validator->errors()->first('email'))->toBe('Vui lòng nhập email.');
    expect($validator->errors()->first('password'))->toBe('Vui lòng nhập mật khẩu.');
});

test('validation email and min rules return vietnamese messages', function () {
    $validator = Validator::make([
        'email' => 'invalid-email',
        'password' => '123',
    ], [
        'email' => 'email',
        'password' => 'min:8',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toBe('Trường email phải là một địa chỉ email hợp lệ.');
    expect($validator->errors()->first('password'))->toBe('Trường mật khẩu phải có ít nhất 8 ký tự.');
});

test('validation unique and exists rules return vietnamese messages', function () {
    $role = Role::firstOrCreate(
        ['role_name' => 'USER'],
        ['description' => 'User role']
    );

    $existingUser = User::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'status' => 'ACTIVE',
    ]);

    $validator = Validator::make([
        'email' => 'existing@example.com',
        'role_id' => 999999,
    ], [
        'email' => 'unique:users,email',
        'role_id' => 'exists:roles,id',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toContain('đã được sử dụng');
    expect($validator->errors()->first('role_id'))->toBe('Giá trị đã chọn trong trường vai trò không tồn tại.');
});

test('auth language lines are in vietnamese', function () {
    expect(Lang::get('auth.failed'))->toContain('Thông tin đăng nhập không chính xác');
    expect(Lang::get('auth.password'))->toContain('Mật khẩu đã nhập không chính xác');
    expect(Lang::get('auth.throttle', ['seconds' => 60]))->toContain('Đăng nhập thất bại quá nhiều lần');
});

test('passwords language lines are in vietnamese', function () {
    expect(Lang::get('passwords.reset'))->toContain('đặt lại thành công');
    expect(Lang::get('passwords.sent'))->toContain('Chúng tôi đã gửi email');
    expect(Lang::get('passwords.user'))->toContain('Không tìm thấy người dùng');
});
