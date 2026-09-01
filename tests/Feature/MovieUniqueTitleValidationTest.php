<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Movie;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MovieUniqueTitleValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected function getAdmin(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        return User::firstOrCreate([
            'email' => 'admin_movie_test@moviego.vn'
        ], [
            'name' => 'Admin Movie Test',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_store_movie_sanitizes_title_and_blocks_duplicate_title(): void
    {
        $admin = $this->getAdmin();
        $uniqueSuffix = uniqid();

        $category = Category::firstOrCreate(['name' => 'Action ' . $uniqueSuffix, 'slug' => 'action-' . $uniqueSuffix]);

        // 1. Tạo phim ban đầu
        $initialTitle = 'Spider-Man Beyond ' . $uniqueSuffix;
        Movie::create([
            'title' => $initialTitle,
            'duration' => 120,
            'status' => Movie::STATUS_COMING_SOON,
        ]);

        // 2. Thử tạo phim mới trùng tên chính xác
        $exactDuplicateResponse = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => $initialTitle,
            'duration' => 130,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $exactDuplicateResponse->assertSessionHasErrors([
            'title' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!'
        ]);

        // 3. Thử tạo phim mới trùng tên nhưng có nhiều khoảng trắng thừa ở đầu, giữa và cuối
        $spacesDuplicateResponse = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => "   Spider-Man   Beyond   {$uniqueSuffix}   ",
            'duration' => 130,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $spacesDuplicateResponse->assertSessionHasErrors([
            'title' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!'
        ]);

        // 4. Thử tạo phim mới trùng tên nhưng thiếu dấu cách giữa các từ
        $missingSpaceDuplicateResponse = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => "Spider-ManBeyond {$uniqueSuffix}",
            'duration' => 130,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $missingSpaceDuplicateResponse->assertSessionHasErrors([
            'title' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!'
        ]);

        // 5. Tạo phim mới với tên hợp lệ có khoảng trắng thừa -> Lưu vào DB chuẩn hóa
        $newValidTitle = "   Venom   The   Last   Dance   {$uniqueSuffix}   ";
        $createResponse = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => $newValidTitle,
            'duration' => 110,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $createResponse->assertRedirect(route('admin.movies.index'));
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('movies', [
            'title' => "Venom The Last Dance {$uniqueSuffix}",
            'duration' => 110,
        ]);
    }

    public function test_update_movie_allows_same_title_but_blocks_duplicate_other_movie(): void
    {
        $admin = $this->getAdmin();
        $uniqueSuffix = uniqid();
        $category = Category::firstOrCreate(['name' => 'SciFi ' . $uniqueSuffix, 'slug' => 'scifi-' . $uniqueSuffix]);

        $movieA = Movie::create([
            'title' => 'The Matrix Resurrections ' . $uniqueSuffix,
            'duration' => 148,
            'status' => Movie::STATUS_COMING_SOON,
        ]);

        $movieB = Movie::create([
            'title' => 'Interstellar Journey ' . $uniqueSuffix,
            'duration' => 169,
            'status' => Movie::STATUS_COMING_SOON,
        ]);

        // 1. Cập nhật Movie A giữ nguyên tên của chính nó (có thể có khoảng trắng thừa) -> Thành công
        $updateSelfResponse = $this->actingAs($admin)->put(route('admin.movies.update', $movieA->id), [
            'title' => "   The   Matrix   Resurrections   {$uniqueSuffix}   ",
            'duration' => 150,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $updateSelfResponse->assertRedirect(route('admin.movies.show', $movieA->id));
        $updateSelfResponse->assertSessionHas('success');

        // 2. Cập nhật Movie A trùng tên với Movie B -> Bị chặn lỗi validation
        $updateDuplicateResponse = $this->actingAs($admin)->put(route('admin.movies.update', $movieA->id), [
            'title' => "   Interstellar   Journey   {$uniqueSuffix}   ",
            'duration' => 150,
            'status' => Movie::STATUS_COMING_SOON,
            'categories' => [$category->id],
        ]);

        $updateDuplicateResponse->assertSessionHasErrors([
            'title' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!'
        ]);
    }
}
