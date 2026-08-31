# BẢNG TESTCASE CHI TIẾT TOÀN BỘ HỆ THỐNG - DỰ ÁN ĐẶT VÉ XEM PHIM (DATN_WD21_SU26)

**Hệ thống:** Website Đặt Vé Xem Phim Trực Tuyến (Laravel Framework)  
**Phiên bản:** DATN WD21 SU26  
**Ngày kiểm thử & Audit lại:** 30/08/2026  
**Phương pháp kiểm thử:** Code Review, Static Analysis, RBAC & Boundary Value Analysis  
**Tổng số phân hệ kiểm thử:** 16 Phân hệ (Bao gồm chức năng & bảo mật)  

---

## MỤC LỤC PHÂN HỆ
1. [Phân hệ 1: Xác thực & Quản lý Tài khoản (Authentication & Profile)](#1-phân-hệ-xác-thực--quản-lý-tài-khoản-tc-auth)
2. [Phân hệ 2: Phân quyền Hệ thống & RBAC (Authorization)](#2-phân-hệ-phân-quyền-hệ-thống--rbac-tc-authz)
3. [Phân hệ 3: Quản lý Thể loại Phim (Movie Categories)](#3-phân-hệ-quản-lý-thể-loại-phim-tc-cat)
4. [Phân hệ 4: Quản lý Phim (Movie Management)](#4-phân-hệ-quản-lý-phim-tc-mov)
5. [Phân hệ 5: Quản lý Rạp chiếu, Phòng chiếu & Ghế (Cinema, Room & Seat)](#5-phân-hệ-quản-lý-rạp-phòng-chiếu--ghế-tc-room)
6. [Phân hệ 6: Quản lý Suất chiếu (Showtime Management)](#6-phân-hệ-quản-lý-suất-chiếu-tc-show)
7. [Phân hệ 7: Đặt vé & Tạm giữ ghế (Booking & Seat Hold Engine)](#7-phân-hệ-đặt-vé--tạm-giữ-ghế-tc-book)
8. [Phân hệ 8: Cơ chế Anti-Spam & Giới hạn Đặt vé (Anti-Spam & Cooldown)](#8-phân-hệ-cơ-chế-anti-spam--giới-hạn-đặt-vé-tc-spam)
9. [Phân hệ 9: Cổng thanh toán Online (Stripe & VNPay Payment Gateways)](#9-phân-hệ-cổng-thanh-toán-online-tc-pay)
10. [Phân hệ 10: Mã giảm giá & Đồ ăn Bắp nước (Coupon & Combo System)](#10-phân-hệ-mã-giảm-giá--bắp-nước-tc-coup-combo)
11. [Phân hệ 11: Vé điện tử, Mã QR & Check-in (Ticket & Verification)](#11-phân-hệ-vé-điện-tử-mã-qr--check-in-tc-tkt)
12. [Phân hệ 12: Vận hành Quầy vé Nhân viên (Staff Walk-in Booking)](#12-phân-hệ-vận-hành-quầy-vé-nhân-viên-tc-stf)
13. [Phân hệ 13: Đánh giá & Bình luận (Review & Rating System)](#13-phân-hệ-đánh-giá--bình-luận-tc-rev)
14. [Phân hệ 14: Quản lý Tin tức & Bài viết (News & Posts Management)](#14-phân-hệ-quản-lý-tin-tức--bài-viết-tc-post)
15. [Phân hệ 15: Thống kê & Báo cáo Dashboard (Admin & Manager Dashboard)](#15-phân-hệ-thống-kê--báo-cáo-dashboard-tc-dash)
16. [Phân hệ 16: Trợ lý ảo AI Chatbot & Kiểm thử Bảo mật (AI Chatbot & Security)](#16-phân-hệ-trợ-lý-ảo-ai-chatbot--bảo-mật-hệ-thống-tc-sec)

---

## 1. PHÂN HỆ XÁC THỰC & QUẢN LÝ TÀI KHOẢN (TC-AUTH)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 1 | TC-AUTH-001 | Đăng ký | Đăng ký tài khoản mới thành công | Chưa đăng nhập | 1. Đột nhập trang `/register`<br>2. Nhập đầy đủ thông tin hợp lệ<br>3. Bấm "Đăng ký" | `name`: "Nguyễn Văn A"<br>`email`: "user1@gmail.com"<br>`password`: "Password123!"<br>`password_confirmation`: "Password123!" | Tạo user thành công trong DB, gửi mail xác nhận (nếu có), redirect về Dashboard. | High | Chưa kiểm thử |
| 2 | TC-AUTH-002 | Đăng ký | Đăng ký với Email đã tồn tại | Email `user1@gmail.com` đã có trong DB | 1. Nhập email đã đăng ký<br>2. Nhập password hợp lệ<br>3. Bấm "Đăng ký" | `email`: "user1@gmail.com" | Báo lỗi validation: "Email đã tồn tại trên hệ thống", giữ lại dữ liệu form trừ password. | High | Chưa kiểm thử |
| 3 | TC-AUTH-003 | Đăng ký | Validate Password không trùng khớp | Chưa đăng nhập | 1. Nhập password và password_confirmation khác nhau<br>2. Bấm "Đăng ký" | `password`: "12345678"<br>`password_confirmation`: "87654321" | Báo lỗi validation password confirmation không khớp. | Medium | Chưa kiểm thử |
| 4 | TC-AUTH-004 | Đăng ký | Validate Password yếu (Độ dài/Ký tự) | Chưa đăng nhập | 1. Nhập password ít hơn 8 ký tự<br>2. Bấm "Đăng ký" | `password`: "123" | Báo lỗi password phải từ 8 ký tự trở lên. | Medium | Chưa kiểm thử |
| 5 | TC-AUTH-005 | Đăng nhập | Đăng nhập tài khoản User hợp lệ | Đã có tài khoản User | 1. Vào `/login`<br>2. Nhập Email & Password đúng<br>3. Bấm "Đăng nhập" | `email`: "user1@gmail.com"<br>`password`: "Password123!" | Đăng nhập thành công, lưu session/cookie, redirect về Trang chủ hoặc `/dashboard`. | Critical | Chưa kiểm thử |
| 6 | TC-AUTH-006 | Đăng nhập | Đăng nhập tài khoản Admin/Manager/Staff | Đã có tài khoản role tương ứng | 1. Nhập credentials của Admin/Manager/Staff<br>2. Bấm "Đăng nhập" | `email`: "admin@cinema.com"<br>`password`: "Admin123!" | Đăng nhập thành công, redirect chính xác về phân vùng quản trị (`/admin`, `/manager`, `/staff`). | Critical | Chưa kiểm thử |
| 7 | TC-AUTH-007 | Đăng nhập | Đăng nhập sai Mật khẩu | Đã có tài khoản | 1. Nhập email đúng, password sai<br>2. Bấm "Đăng nhập" | `email`: "user1@gmail.com"<br>`password`: "WrongPass" | Báo lỗi: "Thông tin đăng nhập không chính xác", không tiết lộ email đúng hay sai. | High | Chưa kiểm thử |
| 8 | TC-AUTH-008 | Đăng nhập | Đăng nhập với Email chưa từng đăng ký | Email không có trong DB | 1. Nhập email chưa có trong DB<br>2. Bấm "Đăng nhập" | `email`: "notfound@gmail.com" | Báo lỗi thông tin đăng nhập không hợp lệ. | High | Chưa kiểm thử |
| 9 | TC-AUTH-009 | Đăng nhập | Đăng nhập với tài khoản bị khóa/inactive | User có `status=inactive` | 1. Nhập credentials user bị khóa<br>2. Bấm "Đăng nhập" | Email + Password đúng | Báo lỗi tài khoản đã bị khóa, ngăn chặn truy cập. | High | Chưa kiểm thử |
| 10 | TC-AUTH-010 | Đăng xuất | Đăng xuất khỏi hệ thống | Đã đăng nhập | 1. Bấm nút "Đăng xuất" trên header/avatar | N/A | Xóa Session, Invalidate Token, redirect về `/login` hoặc Trang chủ. | High | Chưa kiểm thử |
| 11 | TC-AUTH-011 | Quên mật khẩu | Yêu cầu Reset Password email hợp lệ | Đã có tài khoản | 1. Vào `/forgot-password`<br>2. Nhập email<br>3. Bấm "Gửi link reset" | `email`: "user1@gmail.com" | Gửi email chứa Token reset password, thông báo gửi thành công. | Medium | Chưa kiểm thử |
| 12 | TC-AUTH-012 | Quên mật khẩu | Đặt lại mật khẩu từ Token trong Email | Đã nhận link reset | 1. Bấm link trong mail<br>2. Nhập mật khẩu mới<br>3. Bấm "Lưu mật khẩu" | `token` hợp lệ<br>`password`: "NewPassword123!" | Cập nhật password mới vào DB, cho phép đăng nhập bằng password mới. | High | Chưa kiểm thử |
| 13 | TC-AUTH-013 | Profile | Cập nhật thông tin cá nhân (Họ tên, SĐT, Avatar) | Đã đăng nhập User | 1. Vào `/profile`<br>2. Thay đổi tên, số điện thoại, upload ảnh đại diện<br>3. Bấm "Cập nhật" | `name`: "Nguyễn Văn B", `phone`: "0987654321", `avatar`: `avatar.png` | Thông tin cập nhật DB thành công, hiển thị avatar mới trên giao diện. | Medium | Chưa kiểm thử |
| 14 | TC-AUTH-014 | Security Backdoor | Kiểm tra Route Bypass Auth Quick Login Staff | Chưa đăng nhập | 1. Truy cập đường dẫn `/quick-login-staff` | URL: `/quick-login-staff` | **Expected:** 404 / 403 Forbidden.<br>**Result:** Codebase không chứa backdoor này nữa -> PASS | High | **Đạt (Pass)** |

---

## 2. PHÂN HỆ PHÂN QUYỀN HỆ THỐNG & RBAC (TC-AUTHZ)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 15 | TC-AUTHZ-001 | RBAC Client | User thường truy cập Admin Dashboard `/admin` | Đã đăng nhập role `USER` | 1. Nhập URL `/admin` hoặc `/admin/dashboard` | N/A | Trả về HTTP 403 Forbidden. | Critical | Chưa kiểm thử |
| 16 | TC-AUTHZ-002 | RBAC Client | User thường truy cập Manager Dashboard `/manager` | Đã đăng nhập role `USER` | 1. Nhập URL `/manager/dashboard` | N/A | Trả về HTTP 403 Forbidden. | Critical | Chưa kiểm thử |
| 17 | TC-AUTHZ-003 | RBAC Client | User thường truy cập Staff Dashboard `/staff` | Đã đăng nhập role `USER` | 1. Nhập URL `/staff/dashboard` | N/A | Trả về HTTP 403 Forbidden. | Critical | Chưa kiểm thử |
| 18 | TC-AUTHZ-004 | RBAC Staff | Staff truy cập trang quản lý Admin (`/admin/users`) | Đã đăng nhập role `STAFF` | 1. Nhập URL `/admin/users` | N/A | Trả về HTTP 403 Forbidden. Staff chỉ được vào `/staff/*`. | Critical | Chưa kiểm thử |
| 19 | TC-AUTHZ-005 | RBAC Manager | Manager chỉ truy cập dữ liệu Rạp chiếu được phân công | Đã đăng nhập role `MANAGER` | 1. Manager Rạp A truy cập dữ liệu suất chiếu Rạp B | Showtime ID thuộc Rạp B | Hệ thống từ chối hoặc trả về 403 / 404 Not Found. | High | Chưa kiểm thử |
| 20 | TC-AUTHZ-006 | Security Leak | Kiểm tra Admin Coupons/Combos Routes có bọc role:ADMIN | Đã đăng nhập role `USER` | 1. Truy cập `/admin/coupons` hoặc `/admin/combos` | URL `/admin/coupons` | **Result:** Đã bọc kín trong `Route::middleware(['role:ADMIN'])` tại `routes/admin.php:L26`. -> PASS | Critical | **Đạt (Pass)** |
| 21 | TC-AUTHZ-007 | Guest Guard | Khách chưa đăng nhập vào chọn ghế/thanh toán | Guest (Chưa đăng nhập) | 1. Bấm đặt vé tại suất chiếu bất kỳ | Showtime ID | Redirect người dùng sang trang `/login` bắt buộc đăng nhập. | High | Chưa kiểm thử |

---

## 3. PHÂN HỆ QUẢN LÝ THỂ LOẠI PHIM (TC-CAT)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 22 | TC-CAT-001 | Thêm Thể loại | Tạo thể loại phim mới hợp lệ | Role Admin/Manager | 1. Vào danh mục Thể loại<br>2. Bấm "Thêm mới"<br>3. Nhập Tên thể loại, Mô tả<br>4. Lưu | `name`: "Hành Động", `description`: "Phim mạo hiểm..." | Thể loại được thêm vào DB, tự động sinh slug `hanh-dong`, hiển thị danh sách. | Medium | Chưa kiểm thử |
| 23 | TC-CAT-002 | Thêm Thể loại | Tạo thể loại với Tên trùng lặp | Đã có "Hành Động" | 1. Nhập tên thể loại trùng với tên đã có | `name`: "Hành Động" | Báo lỗi validation: "Tên thể loại đã tồn tại". | Medium | Chưa kiểm thử |
| 24 | TC-CAT-003 | Sửa Thể loại | Cập nhật tên thể loại | Đã có thể loại | 1. Bấm Sửa thể loại ID=1<br>2. Đổi tên thành "Hành Động & Phiêu Lưu"<br>3. Lưu | `name`: "Hành Động & Phiêu Lưu" | Cập nhật thành công, slug tự động đổi theo. | Low | Chưa kiểm thử |
| 25 | TC-CAT-004 | Xóa Thể loại | Xóa thể loại phim (Soft Delete) | Đã có thể loại | 1. Bấm Xóa thể loại ID=1<br>2. Xác nhận xóa | N/A | Đưa thể loại vào thùng rác (`deleted_at`), các phim gắn thể loại này không bị mất dữ liệu. | Medium | Chưa kiểm thử |

---

## 4. PHÂN HỆ QUẢN LÝ PHIM (TC-MOV)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 26 | TC-MOV-001 | Thêm Phim | Thêm phim mới hợp lệ | Role Admin/Manager | 1. Vào Quản lý Phim -> Thêm mới<br>2. Nhập tiêu đề, đạo diễn, diễn viên, thời lượng, ngày khởi chiếu, ngày kết thúc, định dạng (2D/3D), chọn Thể loại, upload Poster, nhúng Trailer YouTube<br>3. Bấm Lưu | `title`: "Lật Mặt 7", `duration`: 138 (phút), `format`: "2D", `poster`: `latmat7.jpg`, `release_date`: "2026-09-01" | Tạo phim mới thành công, hiển thị trang danh sách phim. | High | Chưa kiểm thử |
| 27 | TC-MOV-002 | Thêm Phim | Validate thông tin bắt buộc (Tiêu đề, Thời lượng, Poster) | Role Admin/Manager | 1. Để trống Tiêu đề và Thời lượng<br>2. Bấm Lưu | `title`: "", `duration`: "" | Trả về lỗi validation yêu cầu điền đầy đủ trường bắt buộc. | Medium | Chưa kiểm thử |
| 28 | TC-MOV-003 | Thêm Phim | Validate thời lượng phim <= 0 | Role Admin/Manager | 1. Nhập thời lượng là số âm hoặc 0 | `duration`: -10 | Báo lỗi thời lượng phải là số nguyên dương > 0. | Medium | Chưa kiểm thử |
| 29 | TC-MOV-004 | Thêm Phim | Validate định dạng Phim (Format 2D/3D) | Role Admin/Manager | 1. Chọn định dạng phim | `format`: "2D" hoặc "3D" | Hệ thống ghi nhận chính xác format để check tương thích phòng chiếu sau này. | High | Chưa kiểm thử |
| 30 | TC-MOV-005 | Sửa Phim | Cập nhật trạng thái phim (Đang chiếu / Sắp chiếu / Ngừng chiếu) | Đã có phim | 1. Mở màn hình Sửa phim<br>2. Đổi trạng thái từ `coming_soon` sang `now_showing` | `status`: "now_showing" | Cập nhật DB, phim hiển thị ở tab "Phim Đang Chiếu" ngoài giao diện khách hàng. | High | Chưa kiểm thử |
| 31 | TC-MOV-006 | Client Xem | Tìm kiếm & Lọc phim ngoài Trang chủ | Đã có danh sách phim | 1. Vào trang danh sách phim<br>2. Nhập từ khóa tìm kiếm<br>3. Chọn lọc theo Thể loại / Rạp | Keyword: "Lật Mặt", Thể loại: "Hành Động" | Hiển thị chính xác danh sách phim thỏa mãn điều kiện lọc. | High | Chưa kiểm thử |
| 32 | TC-MOV-007 | Client Xem | Xem chi tiết phim | Phim đang active | 1. Bấm vào chi tiết phim | Movie Slug / ID | Hiển thị thông tin phim, trailer, đánh giá trung bình và lịch chiếu của phim đó. | High | Chưa kiểm thử |

---

## 5. PHÂN HỆ QUẢN LÝ RẠP, PHÒNG CHIẾU & GHẾ (TC-ROOM)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 33 | TC-ROOM-001 | Thêm Rạp chiếu | Thêm Rạp chiếu mới (Cinema) | Role Admin | 1. Tạo Rạp chiếu mới<br>2. Nhập Tên rạp, Địa chỉ, Thành phố, SĐT | `name`: "Rạp CGV Vincom", `city`: "Hà Nội" | Thêm Rạp thành công, có thể phân công Manager quản lý rạp này. | High | Chưa kiểm thử |
| 34 | TC-ROOM-002 | Thêm Phòng chiếu | Tạo Phòng chiếu mới kèm Định dạng (2D/3D/IMAX) | Rạp chiếu đã tồn tại | 1. Vào Rạp A -> Thêm Phòng chiếu<br>2. Nhập tên phòng, chọn định dạng room (2D/3D), nhập số hàng/số cột ghế | `name`: "Phòng 01", `format`: "2D", `rows`: 10, `cols`: 12 | Tạo phòng chiếu thành công, tự động ma trận hóa 120 ghế. | Critical | Chưa kiểm thử |
| 35 | TC-ROOM-003 | Cấu hình Ghế | Cài đặt loại ghế (Thường, VIP, Đôi/Sweetbox) | Phòng chiếu đã tạo | 1. Chọn dãy hàng E, F<br>2. Chuyển loại ghế thành VIP<br>3. Chọn dãy H -> Chuyển thành ghế Đôi | `VIP`: Hàng E, F<br>`Sweetbox`: Hàng H | Cập nhật cấu hình loại ghế và hệ số giá tương ứng của từng loại ghế. | High | Chưa kiểm thử |
| 36 | TC-ROOM-004 | Bảo trì Ghế | Đánh dấu ghế bị hỏng / ngưng phục vụ | Ghế đang active | 1. Chọn ghế E5 -> Đổi trạng thái thành `Broken/Disabled` | Seat ID = E5 | Ghế E5 bị vô hiệu hóa trên sơ đồ ghế của khách hàng (không thể click chọn). | High | Chưa kiểm thử |
| 37 | TC-ROOM-005 | Xóa Phòng chiếu | Xóa phòng chiếu đang có Suất chiếu tương lai | Phòng có suất chiếu | 1. Thử xóa Phòng chiếu 01 | Room ID = 1 | **Ràng buộc:** Báo lỗi không cho xóa phòng chiếu đang có lịch chiếu sắp diễn ra. | High | Chưa kiểm thử |

---

## 6. PHÂN HỆ QUẢN LÝ SUẤT CHIẾU (TC-SHOW)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 38 | TC-SHOW-001 | Tạo Suất chiếu | Tạo Suất chiếu Tương thích Định dạng (Phim 2D + Phòng 2D) | Phim 2D, Phòng 2D | 1. Chọn Phim 2D<br>2. Chọn Phòng 2D<br>3. Nhập thời gian chiếu & giá vé cơ bản<br>4. Lưu | Phim 2D, Phòng 2D, `start_time`: 20:00, `price`: 90.000đ | Tạo suất chiếu thành công (`CompatibleFormatRule` pass). | Critical | Chưa kiểm thử |
| 39 | TC-SHOW-002 | Tạo Suất chiếu | **Validate Tương thích Format:** Phim 3D + Phòng 2D | Phim 3D, Phòng 2D | 1. Chọn Phim 3D<br>2. Chọn Phòng chiếu 2D<br>3. Bấm Lưu | Phim 3D, Phòng 2D | **Validation Error:** Báo lỗi phòng 2D không thể chiếu phim định dạng 3D! | Critical | Chưa kiểm thử |
| 40 | TC-SHOW-003 | Tạo Suất chiếu | **Validate Xung đột thời gian (No Overlap):** Trùng giờ trong cùng 1 phòng | Phòng 01 đã có suất chiếu 18:00 - 20:00 | 1. Tạo suất chiếu mới tại Phòng 01 từ 19:00 đến 21:00 | `start_time`: 19:00, `end_time`: 21:00 | **Validation Error:** Báo lỗi khung giờ bị trùng lắp với suất chiếu đã có. | Critical | Chưa kiểm thử |
| 41 | TC-SHOW-004 | Tạo Suất chiếu | Validate Thời gian bắt đầu trong Quá khứ | N/A | 1. Chọn `start_time` là thời gian đã qua so với hiện tại | `start_time`: 10:00 AM (ngày hôm qua) | Báo lỗi thời gian bắt đầu suất chiếu phải ở tương lai. | High | Chưa kiểm thử |
| 42 | TC-SHOW-005 | Sửa Suất chiếu | Cập nhật giá vé cơ bản của suất chiếu | Suất chiếu chưa diễn ra | 1. Sửa Suất chiếu ID=5 -> Đổi giá vé cơ bản từ 80k thành 100k | `price`: 100000 | Cập nhật giá vé cơ bản thành công, áp dụng cho các lượt đặt vé mới. | Medium | Chưa kiểm thử |

---

## 7. PHÂN HỆ ĐẶT VÉ & TẠM GIỮ GHẾ (TC-BOOK)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 43 | TC-BOOK-001 | Chọn Ghế | Chọn ghế hợp lệ & Khởi tạo phiên Đặt vé Pending | User đăng nhập, vào Suất chiếu | 1. Chọn ghế A1, A2<br>2. Bấm "Tiếp tục đặt vé" | `seats`: ["A1", "A2"] | Đơn hàng trạng thái `PENDING` được tạo, 2 ghế A1, A2 chuyển trạng thái `RESERVED` (Giữ ghế 10 phút). | Critical | Chưa kiểm thử |
| 44 | TC-BOOK-002 | Chọn Ghế | **Validate Giới hạn số lượng ghế (Tối đa 8 ghế/lượt)** | User chọn ghế | 1. Thử chọn 9 ghế trên sơ đồ<br>2. Bấm Tiếp tục | `seats`: 9 ghế | **Validation Error:** Báo lỗi "Mỗi lượt đặt vé tối đa 8 ghế". | Critical | Chưa kiểm thử |
| 45 | TC-BOOK-003 | Chọn Ghế | **Validate Ghế trống đơn lẻ (Seat Gap Validation)** | Dãy ghế: [A1, A2, A3, A4] | 1. Chọn ghế A1 và A3 (để trống duy nhất ghế A2 ở giữa) | `seats`: ["A1", "A3"] | **Validation Error:** Báo lỗi không được để trống 1 ghế đơn lẻ giữa các ghế đã chọn. | High | Chưa kiểm thử |
| 46 | TC-BOOK-004 | Giữ Ghế Concurrent | **Đồng thời chọn ghế (Race Condition):** 2 User cùng chọn 1 ghế | 2 User A & B xem cùng 1 suất chiếu | 1. User A bấm chọn ghế B5<br>2. User B cùng bấm chọn ghế B5 ngay sau đó 0.5s | Seat B5 | User A giữ ghế thành công. User B nhận được thông báo "Ghế B5 đã được người khác giữ". | Critical | Chưa kiểm thử |
| 47 | TC-BOOK-005 | Hết hạn Giữ ghế | Tự động giải phóng ghế sau 10 phút timeout | User A đang giữ ghế 10 phút nhưng không thanh toán | 1. Chờ hết 10 phút đếm ngược | Session timeout | Booking `PENDING` chuyển `EXPIRED`, ghế B5 được giải phóng về trạng thái trống (`AVAILABLE`). | Critical | Chưa kiểm thử |
| 48 | TC-BOOK-006 | Active Guard | Đang có đơn Pending suất chiếu A, cố tình tạo đơn mới ở suất chiếu B | User A đang có booking pending ở Suất chiếu 1 | 1. Mở tab mới, chọn ghế đặt ở Suất chiếu 2 | Showtime 2 | Hệ thống ngăn chặn: Yêu cầu hoàn tất hoặc hủy đơn hàng Pending hiện tại trước. | High | Chưa kiểm thử |

---

## 8. PHÂN HỆ CƠ CHẾ ANTI-SPAM & GIỚI HẠN ĐẶT VÉ (TC-SPAM)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 49 | TC-SPAM-001 | Anti-Spam Cooldown | Cooldown chọn lại ghế sau khi vừa giữ ghế | User vừa chọn ghế và bấm quay lại | 1. Cố chọn lại chính các ghế vừa giữ | Seat list | Áp dụng Cooldown 15 phút ngăn giữ đi giữ lại cùng ghế để phá hoại. | High | Chưa kiểm thử |
| 50 | TC-SPAM-002 | Lock User Spam | Khóa đặt vé User cố tình nhả/hết hạn đơn quá 5 lần | User cố tình tạo 5 đơn PENDING rồi bỏ trôi hết hạn | 5 expired bookings | 1. User thử tạo đơn đặt vé thứ 6 | Booking Request | **SeatHoldAbuseService:** Khóa quyền đặt vé của User này trong 30 phút. | Critical | Chưa kiểm thử |
| 51 | TC-SPAM-003 | Rate Limiting | Spam API chọn ghế (>10 req/phút) | Client dùng script gửi API liên tục | >10 requests/min | 1. Gửi 15 request chọn ghế trong 30s | API `/booking/select-seats` | Middleware Throttle chặn request, trả về HTTP 429 Too Many Requests. | High | Chưa kiểm thử |

---

## 9. PHÂN HỆ CỔNG THANH TOÁN ONLINE (TC-PAY)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 52 | TC-PAY-001 | Stripe Payment | Thanh toán thành công qua Stripe | Đơn hàng PENDING | 1. Chọn thanh toán Stripe<br>2. Nhập thẻ test Stripe hợp lệ<br>3. Bấm Thanh toán | Card: `4242...` | Chuyển hướng về `/checkout/success`, Booking đổi sang `PAID`, ghế đổi sang `BOOKED`. | Critical | Bị chặn (Cần Stripe API Key) |
| 53 | TC-PAY-002 | VNPay Payment | Thanh toán thành công qua VNPay | Đơn hàng PENDING | 1. Chọn thanh toán VNPay<br>2. Nhập OTP test thành công trên Sandbox VNPay | VNPay Sandbox Acc | Redirect về Return URL, checksum `vnp_SecureHash` hợp lệ, Booking đổi sang `PAID`. | Critical | Bị chặn (Cần VNPay Config) |
| 54 | TC-PAY-003 | VNPay Security | Giả mạo Tham số Checksum `vnp_SecureHash` (VNPay Hash Fake) | Đơn hàng PENDING | 1. Cố tình thay đổi số tiền `vnp_Amount` trên URL return từ VNPay | Modified Hash URL | Hệ thống phát hiện Sai chữ ký checksum, từ chối cập nhật trạng thái đơn hàng. | Critical | Chưa kiểm thử |
| 55 | TC-PAY-004 | Payment Guard | Thanh toán lại cho Đơn hàng ĐÃ THANH TOÁN (`PAID`) | Đơn hàng đã `PAID` | 1. Gửi request thanh toán lại cho booking đã PAID | Booking ID đã Paid | Trả về lỗi 400 Bad Request: "Đơn hàng này đã được thanh toán trước đó". | High | Chưa kiểm thử |
| 56 | TC-PAY-005 | Security IDOR | **Thanh toán IDOR:** User A thanh toán đơn hàng của User B | User A & User B | 1. User A lấy Booking ID của User B<br>2. User A gửi request `/stripe/session` với ID của User B | Booking ID of User B | `StripeController@createSession` đã check `user_id == auth()->id()`. Nhưng `StripeController@success` bỏ trống check IDOR -> Bị hổng ở callback! | Critical | ❌ **Không đạt** (BUG-004, BUG-006) |
| 57 | TC-PAY-006 | Anti-Abuse Trigger | Kiểm tra gọi `markCompleted` khi hoàn tất thanh toán | Đơn hàng PENDING | 1. Thanh toán thành công qua Stripe / VNPay | Stripe/VNPay Success Callback | `BookingService::completePayment()` L928 đã kích hoạt `markCompleted()`. -> PASS | Critical | **Đạt (Pass)** |

---

## 10. PHÂN HỆ MÃ GIẢM GIÁ & BẮP NƯỚC (TC-COUP-COMBO)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 58 | TC-COUPON-001 | Nhập Coupon | Áp dụng Mã giảm giá Hợp lệ | Đơn hàng 200k, Mã `KM20` giảm 20% | 1. Nhập mã `KM20`<br>2. Bấm Áp dụng | Code: "KM20" | Trừ 40k vào tổng tiền, tổng tiền cần trả còn 160k. | High | Chưa kiểm thử |
| 59 | TC-COUPON-002 | Nhập Coupon | Áp dụng Mã giảm giá Hết hạn / Hết lượt dùng | Mã đã hết hạn | 1. Nhập mã hết hạn | Code: "EXPIRED" | Trả về lỗi: "Mã giảm giá đã hết hạn hoặc hết lượt sử dụng". | High | Chưa kiểm thử |
| 60 | TC-COUPON-003 | Nhập Coupon | Đơn hàng KHÔNG đạt giá trị tối thiểu của Coupon | Mã yêu cầu đơn từ 300k, Đơn hiện tại 150k | 1. Nhập mã giảm giá | Min order: 300.000đ | Trả về lỗi: "Đơn hàng chưa đạt giá trị tối thiểu 300.000đ". | Medium | Chưa kiểm thử |
| 61 | TC-COUPON-004 | Security Auth | Kiểm tra Endpoint `/api/apply-coupon` không require Auth | Chưa đăng nhập (Guest) | 1. Gửi request POST tới `/api/apply-coupon` | Coupon code | **Expected:** Yêu cầu đăng nhập 401.<br>**Actual:** Guest gửi request kiểm tra coupon bình thường (Thiếu Auth middleware). | Medium | ❌ **Không đạt** (BUG-005) |
| 62 | TC-COMBO-001 | Thêm Combo | Chọn Combo Bắp Nước vào Đơn đặt vé | Đang ở bước Chọn dịch vụ | 1. Chọn Combo 1 Bắp + 2 Nước (Số lượng: 2) | Combo ID, Qty = 2 | Tổng tiền đơn hàng tăng lên đúng bằng `Tổng vé + (Giá Combo * 2)`. | High | Chưa kiểm thử |
| 63 | TC-COMBO-002 | Xóa Combo | Bỏ Combo khỏi Đơn đặt vé | Đã chọn Combo | 1. Giảm số lượng Combo về 0 | Qty = 0 | Tổng tiền giảm tương ứng, xóa Combo khỏi đơn hàng. | Medium | Chưa kiểm thử |

---

## 11. PHÂN HỆ VÉ ĐIỆN TỬ, MÃ QR & CHECK-IN (TC-TKT)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 64 | TC-TKT-001 | Sinh Mã Vé | Tự động sinh Mã vé Token UUID & QR Code khi Paid | Đơn hàng thanh toán thành công | 1. Kiểm tra chi tiết booking sau khi Paid | Booking ID | Tự động tạo UUID Token duy nhất, hiển thị QR Code tra cứu vé. | Critical | Chưa kiểm thử |
| 65 | TC-TKT-002 | Email Vé | Gửi Email xác nhận kèm thông tin Vé & QR | Payment thành công | 1. Check hòm thư email của khách | Email khách hàng | Email đến thành công, đầy đủ tên phim, suất chiếu, số ghế, mã QR. | High | Chưa kiểm thử |
| 66 | TC-TKT-003 | Lịch sử Vé | Khách xem Lịch sử đặt vé trong Cá nhân | User đăng nhập | 1. Vào `/profile/booking-history` | User ID | Hiển thị danh sách vé đã đặt (PAID, USED, EXPIRED, CANCELLED). | High | Chưa kiểm thử |
| 67 | TC-TKT-004 | Staff Check-in | Nhân viên Quét/Nhập Mã vé Valid để Check-in | Staff đăng nhập rạp | 1. Nhập Mã vé / Quét QR vé `PAID` | Ticket Token | Vé đổi trạng thái từ `PAID` sang `USED`, thông báo Check-in thành công. | Critical | Chưa kiểm thử |
| 68 | TC-TKT-005 | Staff Check-in | Check-in Mã vé ĐÃ SỬ DỤNG trước đó | Vé đã `USED` | 1. Thử check-in lại mã vé đã quét rồi | Ticket Token (USED) | Cảnh báo lỗi: "Vé này đã được check-in vào lúc HH:MM". | Critical | Chưa kiểm thử |
| 69 | TC-TKT-006 | Staff Check-in | Check-in Mã vé Sai Rạp / Sai Suất chiếu | Vé của Rạp B | 1. Staff Rạp A quét vé Rạp B | Ticket Token Rạp B | **Expected:** Cảnh báo lỗi vé không thuộc rạp.<br>**Actual:** Thiếu scope validate Rạp của Staff khi check-in! | Critical | ❌ **Không đạt** (BUG-007) |

---

## 12. PHÂN HỆ VẬN HÀNH QUẦY VÉ NHÂN VIÊN (TC-STF)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 70 | TC-STF-001 | Walk-in Booking | Nhân viên Đặt vé trực tiếp tại Quầy cho khách | Staff đăng nhập | 1. Chọn Phim -> Chọn Suất chiếu -> Chọn Ghế -> Chọn Combo<br>2. Chọn phương thức "Tiền mặt"<br>3. Bấm Hoàn tất | Form Walk-in | Booking tạo thành công với status `PAID`, in vé trực tiếp cho khách. | Critical | Chưa kiểm thử |
| 71 | TC-STF-002 | Staff Assignment | Kiểm tra Middleware phân công Rạp chiếu Nhân viên | Staff được gán Rạp 1 | 1. Staff cố gắng truy cập dữ liệu quầy vé của Rạp 2 | Cinema ID = 2 | Middleware `cinema.assignment` chặn truy cập, yêu cầu đúng rạp công tác. | High | Chưa kiểm thử |
| 72 | TC-STF-003 | Shift Revenue | Thống kê Ca trực Nhân viên | Cuối ca trực Staff | 1. Vào Staff Dashboard xem tổng doanh thu tiền mặt / chuyển khoản trong ca | Shift data | Hiển thị chính xác tổng số vé đã bán và tổng số tiền đã thu trong ca. | Medium | Chưa kiểm thử |

---

## 13. PHÂN HỆ ĐÁNH GIÁ & BÌNH LUẬN (TC-REV)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 73 | TC-REV-001 | Đánh giá Phim | Khách ĐÃ VEM PHIM thực hiện Đánh giá & Rating | User có vé `USED` cho phim A | 1. Vào chi tiết Phim A<br>2. Chọn 5 sao, nhập nội dung bình luận<br>3. Bấm Gửi | Rating: 5, Content: "Phim rất hay!" | Đánh giá gửi thành công (`canReview` pass), điểm rating trung bình của phim được cập nhật. | High | Chưa kiểm thử |
| 74 | TC-REV-002 | Đánh giá Phim | Khách CHƯA MUA VÉ cố tình đánh giá phim | User chưa mua vé phim A | 1. Cố gửi bình luận đánh giá Phim A | Rating: 1, Content: "Chưa xem" | Báo lỗi: "Bạn cần mua vé và xem phim trước khi gửi đánh giá". | High | Chưa kiểm thử |
| 75 | TC-REV-003 | Moderation | Admin / Manager duyệt / ẩn đánh giá vi phạm | Role Admin | 1. Mở danh sách Reviews<br>2. Bấm Ẩn/Xóa đánh giá chứa từ ngữ thô tục | Review ID | Review bị ẩn khỏi giao diện khách hàng. | Medium | Chưa kiểm thử |

---

## 14. PHÂN HỆ QUẢN LÝ TIN TỨC & BÀI VIẾT (TC-POST)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 76 | TC-POST-001 | Tạo Bài viết | Thêm Bài viết Tin tức / Khuyến mãi mới | Role Admin/Manager | 1. Tạo bài viết mới<br>2. Nhập Tiêu đề, Tóm tắt, Nội dung RichText, Upload Ảnh đại diện, Chọn Chuyên mục<br>3. Bấm Đăng bài | `title`: "Ưu đãi Thứ 3 Vui Vẻ", `status`: "published" | Bài viết hiển thị trên trang Tin tức ngoài client. | Medium | Chưa kiểm thử |
| 77 | TC-POST-002 | Draft Post | Lưu Bài viết ở dạng Nháp (Draft) | Role Admin | 1. Tạo bài viết và chọn `status = draft` | `status`: "draft" | Bài viết được lưu trong DB nhưng KHÔNG hiển thị ngoài giao diện người dùng. | Low | Chưa kiểm thử |

---

## 15. PHÂN HỆ THỐNG KÊ & BÁO CÁO DASHBOARD (TC-DASH)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 78 | TC-DASH-001 | Admin Dashboard | Xem Thống kê Tổng quan toàn hệ thống | Role Admin | 1. Vào `/admin/dashboard` | N/A | Hiển thị đúng Tổng Doanh Thu, Số Vé Bán, Số Người Dùng, Biểu đồ doanh thu theo thời gian. | High | Chưa kiểm thử |
| 79 | TC-DASH-002 | Manager Dashboard | Xem Thống kê Doanh thu theo Rạp quản lý | Role Manager Rạp A | 1. Vào `/manager/dashboard` | N/A | Chỉ hiển thị chỉ số doanh thu và tỷ lệ lấp đầy phòng chiếu của Rạp A. | High | Chưa kiểm thử |
| 80 | TC-DASH-003 | Top Phim | Thống kê Top Phim Doanh thu cao nhất | Role Admin | 1. Xem widget Top Phim Bán Chạy | Date Range | Danh sách phim xếp hạng chuẩn xác theo tổng số tiền vé đã thu. | Medium | Chưa kiểm thử |

---

## 16. PHÂN HỆ TRỢ LÝ ẢO AI CHATBOT & BẢO MẬT HỆ THỐNG (TC-SEC)

| STT | Mã Test Case | Tên Chức Năng | Kịch Bản Kiểm Thử | Điều Kiện Tiền Quyết | Các Bước Thực Hiện | Dữ Liệu Đầu Vào | Kết Quả Mong Đợi | Ưu Tiên | Trạng Thái |
|---|---|---|---|---|---|---|---|---|---|
| 81 | TC-AI-001 | AI Chatbot | Hỏi Lịch chiếu & Giá vé qua AI Chatbot | API Gemini configured | 1. Mở cửa sổ chat<br>2. Nhập: "Hôm nay rạp có phim gì chiếu từ 18h?" | Message query | AI trả lời thông tin lịch chiếu chính xác dựa trên database hệ thống. | Medium | Bị chặn (Cần Gemini API Key) |
| 82 | TC-AI-002 | AI Context | Duy trì Ngữ cảnh hội thoại (Context History) | Chatbot active | 1. Hỏi: "Phim Lật Mặt 7 giá bao nhiêu?"<br>2. Hỏi tiếp: "Đặt cho tôi 2 vé phim đó" | History 6 messages | AI hiểu "phim đó" là Lật Mặt 7 và hướng dẫn các bước chọn suất chiếu. | Medium | Bị chặn (Cần Gemini API Key) |
| 83 | TC-SEC-001 | Security XSS | Tấn công XSS qua Form Bình luận / Bài viết | Form input public | 1. Nhập script độc hại vào ô bình luận: `<script>alert('XSS')</script>` | Input chứa HTML/JS | Blade engine tự động Escape HTML (`{{ }}`), hiển thị dạng plain text, không thực thi script. | Critical | Chưa kiểm thử |
| 84 | TC-SEC-002 | Security SQLi | Tấn công SQL Injection qua ô Tìm kiếm / Parameter | Endpoint tìm kiếm | 1. Nhập từ khóa: `' OR '1'='1` vào ô tìm kiếm phim | Search param | Eloquent ORM tự động Binding Parameter, không bị lọt câu lệnh SQL Injection. | Critical | Chưa kiểm thử |
| 85 | TC-SEC-003 | Security CSRF | Gửi Request POST không có CSRF Token | Form Submit | 1. Dùng Postman gửi request `POST /booking/select-seats` không kèm `_token` | Headers | Laravel Middleware `VerifyCsrfToken` chặn request với HTTP 419 Page Expired. | Critical | Chưa kiểm thử |

---

## TỔNG HỢP DANH SÁCH LỖI BẢO MẬT & VẬN HÀNH SAU AUDIT TỈ MỈ (UPDATED BUG LIST)

| Bug ID | Phân Hệ | Mức Độ | Mô Tả Lỗi (Vulnerability / Bug Description) | Hành Vi Mong Đợi | Hành Vi Thực Tế (Root Cause) | Trạng Thái Code | Vị Trí File / Function |
|---|---|---|---|---|---|---|---|
| **BUG-001** | Auth | **Medium** | Backdoor Route `/quick-login-staff` | Không tồn tại route backdoor | Route không còn tồn tại trong codebase | **Đã Sửa / Pas** | Legacy test case |
| **BUG-002** | AuthZ | **High** | Route Admin Coupons/Combos/Reviews bọc thiếu `role:ADMIN` | Bọc trong middleware `role:ADMIN` | Đã bọc đầy đủ tại line 26 `routes/admin.php` | **Đã Sửa / Pass** | `routes/admin.php:L26` |
| **BUG-003** | Payment | **High** | Gọi `markCompleted()` anti-abuse sau khi thanh toán thành công | Phải gọi `markCompleted()` | `BookingService::completePayment()` L928 đã gọi đúng `markCompleted()` | **Đã Sửa / Pass** | `app/Services/BookingService.php:L928` |
| **BUG-004** | Security | **Critical** | Lỗ hổng IDOR trong Stripe Success / Cancel Callback | Phải check ownership `user_id == auth()->id()` | `StripeController@success` dùng `findOrFail($id)` trực tiếp | **CHƯA SỬA (LỖI)** | `StripeController@success:L83` |
| **BUG-005** | Security | **Medium** | Route API kiểm tra Coupon `/api/apply-coupon` không require Auth | API phải bọc `auth` middleware | Route nằm ngoài middleware `auth` trong `web.php` | **CHƯA SỬA (LỖI)** | `routes/web.php:L83` |
| **BUG-006** | Payment | **Critical** | Bypass Thanh Toán Stripe bằng cách gọi GET `/stripe/success?booking_id={id}` | Phải verify Stripe Session Status với Stripe API hoặc Webhook trước khi update Paid | Controller tin tưởng URL parameter từ client GET request và set Paid ngay lập tức! | **MỚI PHÁT HIỆN** | `StripeController@success:L83-93` |
| **BUG-007** | Staff | **High** | Staff Check-in vé thiếu kiểm tra Rạp được phân công (`cinema_id`) | Staff Rạp A không được check-in vé của Rạp B | `CinemaStaffDashboardController@checkIn` không kiểm tra `cinema_id` của suất chiếu có khớp với Staff | **MỚI PHÁT HIỆN** | `CinemaStaffDashboardController@checkIn` |

---
*Bảng Test Case & Kết quả Audit được cập nhật đối soát trực tiếp từ Codebase dự án `DATN_WD21_SU26` ngày 30/08/2026.*
