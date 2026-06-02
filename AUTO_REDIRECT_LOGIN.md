# Admin Auto-Redirect After Login

## Mô tả
Khi người dùng đăng nhập:
- **Nếu là ADMIN** → Tự động chuyển hướng tới `/admin` (Admin Dashboard)
- **Nếu là USER/STAFF/MANAGER** → Chuyển hướng tới `/dashboard` (User Dashboard)

---

## Cách hoạt động

### 1. Login Flow
```
Người dùng điền email + password
    ↓
AuthenticatedSessionController::store()
    ↓
Kiểm tra role user.isAdmin()
    ↓
Nếu ADMIN → redirect /admin
Nếu khác → redirect /dashboard
```

### 2. Files liên quan

#### A. AuthenticatedSessionController (`app/Http/Controllers/Auth/AuthenticatedSessionController.php`)
**Trước:**
```php
return redirect()->intended(route('dashboard', absolute: false));
```

**Sau:**
```php
$user = auth()->user();
if ($user && $user->isAdmin()) {
    return redirect()->intended(route('admin.dashboard', absolute: false));
}
return redirect()->intended(route('dashboard', absolute: false));
```

**Chức năng**: Kiểm tra role ngay sau login, redirect tương ứng.

#### B. RedirectAdminUsers Middleware (`app/Http/Middleware/RedirectAdminUsers.php`)
**Chức năng**: 
- Nếu user là admin mà truy cập `/dashboard` → redirect `/admin`
- Nếu user không phải admin mà truy cập `/admin/*` → redirect `/dashboard`

**Áp dụng**: Middleware này được thêm vào web middleware stack trong `bootstrap/app.php`

#### C. AdminMiddleware (`app/Http/Middleware/AdminMiddleware.php`)
**Chức năng**: Kiểm tra quyền admin, nếu không phải admin → 403 Unauthorized

**Áp dụng**: Tất cả routes `/admin/*` yêu cầu middleware này

---

## Test Login

### Đăng nhập với tài khoản Admin
```
Email: admin@cinema.local
Password: admin123
```
**Kết quả**: Redirect tới `/admin` ✅

### Đăng nhập với tài khoản Customer
```
Email: customer1@example.com
Password: user123
```
**Kết quả**: Redirect tới `/dashboard` ✅

### Cố gắng truy cập admin page mà không phải admin
```
URL: /admin
User: customer1@example.com
```
**Kết quả**: 403 Unauthorized ❌

---

## Middleware Stack

```php
// bootstrap/app.php
$middleware->web(append: [
    RedirectAdminUsers::class,  // ← Kiểm tra & redirect dựa trên role
]);

$middleware->alias([
    'admin' => AdminMiddleware::class,  // ← Kiểm tra quyền admin
]);
```

---

## Luồng xử lý chi tiết

### Kịch bản 1: Admin login
1. Login form → POST /login
2. AuthenticatedSessionController::store()
   - Authenticate credentials ✓
   - Check user.isAdmin() = true ✓
   - Redirect /admin
3. Truy cập /admin
   - Middleware auth → Pass ✓
   - Middleware admin → Check isAdmin() → Pass ✓
   - Hiển thị Admin Dashboard

### Kịch bản 2: Customer login
1. Login form → POST /login
2. AuthenticatedSessionController::store()
   - Authenticate credentials ✓
   - Check user.isAdmin() = false ✗
   - Redirect /dashboard
3. Truy cập /dashboard
   - Middleware auth → Pass ✓
   - Hiển thị User Dashboard

### Kịch bản 3: Customer cố gắng truy cập admin
1. Truy cập /admin
   - Middleware auth → Pass ✓
   - Middleware admin → Check isAdmin() = false → Abort 403 ❌

---

## Cấu hình

### Thêm Middleware (đã done)
File: `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        RedirectAdminUsers::class,
    ]);
    
    $middleware->alias([
        'admin' => AdminMiddleware::class,
    ]);
})
```

### User Model Requirements
User model phải có các method:
- `isAdmin()` - Kiểm tra role ADMIN
- `isStaff()` - Kiểm tra role STAFF
- `isManager()` - Kiểm tra role MANAGER
- `isCustomer()` - Kiểm tra role USER

*(Các method này đã có trong `app/Models/User.php`)*

---

## Kiểm tra Admin User trong Database

```sql
SELECT id, name, email, role_id, created_at 
FROM users 
WHERE role_id = (SELECT id FROM roles WHERE name = 'ADMIN');
```

Expected result:
```
id | name | email | role_id | created_at
1  | Admin | admin@cinema.local | 1 | 2026-06-02 ...
```

---

## Troubleshooting

### Vấn đề: Admin login nhưng không redirect /admin
**Giải pháp:**
1. Check `isAdmin()` method trong User model
2. Check role_id trong database
3. Check middleware được load trong bootstrap/app.php

### Vấn đề: User không thể login
**Giải pháp:**
1. Check user record trong database
2. Check password được hash đúng
3. Check email trùng khớp

### Vấn đề: 403 Unauthorized truy cập /admin
**Giải pháp:**
1. Đây là lỗi bình thường nếu không phải admin
2. Hoặc check `isAdmin()` method logic

---

## Flow Chart

```
┌─────────────────────────────┐
│  Login Page                 │
│  (Email & Password)         │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  AuthenticatedSessionController
│  ::store()                  │
└──────────────┬──────────────┘
               │
         ┌─────┴─────┐
         │           │
         ▼           ▼
    ┌────────┐  ┌──────────┐
    │isAdmin?│  │
    └─┬──────┘  └──────────┘
      │
  ┌───┴───┐
  │       │
  YES    NO
  │       │
  ▼       ▼
/admin  /dashboard
```

---

**Tạo ngày**: 02/06/2026  
**Phiên bản**: 1.0
