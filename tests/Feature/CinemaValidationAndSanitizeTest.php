<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\StoreCinemaRequest;
use App\Http\Requests\Admin\UpdateCinemaRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CinemaValidationAndSanitizeTest extends TestCase
{
    use DatabaseTransactions;
    protected function getAdmin(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        return User::firstOrCreate([
            'email' => 'admin_cinema_test@moviego.vn'
        ], [
            'name' => 'Admin Cinema Test',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_store_cinema_sanitizes_whitespaces_and_prevents_duplicate_name(): void
    {
        $admin = $this->getAdmin();

        // 1. Tạo rạp ban đầu
        $uniqueSuffix = uniqid();
        $initialName = 'CGV Sư Vạn Hạnh ' . $uniqueSuffix;
        
        $cinema = Cinema::create([
            'name' => $initialName,
            'address' => '11 Sư Vạn Hạnh, Quận 10',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        // 2. Thử tạo rạp mới với tên trùng nhưng có nhiều khoảng trắng thừa ở đầu, giữa và cuối
        $duplicateNameWithSpaces = "   CGV   Sư   Vạn   Hạnh   {$uniqueSuffix}   ";

        $response = $this->actingAs($admin)->post(route('admin.cinemas.store'), [
            'name' => $duplicateNameWithSpaces,
            'address' => '   123   Đường   ABC   ',
            'city' => 'Hồ Chí Minh',
            'phone' => ' 0901234567 ',
            'email' => ' cinema@example.com ',
            'status' => 'ACTIVE',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!'
        ]);

        // 2b. Thử tạo rạp mới với tên thiếu dấu cách giữa các từ (ví dụ: 'CGV Sư VạnHạnh' hoặc 'CGV SưVạn Hạnh')
        $duplicateMissingSpace = "CGV Sư VạnHạnh {$uniqueSuffix}";
        $responseMissingSpace = $this->actingAs($admin)->post(route('admin.cinemas.store'), [
            'name' => $duplicateMissingSpace,
            'address' => '123 Đường XYZ',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        $responseMissingSpace->assertSessionHasErrors([
            'name' => 'Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!'
        ]);

        $duplicateMissingSpace2 = "CGV SưVạn Hạnh {$uniqueSuffix}";
        $responseMissingSpace2 = $this->actingAs($admin)->post(route('admin.cinemas.store'), [
            'name' => $duplicateMissingSpace2,
            'address' => '123 Đường XYZ',
            'city' => 'Hà Nội',
            'status' => 'ACTIVE',
        ]);

        $responseMissingSpace2->assertSessionHasErrors([
            'name' => 'Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!'
        ]);

        // 3. Tạo rạp mới với tên hợp lệ nhưng có khoảng trắng -> kiểm tra được lưu chuẩn hóa
        $newValidName = '   BHD   Star   Bitexco   ' . $uniqueSuffix . '   ';
        $createResponse = $this->actingAs($admin)->post(route('admin.cinemas.store'), [
            'name' => $newValidName,
            'address' => '   2   Hải   Triều,   Quận 1   ',
            'city' => 'Hồ Chí Minh',
            'phone' => '0909999999',
            'email' => 'bhd@example.com',
            'status' => 'ACTIVE',
        ]);

        $createResponse->assertRedirect(route('admin.cinemas.index'));
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('cinemas', [
            'name' => 'BHD Star Bitexco ' . $uniqueSuffix,
            'address' => '2 Hải Triều, Quận 1',
        ]);
    }

    public function test_update_cinema_allows_same_name_but_blocks_duplicate_other_cinema(): void
    {
        $admin = $this->getAdmin();

        $uniqueSuffix = uniqid();
        $cinemaA = Cinema::create([
            'name' => 'Lotte Cinema Gò Vấp ' . $uniqueSuffix,
            'address' => 'Nguyễn Văn Lượng, Gò Vấp',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $cinemaB = Cinema::create([
            'name' => 'Galaxy Cinema Nguyễn Du ' . $uniqueSuffix,
            'address' => '116 Nguyễn Du, Quận 1',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        // Cập nhật Cinema A với cùng tên của chính nó (có thể có khoảng trắng thừa) -> Thành công
        $updateSelfResponse = $this->actingAs($admin)->put(route('admin.cinemas.update', $cinemaA->id), [
            'name' => '   Lotte   Cinema   Gò   Vấp   ' . $uniqueSuffix . '   ',
            'address' => 'Nguyễn Văn Lượng, Gò Vấp',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $updateSelfResponse->assertRedirect(route('admin.cinemas.index'));
        $updateSelfResponse->assertSessionHas('success');

        // Cập nhật Cinema A với tên trùng với Cinema B -> Bị lỗi validation
        $updateDuplicateResponse = $this->actingAs($admin)->put(route('admin.cinemas.update', $cinemaA->id), [
            'name' => '   Galaxy   Cinema   Nguyễn   Du   ' . $uniqueSuffix . '   ',
            'address' => 'Nguyễn Văn Lượng, Gò Vấp',
            'city' => 'Hồ Chí Minh',
            'status' => 'ACTIVE',
        ]);

        $updateDuplicateResponse->assertSessionHasErrors([
            'name' => 'Tên rạp này đã tồn tại trên hệ thống, vui lòng nhập tên khác (Ví dụ: CGV Sư Vạn Hạnh - Hà Nội)!'
        ]);
    }
}
