<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Movie;
use Illuminate\Support\Str;

class MovieAndCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách 10 thể loại phim
        $categoryNames = [
            'Hành động', 'Viễn tưởng', 'Kinh dị', 'Hài hước', 'Tâm lý',
            'Tình cảm', 'Phiêu lưu', 'Hoạt hình', 'Tài liệu', 'Chiến tranh'
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Mô tả cho thể loại ' . $name,
            ]);
        }

        // Tạo 20 bộ phim
        $statuses = ['COMING_SOON', 'NOW_SHOWING', 'ENDED'];
        $ageRatings = ['P', 'K', 'T13', 'T16', 'T18'];

        for ($i = 1; $i <= 20; $i++) {
            $movie = Movie::create([
                'title' => 'Phim Mẫu Số ' . $i,
                'description' => 'Đây là nội dung mô tả ngẫu nhiên cho bộ phim số ' . $i . '. Phim xoay quanh câu chuyện kịch tính và hấp dẫn.',
                'director' => 'Đạo Diễn ' . rand(1, 5),
                'cast' => 'Diễn Viên A, Diễn Viên B, Diễn Viên C',
                'duration' => rand(90, 180),
                'age_rating' => $ageRatings[array_rand($ageRatings)],
                'status' => $statuses[array_rand($statuses)],
                'language' => 'Tiếng Việt',
                'country' => 'Việt Nam',
                'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'poster_url' => null, // Hoặc một URL ảnh placeholder nếu cần
            ]);

            // Gán ngẫu nhiên 1 đến 3 thể loại cho mỗi phim
            $randomCategoryIds = collect($categories)->random(rand(1, 3))->pluck('id');
            $movie->categories()->attach($randomCategoryIds);
        }
    }
}
