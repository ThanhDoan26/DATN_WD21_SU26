<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/**
 * Seeder tạo dữ liệu mẫu Coupon để test chức năng Kiểm Tra Phiếu Giảm Giá của Staff.
 *
 * Chạy lệnh: php artisan db:seed --class=CouponTestSeeder
 *
 * Danh sách mã tạo ra:
 * ─────────────────────────────────────────────────────────────────
 * HỢP LỆ (VALID):
 *   TEST_PERCENT10   — Giảm 10%, không giới hạn đơn tối thiểu, không giới hạn lượt
 *   TEST_FIXED50K    — Giảm 50.000₫ cố định, đơn tối thiểu 100.000₫, còn lượt
 *   TEST_NEWUSER     — Giảm 20%, đơn tối thiểu 200.000₫, giảm tối đa 100.000₫
 *   TEST_NOLIMIT     — Giảm 5%, không ngày, không lượt giới hạn
 *
 * CẦN KIỂM THÊM (WARNING — có min_order_value nhưng chưa nhập giá trị đơn):
 *   TEST_VIP         — Giảm 30%, đơn tối thiểu 500.000₫ (dùng kèm giá trị đơn để kiểm tra)
 *
 * KHÔNG HỢP LỆ (INVALID — để test từng lý do):
 *   TEST_EXPIRED     — Đã hết hạn (end_date quá khứ)
 *   TEST_NOTYET      — Chưa đến ngày dùng (start_date tương lai)
 *   TEST_INACTIVE    — Bị khoá (status = INACTIVE)
 *   TEST_OUTOFSTOCK  — Hết lượt sử dụng (used_count = quantity)
 *   TEST_DELETED     — Đã bị xoá (soft deleted)
 * ─────────────────────────────────────────────────────────────────
 */
class CouponTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎫 Đang tạo dữ liệu mẫu Coupon để test...');

        $now = now();

        $coupons = [
            // ── HỢP LỆ ───────────────────────────────────────────────────
            [
                'code'                => 'TEST_PERCENT10',
                'type'                => 'PERCENT',
                'value'               => 10,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 0,        // 0 = không giới hạn
                'used_count'          => 0,
                'start_date'          => $now->copy()->subDays(7),
                'end_date'            => $now->copy()->addDays(30),
                'status'              => 'ACTIVE',
                '_note'               => '✅ Hợp lệ — Giảm 10%, không giới hạn đơn & lượt',
            ],
            [
                'code'                => 'TEST_FIXED50K',
                'type'                => 'FIXED',
                'value'               => 50000,
                'min_order_value'     => 100000,
                'max_discount_amount' => null,
                'quantity'            => 100,
                'used_count'          => 42,
                'start_date'          => $now->copy()->subDays(3),
                'end_date'            => $now->copy()->addDays(14),
                'status'              => 'ACTIVE',
                '_note'               => '✅ Hợp lệ — Giảm 50.000₫, đơn tối thiểu 100.000₫, còn 58 lượt',
            ],
            [
                'code'                => 'TEST_NEWUSER',
                'type'                => 'PERCENT',
                'value'               => 20,
                'min_order_value'     => 200000,
                'max_discount_amount' => 100000,
                'quantity'            => 500,
                'used_count'          => 123,
                'start_date'          => $now->copy()->subDays(10),
                'end_date'            => $now->copy()->addDays(60),
                'status'              => 'ACTIVE',
                '_note'               => '✅ Hợp lệ — Giảm 20% (tối đa 100k), đơn tối thiểu 200k',
            ],
            [
                'code'                => 'TEST_NOLIMIT',
                'type'                => 'PERCENT',
                'value'               => 5,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 0,
                'used_count'          => 0,
                'start_date'          => null,      // Không giới hạn ngày
                'end_date'            => null,
                'status'              => 'ACTIVE',
                '_note'               => '✅ Hợp lệ — Giảm 5%, không ngày, không lượt giới hạn',
            ],
            // ── CẦN KIỂM THÊM (đơn tối thiểu cao) ───────────────────────
            [
                'code'                => 'TEST_VIP',
                'type'                => 'PERCENT',
                'value'               => 30,
                'min_order_value'     => 500000,
                'max_discount_amount' => 200000,
                'quantity'            => 50,
                'used_count'          => 5,
                'start_date'          => $now->copy()->subDays(1),
                'end_date'            => $now->copy()->addDays(7),
                'status'              => 'ACTIVE',
                '_note'               => '⚠️ Cần nhập giá trị đơn — Giảm 30% (tối đa 200k), đơn tối thiểu 500k',
            ],
            // ── KHÔNG HỢP LỆ ─────────────────────────────────────────────
            [
                'code'                => 'TEST_EXPIRED',
                'type'                => 'PERCENT',
                'value'               => 15,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 0,
                'used_count'          => 88,
                'start_date'          => $now->copy()->subDays(30),
                'end_date'            => $now->copy()->subDays(3),   // ĐÃ HẾT HẠN
                'status'              => 'ACTIVE',
                '_note'               => '❌ Đã hết hạn — end_date trong quá khứ',
            ],
            [
                'code'                => 'TEST_NOTYET',
                'type'                => 'FIXED',
                'value'               => 75000,
                'min_order_value'     => 150000,
                'max_discount_amount' => null,
                'quantity'            => 200,
                'used_count'          => 0,
                'start_date'          => $now->copy()->addDays(5),   // CHƯA ĐẾN NGÀY
                'end_date'            => $now->copy()->addDays(30),
                'status'              => 'ACTIVE',
                '_note'               => '❌ Chưa đến ngày — start_date sau hôm nay 5 ngày',
            ],
            [
                'code'                => 'TEST_INACTIVE',
                'type'                => 'PERCENT',
                'value'               => 25,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 0,
                'used_count'          => 0,
                'start_date'          => $now->copy()->subDays(5),
                'end_date'            => $now->copy()->addDays(25),
                'status'              => 'INACTIVE',                  // BỊ KHOÁ
                '_note'               => '❌ Bị khoá — status = INACTIVE',
            ],
            [
                'code'                => 'TEST_OUTOFSTOCK',
                'type'                => 'FIXED',
                'value'               => 30000,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 10,
                'used_count'          => 10,                          // HẾT LƯỢT
                'start_date'          => $now->copy()->subDays(2),
                'end_date'            => $now->copy()->addDays(10),
                'status'              => 'ACTIVE',
                '_note'               => '❌ Hết lượt — used_count = quantity = 10',
            ],
        ];

        $created = 0;
        $skipped = 0;
        $deletedCode = null;

        foreach ($coupons as $data) {
            $note = $data['_note'];
            unset($data['_note']);

            $exists = Coupon::withTrashed()->where('code', $data['code'])->exists();
            if ($exists) {
                $this->command->warn("  ⏭  Bỏ qua (đã tồn tại): {$data['code']}");
                $skipped++;
                continue;
            }

            Coupon::create($data);
            $this->command->line("  ✔  {$data['code']} — {$note}");
            $created++;
        }

        // ── Coupon bị xoá mềm (soft delete) ──────────────────────────────
        $deletedCode = 'TEST_DELETED';
        $deletedExists = Coupon::withTrashed()->where('code', $deletedCode)->exists();
        if (!$deletedExists) {
            $couponDeleted = Coupon::create([
                'code'                => $deletedCode,
                'type'                => 'PERCENT',
                'value'               => 50,
                'min_order_value'     => 0,
                'max_discount_amount' => null,
                'quantity'            => 0,
                'used_count'          => 0,
                'start_date'          => $now->copy()->subDays(1),
                'end_date'            => $now->copy()->addDays(30),
                'status'              => 'ACTIVE',
            ]);
            $couponDeleted->delete(); // Soft delete
            $this->command->line("  ✔  {$deletedCode} — ❌ Đã bị xoá (soft deleted)");
            $created++;
        } else {
            $this->command->warn("  ⏭  Bỏ qua (đã tồn tại): {$deletedCode}");
            $skipped++;
        }

        $this->command->newLine();
        $this->command->info("✅ Hoàn tất! Đã tạo: {$created} coupon" . ($skipped ? ", bỏ qua: {$skipped}" : "") . ".");
        $this->command->newLine();
        $this->command->table(
            ['Mã voucher', 'Loại', 'Giá trị', 'Kịch bản test'],
            [
                ['TEST_PERCENT10',  'PERCENT', '10%',      '✅ Hợp lệ hoàn toàn — không giới hạn'],
                ['TEST_FIXED50K',   'FIXED',   '50.000₫',  '✅ Hợp lệ — đơn ≥ 100.000₫'],
                ['TEST_NEWUSER',    'PERCENT', '20%',      '✅ Hợp lệ — đơn ≥ 200.000₫, tối đa 100k'],
                ['TEST_NOLIMIT',    'PERCENT', '5%',       '✅ Hợp lệ — không ngày không lượt'],
                ['TEST_VIP',        'PERCENT', '30%',      '⚠️  Nhập ≥ 500.000₫ để thấy valid / < 500k để thấy invalid'],
                ['TEST_EXPIRED',    'PERCENT', '15%',      '❌ Hết hạn (3 ngày trước)'],
                ['TEST_NOTYET',     'FIXED',   '75.000₫',  '❌ Chưa đến ngày (5 ngày nữa)'],
                ['TEST_INACTIVE',   'PERCENT', '25%',      '❌ Bị khoá INACTIVE'],
                ['TEST_OUTOFSTOCK', 'FIXED',   '30.000₫',  '❌ Hết lượt (10/10)'],
                ['TEST_DELETED',    'PERCENT', '50%',      '❌ Đã bị xoá khỏi hệ thống'],
            ]
        );
    }
}
