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
        ];

        // Merge date rules for SCHEDULED status
        [$dateRules, $dateMessages] = $this->getDateRulesAndMessages(Movie::STATUS_SCHEDULED, $data);
        $rules = array_merge($rules, $dateRules);
        $messages = array_merge($messages, $dateMessages);

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate release_date and presale_date according to the movie status.
     *
     * Rules:
     * - SCHEDULED:
     *   release_date: Required, release_date > now().
     *   presale_date: Optional. If present: now() < presale_date AND presale_date <= release_date.
     * - PRE_ORDER:
     *   release_date: Required, release_date > now().
     *   presale_date: Optional. If present: presale_date <= release_date.
     * - COMING_SOON:
     *   release_date: Optional. If provided: release_date > now().
     *   presale_date: Optional. If provided: presale_date <= release_date.
     * - NOW_SHOWING:
     *   release_date: Optional. Allow past dates (release_date <= now()).
     *   presale_date: Ignore / Nullable.
     * - ENDED:
     *   Ignore time validations for release_date and presale_date.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateMovieDatesByStatus(array $data, ?Movie $existingMovie = null): void
    {
        $status = $data['status'] ?? null;

        if ($existingMovie) {
            $this->validateStatusTransition($existingMovie, $status ?? $existingMovie->status);
            $this->validateFieldImmutability($existingMovie, $data);
        }

        [$rules, $messages] = $this->getDateRulesAndMessages($status, $data);

        if (!empty($rules)) {
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    /**
     * Comprehensive validation when updating an existing movie.
     *
     * @param Movie $movie
     * @param array $data
     * @throws ValidationException
     */
    public function validateMovieUpdate(Movie $movie, array $data): void
    {
        $newStatus = $data['status'] ?? $movie->status;

        // 1. Validate status transition constraints
        $this->validateStatusTransition($movie, $newStatus);

        // 2. Validate field immutability when movie has bookings
        $this->validateFieldImmutability($movie, $data);

        // 3. Validate metadata / dates based on new status
        if ($newStatus === Movie::STATUS_SCHEDULED) {
            $this->validateScheduledMetadata($data, $movie);
        } else {
            $this->validateMovieDatesByStatus($data, $movie);
        }
    }

    /**
     * Check if a movie has any active/successful bookings (status = 'SUCCESS' or 'Paid').
     *
     * @param Movie|int $movieOrId
     * @return bool
     */
    public function hasSuccessfulBookings(Movie|int $movieOrId): bool
    {
        $movieId = $movieOrId instanceof Movie ? $movieOrId->id : $movieOrId;

        return DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('showtimes.movie_id', $movieId)
            ->whereIn(DB::raw('UPPER(bookings.status)'), ['SUCCESS', 'PAID'])
            ->exists();
    }

    /**
     * Check if a movie has any historical bookings (regardless of status).
     *
     * @param Movie|int $movieOrId
     * @return bool
     */
    public function hasHistoricalBookings(Movie|int $movieOrId): bool
    {
        $movieId = $movieOrId instanceof Movie ? $movieOrId->id : $movieOrId;

        return DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('showtimes.movie_id', $movieId)
            ->exists();
    }

    /**
     * Validate status transition constraints:
     * - Prevent illegal status jumps.
     * - Disallow changing status back from ENDED to any active status.
     *
     * @param Movie $movie
     * @param string $newStatus
     * @throws ValidationException
     */
    public function validateStatusTransition(Movie $movie, string $newStatus): void
    {
        if ($movie->status === Movie::STATUS_ENDED && $newStatus !== Movie::STATUS_ENDED) {
            throw ValidationException::withMessages([
                'status' => 'Không thể thay đổi trạng thái của phim đã ngừng chiếu (ENDED).',
            ]);
        }

        if ($newStatus === Movie::STATUS_ENDED) {
            $this->validateCanTransitionToEnded($movie);
        }
    }

    /**
     * Validate field immutability:
     * If a movie has active/successful bookings, BLOCK any edits to duration and age_rating.
     *
     * @param Movie $movie
     * @param array $data
     * @throws ValidationException
     */
    public function validateFieldImmutability(Movie $movie, array $data): void
    {
        if (!$this->hasSuccessfulBookings($movie)) {
            return;
        }

        $isDurationChanged = array_key_exists('duration', $data)
            && $data['duration'] !== null
            && (int) $data['duration'] !== (int) $movie->duration;

        $incomingAge = isset($data['age_rating']) ? trim((string) $data['age_rating']) : '';
        $currentAge = trim((string) ($movie->age_rating ?? ''));
        $isAgeRatingChanged = array_key_exists('age_rating', $data) && $incomingAge !== $currentAge;

        if ($isDurationChanged || $isAgeRatingChanged) {
            $errors = [];
            if ($isDurationChanged) {
                $errors['duration'] = 'Không thể thay đổi thời lượng hoặc độ tuổi của phim đã có giao dịch đặt vé.';
            }
            if ($isAgeRatingChanged) {
                $errors['age_rating'] = 'Không thể thay đổi thời lượng hoặc độ tuổi của phim đã có giao dịch đặt vé.';
            }
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Check if a movie has any active bookings (status = 'SUCCESS' or 'Paid')
     * for future showtimes (start_time > now()).
     *
     * @param Movie|int $movieOrId
     * @return bool
     */
    public function hasActiveFutureBookings(Movie|int $movieOrId): bool
    {
        $movieId = $movieOrId instanceof Movie ? $movieOrId->id : $movieOrId;

        return DB::table('bookings')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->where('showtimes.movie_id', $movieId)
            ->whereNull('showtimes.deleted_at')
            ->where('showtimes.start_time', '>', now())
            ->whereIn(DB::raw('UPPER(bookings.status)'), ['SUCCESS', 'PAID'])
            ->exists();
    }

    /**
     * Validate that a movie can be transitioned to 'ENDED' (Ngừng chiếu).
     * Blocks transition if there are active bookings in future showtimes.
     *
     * @param Movie|int $movieOrId
     * @throws ValidationException
     */
    public function validateCanTransitionToEnded(Movie|int $movieOrId): void
    {
        if ($this->hasActiveFutureBookings($movieOrId)) {
            throw ValidationException::withMessages([
                'status' => "Không thể chuyển phim sang 'Ngừng chiếu' vì đang có suất chiếu tương lai đã được đặt vé. Vui lòng hủy các suất chiếu và hoàn tiền cho khách trước.",
            ]);
        }
    }

    /**
     * Automatically update all upcoming showtimes (start_time > now()) for this movie
     * to CANCELLED status when movie status is changed to ENDED.
     *
     * @param Movie|int $movieOrId
     * @return int Number of updated showtimes
     */
    public function cancelUpcomingShowtimes(Movie|int $movieOrId): int
    {
        $movieId = $movieOrId instanceof Movie ? $movieOrId->id : $movieOrId;

        return Showtime::where('movie_id', $movieId)
            ->where('start_time', '>', now())
            ->where('status', '!=', Showtime::STATUS_CANCELLED)
            ->update(['status' => Showtime::STATUS_CANCELLED]);
    }

    /**
     * Automatically update all upcoming showtimes (start_time > now()) with status
     * PENDING or UNPUBLISHED to SCHEDULED when movie switches to PRE_ORDER or NOW_SHOWING.
     *
     * @param Movie|int $movieOrId
     * @return int Number of published showtimes
     */
    public function publishPendingShowtimes(Movie|int $movieOrId): int
    {
        $movieId = $movieOrId instanceof Movie ? $movieOrId->id : $movieOrId;

        return Showtime::where('movie_id', $movieId)
            ->whereIn('status', [Showtime::STATUS_PENDING, Showtime::STATUS_UNPUBLISHED])
            ->where('start_time', '>', now())
            ->update(['status' => Showtime::STATUS_SCHEDULED]);
    }

    /**
     * Helper to get date validation rules and error messages based on status.
     *
     * @param string|null $status
     * @param array $data
     * @return array [rules, messages]
     */
    public function getDateRulesAndMessages(?string $status, array $data): array
    {
        $rules = [];
        $messages = [];

        if ($status === Movie::STATUS_SCHEDULED) {
            $rules['release_date'] = [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && Carbon::parse($value)->lte(now())) {
                        $fail('Ngày phát hành dự kiến (release_date) phải là thời gian trong tương lai.');
                    }
                },
            ];
            $rules['presale_date'] = [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value) {
                        $parsedPresale = Carbon::parse($value);
                        if ($parsedPresale->lte(now())) {
                            $fail('Ngày mở bán sớm (presale_date) phải là thời gian trong tương lai.');
                        }
                        if (!empty($data['release_date']) && $parsedPresale->gt(Carbon::parse($data['release_date']))) {
                            $fail('Ngày mở bán sớm (presale_date) phải trước hoặc bằng ngày phát hành dự kiến.');
                        }
                    }
                },
            ];
            $messages['release_date.required'] = 'Ngày phát hành dự kiến là bắt buộc khi Lên lịch.';
            $messages['release_date.date'] = 'Ngày phát hành dự kiến không đúng định dạng ngày hợp lệ.';
            $messages['presale_date.date'] = 'Ngày mở bán sớm không đúng định dạng ngày hợp lệ.';
        } elseif ($status === Movie::STATUS_PRE_ORDER) {
            $rules['release_date'] = [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && Carbon::parse($value)->lte(now())) {
                        $fail('Ngày phát hành dự kiến (release_date) phải là thời gian trong tương lai.');
                    }
                },
            ];
            $rules['presale_date'] = [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && !empty($data['release_date'])) {
                        if (Carbon::parse($value)->gt(Carbon::parse($data['release_date']))) {
                            $fail('Ngày mở bán sớm (presale_date) phải trước hoặc bằng ngày phát hành dự kiến.');
                        }
                    }
                },
            ];
            $messages['release_date.required'] = 'Ngày phát hành dự kiến là bắt buộc khi Mở bán sớm.';
            $messages['release_date.date'] = 'Ngày phát hành dự kiến không đúng định dạng ngày hợp lệ.';
            $messages['presale_date.date'] = 'Ngày mở bán sớm không đúng định dạng ngày hợp lệ.';
        } elseif ($status === Movie::STATUS_COMING_SOON) {
            $rules['release_date'] = [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && Carbon::parse($value)->lte(now())) {
                        $fail('Ngày phát hành dự kiến (release_date) phải là thời gian trong tương lai.');
                    }
                },
            ];
            $rules['presale_date'] = [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && !empty($data['release_date'])) {
                        if (Carbon::parse($value)->gt(Carbon::parse($data['release_date']))) {
                            $fail('Ngày mở bán sớm (presale_date) phải trước hoặc bằng ngày phát hành dự kiến.');
                        }
                    }
                },
            ];
            $messages['release_date.date'] = 'Ngày phát hành dự kiến không đúng định dạng ngày hợp lệ.';
            $messages['presale_date.date'] = 'Ngày mở bán sớm không đúng định dạng ngày hợp lệ.';
        } elseif ($status === Movie::STATUS_NOW_SHOWING) {
            $rules['release_date'] = ['nullable', 'date'];
            $rules['presale_date'] = ['nullable'];
            $messages['release_date.date'] = 'Ngày phát hành dự kiến không đúng định dạng ngày hợp lệ.';
        } elseif ($status === Movie::STATUS_ENDED) {
            $rules['release_date'] = ['nullable'];
            $rules['presale_date'] = ['nullable'];
        }

        return [$rules, $messages];
    }

    /**
     * Check if a movie is allowed for ticket sales.
     *
     * @param Showtime|int $showtimeOrId
     */
    public function validateTicketSalesAllowed(Showtime|int $showtimeOrId): void
    {
        if ($showtimeOrId instanceof Showtime) {
            $movie = $showtimeOrId->movie ?? Movie::find($showtimeOrId->movie_id);
        } else {
            $showtime = DB::table('showtimes')->where('id', $showtimeOrId)->first();
            $movie = $showtime ? DB::table('movies')->where('id', $showtime->movie_id)->first() : null;
        }

        if ($movie && $movie->status === Movie::STATUS_ENDED) {
            throw new MovieEndedException('Phim đã ngưng chiếu, không thể thực hiện giao dịch đặt vé.');
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

    /**
     * Validate Showtime Status Dropdown & Data Integrity Rules:
     * 1. Terminal State Lock: If existing showtime status is COMPLETED / FINISHED or CANCELLED -> Block edit.
     * 2. Active Booking Protection: If showtime has bookings -> Block changing status to PENDING or CANCELLED.
     * 3. Movie Status Constraint: If movie.status === 'SCHEDULED' -> Force status to PENDING, block SCHEDULED / active statuses.
     * 4. Time-based Rules:
     *    - SCHEDULED or PENDING: Require start_time > now() AND start_time >= movie.release_date
     *    - ONGOING: Require start_time <= now() AND end_time >= now()
     *    - FINISHED / COMPLETED: Require end_time < now()
     *
     * @param Showtime|null $showtime
     * @param array $data
     * @param Movie|null $movie
     * @throws ValidationException
     */
    public function validateShowtimeStatusRules(?Showtime $showtime, array $data, ?Movie $movie = null): void
    {
        $movieId = $data['movie_id'] ?? $showtime?->movie_id;
        if (!$movie && $movieId) {
            $movie = Movie::find($movieId);
        }

        $newStatus = $data['status'] ?? ($showtime?->status ?? Showtime::STATUS_SCHEDULED);
        if (strtoupper($newStatus) === 'FINISHED') {
            $newStatus = Showtime::STATUS_COMPLETED;
        }

        // 1. Terminal State Lock: If existing status is FINISHED/COMPLETED or CANCELLED -> Block any edit
        if ($showtime) {
            $currentStatus = $showtime->status;
            if (in_array($currentStatus, [Showtime::STATUS_COMPLETED, Showtime::STATUS_CANCELLED])) {
                $statusLabel = Showtime::STATUS_LABELS[$currentStatus] ?? $currentStatus;
                $validator = Validator::make([], []);
                $validator->errors()->add(
                    'status',
                    "Suất chiếu đã ở trạng thái kết thúc ({$statusLabel}), không thể chỉnh sửa để bảo toàn dữ liệu lịch sử."
                );
                throw new ValidationException($validator);
            }
        }

        // 2. Active Booking Protection: If showtime has active bookings -> Block changing to PENDING or CANCELLED
        if ($showtime) {
            $hasBookings = $showtime->bookings()
                ->where(function ($q) {
                    $q->whereIn('status', ['Paid', 'SUCCESS', 'Pending', 'Used'])
                      ->orWhere('status', '!=', 'Cancelled');
                })
                ->exists();

            if ($hasBookings) {
                if (in_array($newStatus, [Showtime::STATUS_PENDING, Showtime::STATUS_UNPUBLISHED, Showtime::STATUS_CANCELLED])) {
                    $validator = Validator::make([], []);
                    $validator->errors()->add(
                        'status',
                        'Không thể chuyển suất chiếu đã có vé đặt sang trạng thái Chờ (PENDING) hoặc Đã hủy (CANCELLED). Vui lòng hủy các suất chiếu và hoàn tiền cho khách trước.'
                    );
                    throw new ValidationException($validator);
                }
            }
        }

        // 3. Time-based Rules
        if (!empty($data['start_time'])) {
            $startTime = $data['start_time'] instanceof Carbon ? $data['start_time'] : Carbon::parse($data['start_time']);

            $endTime = null;
            if (!empty($data['end_time'])) {
                $endTime = $data['end_time'] instanceof Carbon ? $data['end_time'] : Carbon::parse($data['end_time']);
            } elseif ($movie && $movie->duration) {
                $bufferMinutes = config('booking.showtime.buffer_minutes', 15);
                $endTime = $startTime->copy()->addMinutes($movie->duration + $bufferMinutes);
            } else {
                $endTime = $startTime->copy()->addHours(2);
            }

            // Rule 4a: SCHEDULED or PENDING -> start_time > now() AND start_time >= movie.release_date
            if (in_array($newStatus, [Showtime::STATUS_SCHEDULED, Showtime::STATUS_PENDING, Showtime::STATUS_UNPUBLISHED])) {
                if ($startTime->lte(now())) {
                    $validator = Validator::make([], []);
                    $validator->errors()->add(
                        'status',
                        'Suất chiếu Lên lịch (SCHEDULED) hoặc Chờ công bố (PENDING) yêu cầu thời gian bắt đầu phải ở tương lai (start_time > hiện tại).'
                    );
                    throw new ValidationException($validator);
                }

                if ($movie) {
                    $earliestAllowed = $movie->presale_date ?? $movie->release_date;
                    if ($earliestAllowed && $startTime->lt($earliestAllowed)) {
                        $validator = Validator::make([], []);
                        if ($movie->presale_date) {
                            $validator->errors()->add(
                                'start_time',
                                "Thời gian suất chiếu ({$startTime->format('d/m/Y H:i')}) không được sớm hơn Ngày mở bán sớm của phim ({$movie->presale_date->format('d/m/Y H:i')})."
                            );
                        } else {
                            $validator->errors()->add(
                                'start_time',
                                "Thời gian suất chiếu ({$startTime->format('d/m/Y H:i')}) không được sớm hơn Ngày công chiếu của phim ({$movie->release_date->format('d/m/Y H:i')})."
                            );
                        }
                        throw new ValidationException($validator);
                    }
                }
            }

            // Rule 4b: ONGOING -> start_time <= now() AND end_time >= now()
            if ($newStatus === Showtime::STATUS_ONGOING) {
                if ($startTime->gt(now()) || $endTime->lt(now())) {
                    $validator = Validator::make([], []);
                    $validator->errors()->add(
                        'status',
                        "Suất chiếu 'Đang chiếu' (ONGOING) yêu cầu thời gian bắt đầu <= hiện tại và thời gian kết thúc >= hiện tại."
                    );
                    throw new ValidationException($validator);
                }
            }

            // Rule 4c: FINISHED / COMPLETED -> end_time < now()
            if ($newStatus === Showtime::STATUS_COMPLETED) {
                if ($endTime->gte(now())) {
                    $validator = Validator::make([], []);
                    $validator->errors()->add(
                        'status',
                        "Suất chiếu 'Đã chiếu' (FINISHED/COMPLETED) yêu cầu thời gian kết thúc phải trong quá khứ (end_time < hiện tại)."
                    );
                    throw new ValidationException($validator);
                }
            }
        }
    }
}

