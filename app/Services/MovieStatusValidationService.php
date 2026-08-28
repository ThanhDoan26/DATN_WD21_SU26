<?php

namespace App\Services;

use App\Exceptions\MovieScheduledException;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MovieStatusValidationService
{
    /**
     * Validate completeness of movie metadata when status is 'SCHEDULED' (Lên lịch).
     *
     * Required metadata:
     * - Title
     * - Poster (file upload or existing poster_url)
     * - Trailer URL (valid URL)
     * - Duration (minutes > 0)
     * - Age Rating (P, K, T13, T16, T18, etc.)
     * - Genre / Categories (at least 1 category)
     * - Expected Release Date (release_date > CURRENT_TIMESTAMP)
     *
     * @param array $data
     * @param Movie|null $existingMovie
     * @return array
     * @throws ValidationException
     */
    public function validateScheduledMetadata(array $data, ?Movie $existingMovie = null): array
    {
        $hasExistingPoster = $existingMovie && !empty($existingMovie->poster_url);
        $hasPosterInInput = !empty($data['poster']) || !empty($data['poster_url']);

        $rules = [
            'title' => 'required|string|max:255',
            'trailer_url' => 'required|url|max:255',
            'duration' => 'required|integer|min:1|max:500',
            'age_rating' => 'required|string|max:50',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'release_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && Carbon::parse($value)->lte(now())) {
                        $fail('Ngày phát hành dự kiến (release_date) phải là thời gian trong tương lai.');
                    }
                },
            ],
            'presale_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && !empty($data['release_date'])) {
                        if (Carbon::parse($value)->gt(Carbon::parse($data['release_date']))) {
                            $fail('Ngày mở bán sớm (presale_date) phải trước hoặc bằng ngày phát hành.');
                        }
                    }
                },
            ],
        ];

        // Poster validation
        if (!$hasExistingPoster && !$hasPosterInInput) {
            $rules['poster'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['poster'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        $messages = [
            'title.required' => 'Tên phim là bắt buộc khi lên lịch chiếu.',
            'poster.required' => 'Poster phim là bắt buộc khi lên lịch chiếu.',
            'trailer_url.required' => 'Trailer URL là bắt buộc khi lên lịch chiếu.',
            'trailer_url.url' => 'Trailer URL không đúng định dạng đường dẫn hợp lệ.',
            'duration.required' => 'Thời lượng phim là bắt buộc khi lên lịch chiếu.',
            'duration.integer' => 'Thời lượng phim phải là số nguyên.',
            'duration.min' => 'Thời lượng phim tối thiểu phải từ 1 phút.',
            'age_rating.required' => 'Độ tuổi giới hạn (Age Rating) là bắt buộc khi lên lịch chiếu.',
            'categories.required' => 'Thể loại phim (Genre) là bắt buộc khi lên lịch chiếu.',
            'categories.min' => 'Vui lòng chọn ít nhất một thể loại phim.',
            'release_date.required' => 'Ngày phát hành dự kiến (release_date) là bắt buộc khi lên lịch chiếu.',
            'release_date.date' => 'Ngày phát hành dự kiến không hợp lệ.',
            'presale_date.date' => 'Ngày mở bán sớm không hợp lệ.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Check if a movie is allowed for ticket sales.
     * Blocks all booking attempts if the movie status is 'SCHEDULED'.
     *
     * @param Showtime|int $showtimeOrId
     * @throws MovieScheduledException
     */
    public function validateTicketSalesAllowed(Showtime|int $showtimeOrId): void
    {
        if ($showtimeOrId instanceof Showtime) {
            $movie = $showtimeOrId->movie ?? Movie::find($showtimeOrId->movie_id);
        } else {
            $showtime = DB::table('showtimes')->where('id', $showtimeOrId)->first();
            $movie = $showtime ? DB::table('movies')->where('id', $showtime->movie_id)->first() : null;
        }

        if ($movie && $movie->status === Movie::STATUS_SCHEDULED) {
            throw new MovieScheduledException('Movie is currently scheduled and not yet open for ticket sales.');
        }
    }

    /**
     * Automatic Status Transition Logic:
     * - If presale_date exists and CURRENT_TIMESTAMP >= presale_date (and CURRENT_TIMESTAMP < release_date) -> 'PRE_ORDER'
     * - Else if CURRENT_TIMESTAMP >= release_date -> 'NOW_SHOWING'
     * - Automatically publish pending showtimes to 'SCHEDULED'
     *
     * @return int Number of updated movies
     */
    public function syncMovieStatuses(): int
    {
        return Movie::syncAllStatuses();
    }

    /**
     * Validate that a showtime's start_time is in the future and not earlier than the movie's
     * release_date (unless a valid presale_date allows early sneak shows).
     *
     * @param int|null $movieId
     * @param mixed $startTime
     * @param \Closure $fail
     */
    public function validateShowtimeStartTime(?int $movieId, mixed $startTime, \Closure $fail): void
    {
        if (!$startTime) {
            return;
        }

        $parsedStart = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);

        if ($parsedStart->lt(now())) {
            $fail('Không thể lên lịch chiếu cho thời gian đã qua. Thời gian bắt đầu phải từ thời điểm hiện tại trở đi.');
            return;
        }

        if ($movieId) {
            $movie = Movie::find($movieId);
            if ($movie) {
                $earliestAllowed = $movie->presale_date ?? $movie->release_date;

                if ($earliestAllowed && $parsedStart->lt($earliestAllowed)) {
                    if ($movie->presale_date) {
                        $fail("Thời gian suất chiếu ({$parsedStart->format('d/m/Y H:i')}) không được sớm hơn Ngày mở bán sớm của phim ({$movie->presale_date->format('d/m/Y H:i')}).");
                    } else {
                        $fail("Thời gian suất chiếu ({$parsedStart->format('d/m/Y H:i')}) không được trước Ngày khởi chiếu chính thức ({$movie->release_date->format('d/m/Y H:i')}). Nếu muốn tạo suất chiếu sớm (Sneak Show), vui lòng cài đặt Ngày mở bán sớm (presale_date) cho phim.");
                    }
                }
            }
        }
    }
}

