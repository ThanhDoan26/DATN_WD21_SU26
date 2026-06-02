# Admin Panel - Cinema Ticket Booking System

## Giới thiệu
Admin Panel là một giao diện quản lý toàn bộ hệ thống Cinema Booking. Được thiết kế với sidebar nằm bên trái, giao diện đẹp mắt, responsive và dễ sử dụng.

---

## Cấu trúc giao diện

### Layout Master
- **File**: `resources/views/admin/layouts/app.blade.php`
- **Tính năng**:
  - Sidebar cố định bên trái với menu chính
  - Topbar hiển thị tiêu đề trang và thông tin người dùng
  - Nền gradient chuyên nghiệp
  - Responsive design cho mobile
  - Font Awesome icons cho các menu items

### Sidebar Menu
```
🎬 Cinema (Admin Panel)
├── Dashboard          ← Trang chính
├── Cinemas           ← Quản lý cụm rạp
├── Rooms             ← Quản lý phòng chiếu
├── Seats             ← Quản lý sơ đồ ghế
├── Movies            ← Quản lý phim (coming soon)
├── Showtimes         ← Quản lý lịch chiếu (coming soon)
├── Bookings          ← Quản lý đơn hàng (coming soon)
├── Users             ← Quản lý người dùng (coming soon)
└── Logout            ← Đăng xuất
```

---

## Các trang Admin chính

### 1. Dashboard (`/admin`)
- Hiển thị thống kê tổng quan:
  - Tổng số cụm rạp
  - Tổng số phòng chiếu
  - Tổng số phim
  - Tổng số đơn hàng
- Hướng dẫn sử dụng panel

### 2. Cinemas Management (`/admin/cinemas`)
- **Danh sách**: Xem tất cả cụm rạp với thông tin chi tiết
- **Thêm mới**: Tạo rạp mới với thông tin:
  - Tên rạp (bắt buộc)
  - Địa chỉ (bắt buộc)
  - Thành phố (bắt buộc)
  - Điện thoại
  - Email
  - Trạng thái (ACTIVE/INACTIVE)
- **Sửa**: Cập nhật thông tin rạp
- **Xem chi tiết**: Thông tin rạp + danh sách phòng trong rạp
- **Xóa**: Xoá rạp khỏi hệ thống

### 3. Rooms Management (`/admin/rooms`)
- **Danh sách**: Xem tất cả phòng chiếu với thông tin:
  - Tên phòng
  - Rạp (cinema)
  - Format (2D, 3D, IMAX, ...)
  - Tổng ghế
  - Trạng thái
- **Thêm mới**: Tạo phòng mới
  - Chọn rạp
  - Tên phòng
  - Format
  - Tổng ghế
  - Trạng thái (ACTIVE, INACTIVE, MAINTENANCE, CLOSED)
- **Sửa/Xóa/Xem**: Quản lý phòng
- **Chi tiết phòng**: Xem danh sách ghế trong phòng

### 4. Seats Management (`/admin/seats`)
- **Sơ đồ ghế**: Hiển thị trực quan sơ đồ ghế ngồi
  - Ghế Regular: màu xanh (17a2b8)
  - Ghế VIP: màu vàng (ffc107)
  - Ghế Sweetbox: màu hồng (e83e8c)
  - Ghế trống: xanh lá (28a745)
  - Ghế hỏng: đỏ (dc3545)
- **Thêm ghế**: Tạo nhiều ghế cùng lúc
  - Chọn phòng
  - Nhập dòng (A, B, C, ...)
  - Từ ghế - đến ghế (VD: 1-10)
  - Chọn loại ghế
  - Chọn trạng thái
- **Lọc**: Lọc ghế theo rạp hoặc phòng
- **Thống kê**: Hiển thị số lượng từng loại ghế
- **Bảng chi tiết**: Liệt kê tất cả ghế với thông tin

---

## Cấu trúc thư mục

```
resources/views/admin/
├── layouts/
│   └── app.blade.php          ← Layout master
├── dashboard.blade.php         ← Trang chính
├── cinemas/
│   ├── index.blade.php        ← Danh sách rạp
│   ├── create.blade.php       ← Tạo rạp mới
│   ├── edit.blade.php         ← Sửa rạp
│   └── show.blade.php         ← Xem chi tiết rạp
├── rooms/
│   ├── index.blade.php        ← Danh sách phòng
│   ├── create.blade.php       ← Tạo phòng mới
│   ├── edit.blade.php         ← Sửa phòng
│   └── show.blade.php         ← Xem chi tiết phòng
├── seats/
│   ├── index.blade.php        ← Sơ đồ ghế + danh sách
│   ├── create.blade.php       ← Tạo ghế
│   └── edit.blade.php         ← Sửa ghế
├── movies/
│   └── index.blade.php        ← Danh sách phim (coming soon)
├── showtimes/
│   └── index.blade.php        ← Danh sách lịch chiếu (coming soon)
├── bookings/
│   └── index.blade.php        ← Danh sách đơn hàng (coming soon)
└── users/
    └── index.blade.php        ← Danh sách người dùng (coming soon)
```

---

## Controllers

```
app/Http/Controllers/Admin/
├── AdminController.php         ← Base controller
├── DashboardController.php     ← Dashboard
├── CinemaController.php        ← Cinemas CRUD
├── RoomController.php          ← Rooms CRUD
└── SeatController.php          ← Seats CRUD
```

---

## Routes

```php
// Tất cả routes admin bắt đầu từ /admin
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('cinemas', CinemaController::class, ['as' => 'admin']);
    Route::resource('rooms', RoomController::class, ['as' => 'admin']);
    Route::resource('seats', SeatController::class, ['as' => 'admin']);
    // ... menu items khác
});
```

---

## Middleware & Security

### AdminMiddleware
- **File**: `app/Http/Middleware/AdminMiddleware.php`
- **Chức năng**: 
  - Kiểm tra user đã login chưa
  - Kiểm tra user có quyền admin
  - Chặn truy cập nếu không phải admin

### Đăng ký Middleware
- Đã đăng ký trong `bootstrap/app.php`
- Sử dụng: `'admin'` middleware alias

---

## Styling & Design

### Màu sắc chính
- **Primary**: #1e3c72 (Xanh đậm)
- **Secondary**: #2a5298 (Xanh nhạt)
- **Gradient**: Linear gradient từ #1e3c72 → #2a5298

### Thư viện
- **Bootstrap 5.3**: Responsive framework
- **Font Awesome 6.4**: Icons
- **Custom CSS**: Styling bổ sung

### Responsive
- Desktop (1200px+): Sidebar cố định + full width
- Tablet (768px-1200px): Sidebar ẩn, menu toggle
- Mobile (<768px): Full screen menu

---

## Sử dụng

### Truy cập Admin Panel
1. Đăng nhập với tài khoản admin
2. Truy cập: `http://localhost:8000/admin`
3. Nếu không phải admin → 403 Unauthorized

### Ví dụ: Tạo rạp mới
1. Click **Cinemas** → **Thêm Rạp Mới**
2. Điền thông tin (tên, địa chỉ, thành phố)
3. Click **Tạo Mới**
4. Nhìn lại trong danh sách

### Ví dụ: Tạo ghế
1. Click **Seats** → **Thêm Ghế Mới**
2. Chọn phòng: "CGV Sư Vạn Hạnh - Cinema 1"
3. Dòng: A
4. Từ ghế: 1, Đến ghế: 10
5. Loại ghế: Regular
6. Trạng thái: AVAILABLE
7. Click **Tạo Mới** → Tạo 10 ghế A1 đến A10

---

## Tính năng nâng cao (TODO)

- [ ] Xóa hàng loạt
- [ ] Import/Export CSV
- [ ] Báo cáo thống kê
- [ ] Lịch sử thay đổi (audit log)
- [ ] Tìm kiếm nâng cao
- [ ] Phân quyền chi tiết
- [ ] Dark mode
- [ ] Multi-language

---

## Lưu ý

1. **Bảo mật**: Luôn kiểm tra quyền admin trước khi xoá dữ liệu
2. **Validate**: Tất cả input đều có validation
3. **Relationships**: Sử dụng eager loading (`with()`) để tối ưu hóa query
4. **Error handling**: Hiển thị message lỗi thân thiện với user
5. **Responsive**: Test trên mobile trước khi release

---

## Hỗ trợ

Nếu có vấn đề:
1. Kiểm tra middleware `AdminMiddleware`
2. Kiểm tra user có `isAdmin()` method
3. Kiểm tra routes được load từ `routes/admin.php`
4. Check browser console để xem lỗi JavaScript

---

**Tạo ngày**: 02/06/2026  
**Phiên bản**: 1.0
