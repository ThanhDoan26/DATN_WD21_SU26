# TEST CASE MATRIX — Website Đặt Vé Xem Phim (DATN_WD21_SU26)

**Ngày audit:** 18/08/2026  
**Phiên bản:** Codebase tại thời điểm audit  
**Phương pháp:** Code Review + Static Analysis (chưa execution test)  
**Người kiểm thử:** QA Auditor (Antigravity)

---

> **IMPORTANT**: Trạng thái test case được đánh ĐÚNG nguyên tắc QA:
> - **Không đạt**: Đã xác minh qua code review rằng behavior sai hoặc vi phạm business rule
> - **Chưa kiểm thử**: Chưa đủ evidence để kết luận (chưa execution test)
> - **Bị chặn**: Không thể test do external dependency
> - **Đạt**: CHỈ khi có evidence đủ mạnh từ code + unit test

---

## 1. AUTHENTICATION (TC-AUTH)

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Điều kiện tiên quyết | Các .1. GET /register 2. Nhập thông tin 3. Submit | name, email, password, password_confirmation | Tạo tài khoản thành công, redirect dashboard | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | Route: POST /register → RegisteredUserController |
| TC-AUTH-002 | Auth | Đăng ký | Đăng ký email đã tồn tại | Email đã tồn tại DB | 1. POST /register email đã tồn tại | email=admin@example.com | Validation error email unique | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | |
| TC-AUTH-003 | Auth | Đăng nhập | Đăng nhập credentials hợp lệ | User có tài khoản | 1. POST /login email+password | email, password đúng | Đăng nhập thành công, redirect theo role | Chưa thực hiện kiểm thử | Critical | Chưa kiểm thử | QA Auditor | 18/08/2026 | | Route: POST /login → AuthenticatedSessionController@store |
| TC-AUTH-004 | Auth | Đăng nhập | Đăng nhập password sai | User có tài khoản | 1. POST /login password sai | email đúng, password sai | Lỗi authentication | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | |
| TC-AUTH-005 | Auth | Đăng xuất | Đăng xuất hệ thống | User đăng nhập | 1. POST /logout | N/A | Đăng xuất, redirect home | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | |
| TC-AUTH-006 | Auth | Quên mật khẩu | Request reset password | User có tài khoản | 1. POST /forgot-password email | email | Gửi email reset password | Chưa thực hiện kiểm thử | Medium | Chưa kiểm thử | QA Auditor | 18/08/2026 | | |
| TC-AUTH-007 | Auth | Redirect theo Role | Admin redirect về admin dashboard | Admin đăng nhập | 1. Đăng nhập Admin 2. GET /dashboard | Admin credentials | Redirect /admin | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | web.php L29-30 |
| TC-AUTH-008 | Auth | Quick Login Staff | /quick-login-staff bypass auth | Guest | 1. GET /quick-login-staff | N/A | Auto-login Staff không cần password | Code auto-login staff → Lỗ hổng bảo mật production | Critical | Không đạt | QA Auditor | 18/08/2026 | BUG-001 | web.php L150-161: Route backdoor |

---

## 2. AUTHORIZATION (TC-AUTHZ)

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Điều kiện tiên quyết | Các bước kiểm thử | Dữ liệu kiểm thử | Kết quả mong đợi | Kết quả thực tế | Mức độ ưu tiên | Trạng thái | Người kiểm thử | Ngày kiểm thử | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| TC-AUTHZ-001 | Authorization | Admin access | USER truy cập admin | role=USER | 1. GET /admin | N/A | HTTP 403 | Chưa thực hiện kiểm thử | Critical | Chưa kiểm thử | QA Auditor | 18/08/2026 | | admin.php: role:ADMIN middleware |
| TC-AUTHZ-002 | Authorization | Manager access | USER truy cập manager | role=USER | 1. GET /manager/dashboard | N/A | HTTP 403 | Chưa thực hiện kiểm thử | Critical | Chưa kiểm thử | QA Auditor | 18/08/2026 | | manager.php: role:MANAGER |
| TC-AUTHZ-003 | Authorization | Staff access | USER truy cập staff | role=USER | 1. GET /staff/dashboard | N/A | HTTP 403 | Chưa thực hiện kiểm thử | Critical | Chưa kiểm thử | QA Auditor | 18/08/2026 | | staff.php: role:STAFF |
| TC-AUTHZ-004 | Authorization | Coupon/Combo admin | STAFF truy cập admin coupons | role=STAFF | 1. GET /admin/coupons | N/A | HTTP 403 — chỉ ADMIN | Routes nằm NGOÀI role:ADMIN (admin.php L113-120) | Critical | Không đạt | QA Auditor | 18/08/2026 | BUG-002 | Coupons/Combos/Reviews routes thiếu role middleware |
| TC-AUTHZ-005 | Authorization | Guest seat access | Guest truy cập chọn ghế | Guest | 1. GET /booking/showtime/{id}/seats | N/A | Redirect /login | Chưa thực hiện kiểm thử | High | Chưa kiểm thử | QA Auditor | 18/08/2026 | | middleware auth web.php L67 |
| TC-AUTHZ-006 | Authorization | Booking ownership | User A xem checkout User B | User A đăng nhập | 1. GET /checkout/success?booking_id=B | Booking B | 404 — Auth::id() check | Chưa thực hiện kiểm thử | Critical | Chưa kiểm thử | QA Auditor | 18/08/2026 | | CheckoutController@success L302 |

---

## 3-9. MOVIE / CINEMA / ROOM / SEAT / SHOWTIME / BOOKING / SEAT HOLD

*(Xem file CSV đính kèm cho bảng đầy đủ — tổng hợp dưới đây chỉ ghi các test case quan trọng nhất)*

### SHOWTIME — Highlight

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|
| TC-SHOW-001 | Showtime | Format compat Create | Movie 2D + Room 2D | Tạo thành công | Chưa kiểm thử | Chưa kiểm thử | | CompatibleFormatRule gọi trong store() |
| TC-SHOW-002 | Showtime | Format compat Create | Movie 3D + Room 2D | Validation error | Chưa kiểm thử — Unit test verify matrix | Chưa kiểm thử | | Rule + 35 unit tests |
| TC-SHOW-003 | Showtime | Format compat Update | Update room incompatible | Validation error | Chưa kiểm thử | Chưa kiểm thử | | Rule cũng áp dụng khi update |
| TC-SHOW-004 | Showtime | Overlap | Trùng thời gian cùng phòng | Validation error | Chưa kiểm thử | Chưa kiểm thử | | validateNoOverlap() |
| TC-SHOW-005 | Showtime | Past start_time | start_time quá khứ | Nên reject | Code KHÔNG validate after:now | Chưa kiểm thử | | Potential issue |

### BOOKING — Highlight

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|
| TC-BOOK-001 | Booking | Create | Tạo booking hợp lệ | Pending + RESERVED | Chưa kiểm thử | Chưa kiểm thử | | BookingService@createBooking |
| TC-BOOK-002 | Booking | Max seats | > 8 ghế | Error 422 | Chưa kiểm thử | Chưa kiểm thử | | Double validation: controller + service |
| TC-BOOK-003 | Booking | Active guard | Pending khác showtime → block | Error | Chưa kiểm thử | Chưa kiểm thử | | |
| TC-BOOK-004 | Booking | Seat validation | Ghế không liền kề | Error | Chưa kiểm thử | Chưa kiểm thử | | SeatSelectionValidationService |

### SEAT HOLD — Highlight

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|
| TC-HOLD-001 | Seat Hold | Mechanism | Booking Pending = hold | RESERVED status | Chưa kiểm thử | Chưa kiểm thử | | SeatHold chỉ tracking |
| TC-HOLD-002 | Seat Hold | Concurrent | User B chọn ghế User A | Blocked by lock | Chưa kiểm thử | Chưa kiểm thử | | Redis lock + SELECT FOR UPDATE |
| TC-HOLD-003 | Seat Hold | Expiration | 10 min → release | Cleanup logic | Chưa kiểm thử | Chưa kiểm thử | | On-demand cleanup |

---

## 10. PAYMENT (TC-PAY) — CRITICAL

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế | Mức độ ưu tiên | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|---|
| TC-PAY-001 | Payment | Stripe session | Tạo Stripe session | Redirect URL | Chưa kiểm thử | Critical | Bị chặn | | Cần Stripe API key |
| TC-PAY-002 | Payment | VnPay create | Tạo VnPay URL | Payment URL | Chưa kiểm thử | Critical | Bị chặn | | Cần VnPay config |
| TC-PAY-003 | Payment | Bypass service | Stripe/VnPay không gọi completePayment() | Phải gọi service | Direct update, tracking bị miss | Critical | Không đạt | BUG-003 | SeatHoldAbuseService::markCompleted() KHÔNG trigger |
| TC-PAY-004 | Payment | IDOR | User A pay booking User B | Phải check ownership | findOrFail KHÔNG check user_id | Critical | Không đạt | BUG-004 | IDOR vulnerability |
| TC-PAY-005 | Payment | Hash invalid | VnPay hash sai | Reject | Chưa kiểm thử | Critical | Chưa kiểm thử | | VnPayController@return hash check |
| TC-PAY-006 | Payment | Already paid | Pay booking đã Paid | 400 error | Chưa kiểm thử | High | Chưa kiểm thử | | Both controllers check |

---

## 11-14. COUPON / COMBO / TICKET / REVIEW

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|
| TC-COUPON-001 | Coupon | Apply valid | Mã hợp lệ → discount | Success | Chưa kiểm thử | | |
| TC-COUPON-002 | Coupon | Expired | Mã hết hạn | Error | Chưa kiểm thử | | |
| TC-COUPON-003 | Coupon | No auth endpoint | /api/apply-coupon no auth | Nên yêu cầu auth | Không đạt | BUG-005 | web.php L86 |
| TC-COMBO-001 | Combo | Add to booking | Thêm combo | Total tăng | Chưa kiểm thử | | |
| TC-COMBO-002 | Combo | Invalid | Combo không tồn tại | Error | Chưa kiểm thử | | |
| TC-TICKET-001 | Ticket | View history | User xem lịch sử | Danh sách bookings | Chưa kiểm thử | | |
| TC-TICKET-002 | Ticket | Token gen | Paid → UUID token | Tự động tạo | Chưa kiểm thử | | Booking model booted() |
| TC-REV-001 | Review | Create | User đã xem phim review | Success | Chưa kiểm thử | | |
| TC-REV-002 | Review | No ticket | User chưa mua vé | Error | Chưa kiểm thử | | canReview check |

---

## 15-18. STAFF / DASHBOARD / AI CHATBOT / ANTI-SPAM

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|
| TC-STAFF-001 | Staff | Dashboard | Staff xem KPI | KPI data | Chưa kiểm thử | | |
| TC-STAFF-002 | Staff | Check-in | Check-in vé | USED status | Chưa kiểm thử | | |
| TC-STAFF-003 | Staff | Walk-in | Booking tại quầy | Success | Chưa kiểm thử | | cinema.assignment middleware |
| TC-DASH-001 | Dashboard | Admin | Admin thống kê | Statistics data | Chưa kiểm thử | | DashboardService |
| TC-AI-001 | AI | Greeting | Gửi chào hỏi | AI response | Bị chặn | | Cần Gemini API |
| TC-AI-002 | AI | Follow-up | Context tracking | Context maintained | Bị chặn | | 6 messages history |
| TC-AI-003 | AI | No auth web | /chat/web no auth | Vẫn hoạt động | Bị chặn | | Route public |
| TC-SPAM-001 | Anti-Spam | Cooldown | Cooldown 15 phút | Block re-select | Chưa kiểm thử | | BookingService L224-242 |
| TC-SPAM-002 | Anti-Spam | Restriction | 5 expired → block | Block 30 min | Chưa kiểm thử | | SeatHoldAbuseService |
| TC-SPAM-003 | Anti-Spam | Rate limit | >10 req/min | 429 | Chưa kiểm thử | | throttle:booking |

---

## 19. SECURITY (TC-SEC)

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Mã lỗi | Ghi chú |
|---|---|---|---|---|---|---|---|
| TC-SEC-001 | Security | IDOR Booking payment | User A pay booking B | Block | NOT blocked | Không đạt | BUG-004 | |
| TC-SEC-002 | Security | Fake seat ID | Seat ID room khác | Error | Chưa kiểm thử | Chưa kiểm thử | | BookingService check |
| TC-SEC-003 | Security | SQL Injection | SQL qua input | Auto-escape | Chưa kiểm thử | Chưa kiểm thử | | Laravel Eloquent |
| TC-SEC-004 | Security | XSS | Script tag trong comment | Auto-escape | Chưa kiểm thử | Chưa kiểm thử | | Blade {{ }} |
| TC-SEC-005 | Security | Admin routes leak | Verified user access coupons | 403 | Allowed | Không đạt | BUG-002 | |
| TC-SEC-006 | Security | Backdoor | /quick-login-staff | Không tồn tại | Tồn tại, public | Không đạt | BUG-001 | |

---

## 20. REGRESSION (TC-REG)

| Mã Test Case | Phân hệ | Chức năng | Kịch bản kiểm thử | Kết quả mong đợi | Trạng thái | Ghi chú |
|---|---|---|---|---|---|
| TC-REG-001 | Regression | Full Booking Flow | Login→Movie→Showtime→Seat→Hold→Coupon→Combo→Payment→Ticket→History | All steps pass | Chưa kiểm thử | Cần running app + payment |
| TC-REG-002 | Regression | Authorization Flow | Verify 4 roles | Đúng phân quyền | Chưa kiểm thử | |
| TC-REG-003 | Regression | Seat Protection | Hold→Block→Pay→Cancel→Re-select | Protection works | Chưa kiểm thử | 2 concurrent sessions |
| TC-REG-004 | Regression | Chatbot Flow | Question→Intent→Knowledge→AI→Save | Full flow works | Bị chặn | Gemini API |

---

## DANH SÁCH BUG ĐÃ PHÁT HIỆN

| Bug ID | Mức độ | Mô tả | Expected | Actual | Root Cause | File/Function |
|--------|--------|-------|----------|--------|------------|---------------|
| BUG-001 | Critical | Backdoor /quick-login-staff cho phép login không cần password | Route không tồn tại trên production | Route public, auto-login Staff | Route không có middleware env/protection | web.php L150-161 |
| BUG-002 | Critical | Coupons/Combos/Reviews admin routes thiếu role middleware | Chỉ ADMIN truy cập | Mọi authenticated+verified user truy cập được | Routes nằm NGOÀI role:ADMIN group | admin.php L113-129 |
| BUG-003 | Critical | Stripe/VnPay bypass BookingService::completePayment() | Payment gọi completePayment() | Direct update, SeatHoldAbuseService::markCompleted() KHÔNG trigger | Controllers update trực tiếp | StripeController@success L83-99, VnPayController@return L125-136 |
| BUG-004 | Critical | Payment endpoints KHÔNG kiểm tra booking ownership (IDOR) | Check user_id ownership | User A có thể tạo payment cho booking User B | findOrFail không kèm where user_id | StripeController@createSession L18, VnPayController@createPayment L17 |
| BUG-005 | Medium | /api/apply-coupon không yêu cầu authentication | Yêu cầu auth | Guest có thể validate coupon | Route ngoài middleware auth | web.php L86 |
