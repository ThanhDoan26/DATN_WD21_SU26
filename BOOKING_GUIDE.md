# 📋 Hướng Dẫn Hệ Thống Booking Vé - movieGo

## 🎬 Tổng Quan Quy Trình

### Bước 1: Chọn Cụm Rạp
- **URL**: `/booking/movie/{movie_id}/cinema`
- **Truy cập qua**: Nút "Đặt Vé" trên card phim
- **Hiển thị**: Danh sách tất cả rạp có suất chiếu cho phim đó
- **Chức năng**:
  - Hiển thị thông tin rạp (tên, địa chỉ, thành phố, phone)
  - Đếm số phòng chiếu
  - Click vào rạp → tiếp tục bước 2

### Bước 2 & 3: Chọn Ngày & Suất Chiếu
- **URL**: `/booking/movie/{movie_id}/cinema/{cinema_id}/dates`
- **Hiển thị**:
  - **Bước 2**: Grid ngày chiếu (chỉ những ngày có suất chiếu)
  - **Bước 3**: Khi chọn ngày → hiển thị danh sách suất chiếu
- **Chức năng**:
  - Danh sách ngày được load từ API `/api/booking/dates`
  - Khi click ngày → API gọi `/api/booking/showtimes`
  - Hiển thị thông tin suất: giờ, phòng, format, số ghế trống
  - Disable suất nếu không còn ghế trống
  - Click suất chiếu → chuyển sang chọn ghế

### Bước 4: Chọn Ghế
- **URL**: `/booking/showtime/{showtime_id}/seats`
- **Yêu cầu**: User phải đăng nhập
- **Hiển thị**:
  - Sơ đồ ghế interactive (hàng A, B, C...)
  - Legend: ghế trống, ghế chọn, ghế đã đặt, ghế VIP
  - Thanh tóm tắt bên phải: ghế chọn, tổng tiền
- **Chức năng**:
  - Click ghế để chọn/bỏ chọn
  - Ghế đã đặt (đen) không click được
  - Hiển thị danh sách ghế chọn (ví dụ: A1, A2, B5)
  - Tính tổng tiền dựa trên số ghế × giá vé
  - Click "Tiếp tục thanh toán" → chuyển sang checkout

## 🔌 API Endpoints

### GET /api/booking/dates
**Tham số**:
```
GET /api/booking/dates?movie_id=1&cinema_id=1
```

**Response**:
```json
{
  "data": [
    "2026-06-20",
    "2026-06-21",
    "2026-06-22"
  ],
  "message": "Danh sách ngày chiếu"
}
```

**Logic**:
- Lấy tất cả suất chiếu của phim + rạp
- Chỉ lấy suất chiếu trong tương lai (start_time > now)
- Chỉ lấy suất chiếu đang hoạt động (SCHEDULED hoặc ONGOING)
- Group by DATE(start_time)
- Sort theo thời gian

### GET /api/booking/showtimes
**Tham số**:
```
GET /api/booking/showtimes?movie_id=1&cinema_id=1&date=2026-06-20
```

**Response**:
```json
{
  "data": [
    {
      "id": 5,
      "time": "14:00",
      "start_time": "2026-06-20T14:00:00Z",
      "end_time": "2026-06-20T16:30:00Z",
      "room_name": "Cinema 1",
      "room_format": "2D",
      "cinema_name": "CGV Landmark 81",
      "available_seats": 42
    },
    {
      "id": 6,
      "time": "17:00",
      "start_time": "2026-06-20T17:00:00Z",
      "end_time": "2026-06-20T19:30:00Z",
      "room_name": "Cinema 2",
      "room_format": "3D",
      "cinema_name": "CGV Landmark 81",
      "available_seats": 28
    }
  ],
  "message": "Danh sách suất chiếu"
}
```

**Logic**:
- Lọc suất chiếu theo phim + rạp + ngày
- Tính số ghế trống = tổng ghế - (ghế status Pending/Paid)
- Sort theo start_time

## 📊 Database Relations

```
Movie (1) ---> (N) Showtime
Cinema (1) ---> (N) Room
Room (1) ---> (N) Seat
Room (1) ---> (N) Showtime
Showtime (1) ---> (N) Booking
Booking (1) ---> (N) BookedSeat
Seat (1) ---> (N) BookedSeat
User (1) ---> (N) Booking
```

## 🛡️ Auth & Middleware

- Bước 1-3: **Public** - Không cần đăng nhập
- Bước 4 (chọn ghế): **Protected** - Cần `auth` middleware
- Khi chưa đăng nhập + click "Tiếp tục" → Redirect login

## 📝 Component & Views

### select-cinema.blade.php
- Grid cinema cards với info
- Hiển thị số phòng chiếu
- Button "Chọn Rạp Này"

### select-dates-and-showtimes.blade.php
- 2 step lần lượt
- Bước 2: Date picker (grid)
- Bước 3: Showtime list (card)
- Load dữ liệu via JavaScript fetch
- Navigation: quay lại rạp | tiếp tục ghế

### select-seats.blade.php
- Responsive seat map
- 2 cột layout: seat map (2/3 width) + summary (1/3 width)
- Interactive seat selection
- Real-time price calculation
- Sticky sidebar

## 🎨 UI/UX Features

✅ Loading states - Spinner khi fetch data
✅ Error handling - Hiển thị thông báo lỗi
✅ Responsive design - Mobile friendly
✅ Color coding:
   - Xám: ghế trống
   - Đỏ (primary): ghế chọn
   - Xám đậm: ghế đã đặt (disabled)
   - Vàng: ghế VIP

## 🔄 Session/State Management

- Bước 1→2: Movie + Cinema info truyền via URL
- Bước 2→3: Movie + Cinema từ URL
- Bước 3→4: Showtime ID từ URL
- **Không sử dụng sessionStorage** (dễ bị clear, hard refresh)
- **Sử dụng URL parameters** (persistent, shareable)

## 🚀 Future Improvements

1. **Coupon/Promotion** - Apply discount
2. **Seat Categories** - Different prices for different types
3. **Group Booking** - Book multiple showtimes
4. **Saved Seats** - Save favorite seats
5. **Payment Integration** - VNPay, Momo, etc.
6. **Email Confirmation** - Send booking details
7. **QR Code** - Generate QR for tickets
8. **Cancellation** - Allow cancellation before certain time

## 📞 Support

- Nếu gặp lỗi API → check browser console
- Nếu ghế không update → hard refresh F5
- Nếu chọn ghế bị lag → check server performance
