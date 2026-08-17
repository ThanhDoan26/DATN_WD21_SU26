<?php

namespace App\Rules;

use App\Models\Movie;
use App\Models\Room;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * CompatibleFormatRule
 * ========================================
 * Kiểm tra tính tương thích giữa định dạng Phim và định dạng Phòng chiếu.
 *
 * Business Rule:
 * - Phim 2D có thể chiếu ở mọi loại phòng (2D, 3D, 4DX, 5D, IMAX).
 * - Phim 3D chỉ chiếu được ở phòng 3D, 4DX, 5D.
 * - Phim 4DX chỉ chiếu được ở phòng 4DX, 5D.
 * - Phim 5D chỉ chiếu được ở phòng 5D.
 * - Phim IMAX chỉ chiếu được ở phòng IMAX.
 *
 * Rule này được gắn vào field `room_id` trong validation
 * và nhận `movie_id` từ request để so sánh.
 */
class CompatibleFormatRule implements ValidationRule
{
    /**
     * Compatibility Matrix: Movie Base Format → danh sách Room Base Format được phép.
     */
    private const COMPATIBILITY_MATRIX = [
        '2D'   => ['2D', '3D', '4DX', '5D', 'IMAX'],
        '3D'   => ['3D', '4DX', '5D'],
        '4DX'  => ['4DX', '5D'],
        '5D'   => ['5D'],
        'IMAX' => ['IMAX'],
    ];

    protected ?int $movieId;

    /**
     * @param mixed $movieId  ID của phim (lấy từ request)
     */
    public function __construct(mixed $movieId)
    {
        $this->movieId = $movieId ? (int) $movieId : null;
    }

    /**
     * Validate: Kiểm tra room_id có tương thích với movie_id không.
     *
     * @param string  $attribute  Tên field đang validate (room_id)
     * @param mixed   $value      Giá trị field đang validate (room_id value)
     * @param Closure $fail       Callback để báo lỗi
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nếu thiếu movie_id hoặc room_id, bỏ qua — để rule 'required'/'exists' xử lý.
        if (! $this->movieId || ! $value) {
            return;
        }

        $movie = Movie::find($this->movieId);
        $room  = Room::find($value);

        // Nếu movie/room không tồn tại, bỏ qua — để rule 'exists' xử lý.
        if (! $movie || ! $room) {
            return;
        }

        $movieFormats = $movie->format; // array (via accessor trong Movie model)
        $roomFormat   = $room->format;  // string

        // Nếu không có dữ liệu format, bỏ qua validation.
        if (empty($movieFormats) || empty($roomFormat)) {
            return;
        }

        $roomBaseFormat = self::normalizeFormat($roomFormat);

        // Kiểm tra: Ít nhất 1 format của phim phải tương thích với phòng chiếu.
        foreach ($movieFormats as $fmt) {
            $movieBaseFormat    = self::normalizeFormat($fmt);
            $allowedRoomFormats = self::COMPATIBILITY_MATRIX[$movieBaseFormat] ?? [];

            if (in_array($roomBaseFormat, $allowedRoomFormats, true)) {
                return; // Có ít nhất 1 format tương thích → hợp lệ
            }
        }

        // Không có format nào tương thích → reject.
        $movieTitle       = $movie->title;
        $movieFormatStr   = implode(', ', $movieFormats);
        $roomName         = $room->name;
        $roomFormatDisplay = $room->format;

        $fail(
            "Phim \"{$movieTitle}\" (Định dạng: {$movieFormatStr}) không thể chiếu tại phòng \"{$roomName}\" (Định dạng: {$roomFormatDisplay}). " .
            "Vui lòng chọn phòng có định dạng tương thích."
        );
    }

    /**
     * Normalize format string về Base Format.
     *
     * Ví dụ:
     * - "2D Phụ Đề"     → "2D"
     * - "2D Lồng Tiếng" → "2D"
     * - "3D Lồng Tiếng" → "3D"
     * - "3D"             → "3D"
     * - "4DX"            → "4DX"
     * - "4D"             → "4DX"
     * - "5D"             → "5D"
     * - "Imax"           → "IMAX"
     * - "IMAX"           → "IMAX"
     */
    public static function normalizeFormat(string $format): string
    {
        $upper = mb_strtoupper(trim($format));

        if (str_contains($upper, 'IMAX')) {
            return 'IMAX';
        }

        if (str_contains($upper, '5D')) {
            return '5D';
        }

        if (str_contains($upper, '4D')) {
            return '4DX';
        }

        if (str_contains($upper, '3D')) {
            return '3D';
        }

        return '2D';
    }
}
