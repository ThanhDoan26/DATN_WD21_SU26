<?php

use App\Models\Category;
use App\Models\Movie;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Movie Form Validation by Status Rules', function () {

    function getAdmin(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'ADMIN'], ['description' => 'Administrator']);
        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'ACTIVE',
        ]);
    }

    beforeEach(function () {
        Storage::fake('public');
    });

    test('SCHEDULED status requires future release_date and valid future presale_date <= release_date', function () {
        $admin = getAdmin();
        $unique = uniqid();
        $cat = Category::create(['name' => 'Action ' . $unique, 'slug' => 'action-' . $unique]);

        // 1. Missing release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Scheduled Movie 1',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'age_rating' => 'T16',
            'trailer_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'categories' => [$cat->id],
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'release_date' => null,
        ]);
        $response->assertSessionHasErrors('release_date');

        // 2. Past release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Scheduled Movie 2',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'age_rating' => 'T16',
            'trailer_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'categories' => [$cat->id],
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'release_date' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('release_date');

        // 3. Past presale_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Scheduled Movie 3',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'age_rating' => 'T16',
            'trailer_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'categories' => [$cat->id],
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'release_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'presale_date' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('presale_date');

        // 4. presale_date > release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Scheduled Movie 4',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'age_rating' => 'T16',
            'trailer_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'categories' => [$cat->id],
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'release_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'presale_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('presale_date');

        // 5. Valid future dates -> Success
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Scheduled Movie Valid',
            'status' => Movie::STATUS_SCHEDULED,
            'duration' => 120,
            'age_rating' => 'T16',
            'trailer_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'categories' => [$cat->id],
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'release_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'presale_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.movies.index'));
    });

    test('PRE_ORDER status requires future release_date and presale_date <= release_date', function () {
        $admin = getAdmin();

        // 1. Missing release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Pre Order Movie 1',
            'status' => Movie::STATUS_PRE_ORDER,
            'duration' => 120,
            'release_date' => null,
        ]);
        $response->assertSessionHasErrors('release_date');

        // 2. Past release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Pre Order Movie 2',
            'status' => Movie::STATUS_PRE_ORDER,
            'duration' => 120,
            'release_date' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('release_date');

        // 3. presale_date > release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Pre Order Movie 3',
            'status' => Movie::STATUS_PRE_ORDER,
            'duration' => 120,
            'release_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'presale_date' => now()->addDays(4)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('presale_date');

        // 4. Valid future release_date without presale -> Success
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Pre Order Movie Valid',
            'status' => Movie::STATUS_PRE_ORDER,
            'duration' => 120,
            'release_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();
    });

    test('COMING_SOON status allows optional release_date (must be future if provided)', function () {
        $admin = getAdmin();

        // 1. Missing release_date -> Allowed
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Coming Soon Movie 1',
            'status' => Movie::STATUS_COMING_SOON,
            'duration' => 120,
            'release_date' => null,
        ]);
        $response->assertSessionHasNoErrors();

        // 2. Past release_date -> Fail
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Coming Soon Movie 2',
            'status' => Movie::STATUS_COMING_SOON,
            'duration' => 120,
            'release_date' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasErrors('release_date');

        // 3. Future release_date -> Success
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Coming Soon Movie 3',
            'status' => Movie::STATUS_COMING_SOON,
            'duration' => 120,
            'release_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();
    });

    test('NOW_SHOWING status allows past release_date and ignores presale_date', function () {
        $admin = getAdmin();

        // 1. Past release_date -> Allowed
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Now Showing Past Date Movie',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'release_date' => now()->subDays(20)->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();

        // 2. Null release_date -> Allowed
        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Now Showing Null Date Movie',
            'status' => Movie::STATUS_NOW_SHOWING,
            'duration' => 120,
            'release_date' => null,
        ]);
        $response->assertSessionHasNoErrors();
    });

    test('ENDED status ignores time validations for release_date', function () {
        $admin = getAdmin();

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Ended Movie',
            'status' => Movie::STATUS_ENDED,
            'duration' => 120,
            'release_date' => now()->subYear()->format('Y-m-d H:i:s'),
        ]);
        $response->assertSessionHasNoErrors();
    });
});
