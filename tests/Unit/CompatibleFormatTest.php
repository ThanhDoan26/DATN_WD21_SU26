<?php

use App\Rules\CompatibleFormatRule;

// ========================================
// UNIT TESTS: normalizeFormat()
// ========================================

it('normalizes "2D" to "2D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('2D'))->toBe('2D')
);

it('normalizes "2D Phụ Đề" to "2D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('2D Phụ Đề'))->toBe('2D')
);

it('normalizes "2D Lồng Tiếng" to "2D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('2D Lồng Tiếng'))->toBe('2D')
);

it('normalizes "3D" to "3D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('3D'))->toBe('3D')
);

it('normalizes "3D Lồng Tiếng" to "3D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('3D Lồng Tiếng'))->toBe('3D')
);

it('normalizes "4DX" to "4DX"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('4DX'))->toBe('4DX')
);

it('normalizes "4D" to "4DX"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('4D'))->toBe('4DX')
);

it('normalizes "5D" to "5D"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('5D'))->toBe('5D')
);

it('normalizes "IMAX" to "IMAX"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('IMAX'))->toBe('IMAX')
);

it('normalizes "Imax" (mixed case) to "IMAX"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('Imax'))->toBe('IMAX')
);

it('normalizes "imax" (all lowercase) to "IMAX"', fn () =>
    expect(CompatibleFormatRule::normalizeFormat('imax'))->toBe('IMAX')
);

// ========================================
// COMPATIBILITY MATRIX: 16 combinations cơ bản
// ========================================

$matrixCases = [
    ['2D',   '2D',   true],
    ['2D',   '3D',   true],
    ['2D',   '4DX',  true],
    ['2D',   'IMAX', true],

    ['3D',   '2D',   false],
    ['3D',   '3D',   true],
    ['3D',   '4DX',  true],
    ['3D',   'IMAX', false],

    ['4DX',  '2D',   false],
    ['4DX',  '3D',   false],
    ['4DX',  '4DX',  true],
    ['4DX',  'IMAX', false],

    ['IMAX', '2D',   false],
    ['IMAX', '3D',   false],
    ['IMAX', '4DX',  false],
    ['IMAX', 'IMAX', true],
];

foreach ($matrixCases as [$movieFormat, $roomFormat, $expected]) {
    $label = $expected ? 'VALID' : 'INVALID';
    it("matrix: Movie {$movieFormat} → Room {$roomFormat} = {$label}", function () use ($movieFormat, $roomFormat, $expected) {
        $reflection = new ReflectionClass(CompatibleFormatRule::class);
        $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

        $movieBase = CompatibleFormatRule::normalizeFormat($movieFormat);
        $roomBase  = CompatibleFormatRule::normalizeFormat($roomFormat);

        $allowedRooms = $matrix[$movieBase] ?? [];
        $result = in_array($roomBase, $allowedRooms, true);

        expect($result)->toBe($expected);
    });
}

// ========================================
// EXTENDED MATRIX: Phòng 5D
// ========================================

$extendedCases = [
    ['2D',   '5D', true],
    ['3D',   '5D', true],
    ['4DX',  '5D', true],
    ['5D',   '5D', true],
    ['IMAX', '5D', false],
    ['5D',   '2D', false],
    ['5D',   '3D', false],
    ['5D',   '4DX', false],
    ['5D',   'IMAX', false],
];

foreach ($extendedCases as [$movieFormat, $roomFormat, $expected]) {
    $label = $expected ? 'VALID' : 'INVALID';
    it("matrix: Movie {$movieFormat} → Room {$roomFormat} = {$label}", function () use ($movieFormat, $roomFormat, $expected) {
        $reflection = new ReflectionClass(CompatibleFormatRule::class);
        $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

        $movieBase = CompatibleFormatRule::normalizeFormat($movieFormat);
        $roomBase  = CompatibleFormatRule::normalizeFormat($roomFormat);

        $allowedRooms = $matrix[$movieBase] ?? [];
        $result = in_array($roomBase, $allowedRooms, true);

        expect($result)->toBe($expected);
    });
}

// ========================================
// NORMALIZED FORMAT (edge cases thực tế từ DB)
// ========================================

it('matrix: Movie "2D Phụ Đề" → Room "3D" = VALID (base = 2D)', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieBase = CompatibleFormatRule::normalizeFormat('2D Phụ Đề');
    $roomBase  = CompatibleFormatRule::normalizeFormat('3D');

    expect($movieBase)->toBe('2D');
    expect(in_array($roomBase, $matrix[$movieBase], true))->toBeTrue();
});

it('matrix: Movie "3D Lồng Tiếng" → Room "2D" = INVALID (base = 3D)', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieBase = CompatibleFormatRule::normalizeFormat('3D Lồng Tiếng');
    $roomBase  = CompatibleFormatRule::normalizeFormat('2D');

    expect($movieBase)->toBe('3D');
    expect(in_array($roomBase, $matrix[$movieBase], true))->toBeFalse();
});

it('matrix: Movie "3D Lồng Tiếng" → Room "4DX" = VALID', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieBase = CompatibleFormatRule::normalizeFormat('3D Lồng Tiếng');
    $roomBase  = CompatibleFormatRule::normalizeFormat('4DX');

    expect(in_array($roomBase, $matrix[$movieBase], true))->toBeTrue();
});

it('matrix: Room "Imax" (mixed case) normalizes and matches IMAX movie', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $roomBase = CompatibleFormatRule::normalizeFormat('Imax');
    expect($roomBase)->toBe('IMAX');

    // IMAX Movie → Imax Room = VALID
    expect(in_array($roomBase, $matrix['IMAX'], true))->toBeTrue();
    // 3D Movie → Imax Room = INVALID
    expect(in_array($roomBase, $matrix['3D'], true))->toBeFalse();
});

// ========================================
// EDGE CASES: Rule skips khi thiếu dữ liệu
// ========================================

it('rule skips validation when movie_id is null', function () {
    $rule = new CompatibleFormatRule(null);
    $passed = true;

    $rule->validate('room_id', 1, function () use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeTrue();
});

it('rule skips validation when room_id value is null', function () {
    $rule = new CompatibleFormatRule(999);
    $passed = true;

    $rule->validate('room_id', null, function () use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeTrue();
});

// ========================================
// MULTI-FORMAT MOVIE: Logic thuần
// ========================================

it('multi-format: ["2D", "3D"] → Room "2D" = VALID (2D matches)', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieFormats = ['2D', '3D'];
    $roomBase = CompatibleFormatRule::normalizeFormat('2D');

    $compatible = false;
    foreach ($movieFormats as $fmt) {
        $movieBase = CompatibleFormatRule::normalizeFormat($fmt);
        if (in_array($roomBase, $matrix[$movieBase] ?? [], true)) {
            $compatible = true;
            break;
        }
    }

    expect($compatible)->toBeTrue();
});

it('multi-format: ["IMAX"] → Room "3D" = INVALID', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieFormats = ['IMAX'];
    $roomBase = CompatibleFormatRule::normalizeFormat('3D');

    $compatible = false;
    foreach ($movieFormats as $fmt) {
        $movieBase = CompatibleFormatRule::normalizeFormat($fmt);
        if (in_array($roomBase, $matrix[$movieBase] ?? [], true)) {
            $compatible = true;
            break;
        }
    }

    expect($compatible)->toBeFalse();
});

it('multi-format: ["3D", "IMAX"] → Room "3D" = VALID (3D matches)', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieFormats = ['3D', 'IMAX'];
    $roomBase = CompatibleFormatRule::normalizeFormat('3D');

    $compatible = false;
    foreach ($movieFormats as $fmt) {
        $movieBase = CompatibleFormatRule::normalizeFormat($fmt);
        if (in_array($roomBase, $matrix[$movieBase] ?? [], true)) {
            $compatible = true;
            break;
        }
    }

    expect($compatible)->toBeTrue();
});

it('multi-format: ["4DX", "IMAX"] → Room "2D" = INVALID (neither matches)', function () {
    $reflection = new ReflectionClass(CompatibleFormatRule::class);
    $matrix = $reflection->getConstant('COMPATIBILITY_MATRIX');

    $movieFormats = ['4DX', 'IMAX'];
    $roomBase = CompatibleFormatRule::normalizeFormat('2D');

    $compatible = false;
    foreach ($movieFormats as $fmt) {
        $movieBase = CompatibleFormatRule::normalizeFormat($fmt);
        if (in_array($roomBase, $matrix[$movieBase] ?? [], true)) {
            $compatible = true;
            break;
        }
    }

    expect($compatible)->toBeFalse();
});
