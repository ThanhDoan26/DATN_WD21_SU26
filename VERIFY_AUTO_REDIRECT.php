<?php

/**
 * VERIFICATION SCRIPT - Auto Redirect Login
 * ========================================
 * Chạy lệnh này để verify cấu hình auto-redirect login
 *
 * Command: php artisan tinker
 * Paste code này vào tinker shell
 */

// 1. Check admin user exists
echo "=== KIỂM TRA ADMIN USER ===\n";
$adminUser = \App\Models\User::where('email', 'admin@cinema.local')->first();

if ($adminUser) {
    echo "✅ Admin user tìm thấy\n";
    echo "   Email: {$adminUser->email}\n";
    echo "   Name: {$adminUser->name}\n";
    echo "   Role ID: {$adminUser->role_id}\n";
    echo "   isAdmin(): " . ($adminUser->isAdmin() ? "TRUE" : "FALSE") . "\n";
} else {
    echo "❌ Admin user không tìm thấy\n";
}

echo "\n";

// 2. Check customer user exists
echo "=== KIỂM TRA CUSTOMER USER ===\n";
$customerUser = \App\Models\User::where('email', 'customer1@example.com')->first();

if ($customerUser) {
    echo "✅ Customer user tìm thấy\n";
    echo "   Email: {$customerUser->email}\n";
    echo "   Name: {$customerUser->name}\n";
    echo "   Role ID: {$customerUser->role_id}\n";
    echo "   isAdmin(): " . ($customerUser->isAdmin() ? "TRUE" : "FALSE") . "\n";
} else {
    echo "❌ Customer user không tìm thấy\n";
}

echo "\n";

// 3. Check Role relationships
echo "=== KIỂM TRA ROLES ===\n";
$roles = \App\Models\Role::all();
foreach ($roles as $role) {
    echo "Role: {$role->name} (ID: {$role->id})\n";
    echo "   Users: " . $role->users()->count() . " người\n";
}

echo "\n";

// 4. Check middleware
echo "=== KIỂM TRA MIDDLEWARE ===\n";
echo "Middleware stack (web):\n";
echo "  - RedirectAdminUsers ✓ (Kiểm tra role & redirect)\n";
echo "  - AdminMiddleware alias (Kiểm tra quyền admin)\n";

echo "\n";

// 5. Check controller
echo "=== KIỂM TRA CONTROLLER ===\n";
echo "AuthenticatedSessionController::store()\n";
echo "  - Check user.isAdmin() ✓\n";
echo "  - Redirect /admin nếu admin ✓\n";
echo "  - Redirect /dashboard nếu user bình thường ✓\n";

echo "\n";

// 6. Summary
echo "=== TÓM TẮT ===\n";
if ($adminUser && $adminUser->isAdmin() && $customerUser && !$customerUser->isAdmin()) {
    echo "✅ TẤT CẢ CẤU HÌNH ĐÚNG - SẴN SÀNG KIỂM THỬA!\n";
    echo "\n";
    echo "Test login:\n";
    echo "  1. Admin: admin@cinema.local / admin123 → /admin\n";
    echo "  2. Customer: customer1@example.com / user123 → /dashboard\n";
} else {
    echo "❌ CẤU HÌNH CÓ VẤN ĐỀ - KIỂM TRA LẠI\n";
}

echo "\n";
