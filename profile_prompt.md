

Hãy đóng vai một Senior Frontend / Full-Stack Engineer (React / Blade + Tailwind CSS). Bạn được giao nhiệm vụ thiết kế và lập trình trang "Hồ Sơ Cá Nhân theo Vai Trò"  cho hệ thống 

Trang web sử dụng chủ đề Modern Dark Mode cao cấp (nền #0B0F19, thẻ #131927, viền #1F2937, nhấn đỏ #EF4444 và tím #9333EA).

1. MỤC TIÊU & KIẾN TRÚC VAI TRÒ 


🛡️ Admin (Quản trị viên):

KPI: Doanh thu toàn hệ thống, Tổng số phim đang quản lý, Số vé bán ra hôm nay, Số cụm rạp Active.

Thẻ thông tin: Nhãn HQ (Toàn hệ thống), Đặc quyền Full Control, Nút lối tắt đến Trang Admin.

Nhật ký: Ghi lại các thao tác cấp cao (Tạo lịch chiếu, Duyệt hoàn tiền, Cấu hình giá vé).

🏢 Manager (Quản lý cụm rạp):

KPI: Doanh thu rạp chi nhánh, Số phòng chiếu (IMAX/2D), Combo bắp nước bán ra, Nhân viên đang trong ca.

Thẻ thông tin: Tên chi nhánh cụ thể (VD: movieGo Cầu Giấy), Phân công ca trực.

Nhật ký: Phân ca nhân viên, Ghi nhận bảo trì máy chiếu, Nhập kho bắp nước.

🎟️ Staff (Nhân viên soát vé & POS):

KPI: Số vé đã quét/bán qua POS, Ca làm việc hôm nay, Doanh số quầy, Đánh giá phục vụ (Star).

Thẻ thông tin: Staff ID, Lối tắt đến Màn hình Bán vé POS Walk-in.

Nhật ký: Lịch sử quét mã QR vào phòng chiếu, In hóa đơn POS.

🍿 Customer (Khách hàng xem phim):

KPI: Điểm tích lũy, Vé phim đã đặt, Voucher giảm giá, Combo đã dùng.

Thẻ thông tin: Thẻ thành viên GOLD/VIP với thanh tiến trình tích điểm thăng hạng.

Nhật ký: Lịch sử đặt vé, Xem mã QR Code Vé Điện Tử (Mở Modal Popup).

2. BỐ CỤC MÀN HÌNH (LAYOUT & UI STRUCTURE)


Brand Logo: Icon m gradient đỏ-tím + tên thương hiệu movieGo.






Badge hiển thị cấp độ tài khoản hiện tại với hiệu ứng phát sáng mờ.

Thanh Navigation Tabs nằm ngang:

[Thông tin] - Form chỉnh sửa thông tin cá nhân.

[Bảo mật & 2FA] - Đổi mật khẩu & Cấu hình ứng dụng 2FA.

[Nhật ký Thao tác / Lịch sử] - Đổi tên động theo Role.

[Thiết bị & Phân quyền] - Quản lý thiết bị đang đăng nhập & Bảng quyền hạn.

C. Lưới KPI Grid (4 Thẻ Thống Kê Nhanh)

Render động 4 thẻ KPI chứa Icon, Nhãn và Giá trị con số riêng cho từng Role.

D. Bố Cục Main (2 Cột 8-4)

Cột Trái (8 Cột): Nội dung chính theo Tab

Tab 1 (Form Thông Tin): Họ tên, Số điện thoại (có badge Đã xác thực), Email (đóng băng chọn cố định để bảo mật), Ngày sinh, Giới tính, Chi nhánh rạp.

Tab 2 (Bảo Mật & 2FA):

Box Bật/Tắt 2FA Authenticator.

Form Đổi Mật Khẩu (Mật khẩu hiện tại, Mật khẩu mới, Xác nhận).

Password Strength Meter: Thanh đo độ mạnh mật khẩu (3 mức: Yếu - Trung bình - Cực mạnh).

Icon Mắt (Eye Toggle) để Ẩn/Hiện mật khẩu.

Tab 3 (Nhật Ký Thao Tác / Lịch Sử Vé): Danh sách hành động dạng timeline kèm Badge trạng thái. Hỗ trợ bấm nút xem QR Code Vé Điện Tử nếu là Customer.

Tab 4 (Thiết Bị & Phân Quyền):

Danh sách các thiết bị đang duy trì phiên đăng nhập (Windows PC, iPhone...).

Nút "Đăng xuất khỏi mọi thiết bị khác".

Bảng danh sách các Quyền hạn hợp lệ (Permissions) của tài khoản.

Cột Phải (4 Cột): Identity Card & Danger Zone

User Identity Card:

Background Accent Banner màu gradient.

Avatar dạng Vòng tròn Gradient kèm Camera Upload Trigger (Cho phép bấm chọn ảnh từ máy tính để xem thử avatar trực tiếp).

Badge Role thương hiệu.

Khung Đặc quyền Vai trò (Role Privileges).

Loyalty Card (Thẻ Tích Điểm): Chỉ hiển thị khi chọn Role Customer (Khách hàng) với thanh tiến trình % nâng hạng.

Khu Vực Nguy Hiểm (Danger Zone):

Đóng khung viền đỏ cảnh báo.

Nút "Vô Hiệu Hóa / Xóa Tài Khoản" kèm hộp thoại xác nhận an toàn (confirm).

3. MODAL VÉ ĐIỆN TỬ & TOAST NOTIFICATION

Ticket QR Code Modal:

Modal hiển thị khi Khách hàng bấm nút xem QR vé.

Backdrop mờ xám (backdrop-blur-md).

Mã QR Code đồ họa vector SVG trong suốt kèm thông tin suất chiếu, tên phim và số ghế.

Toast Notification:

Thông báo nổi ở góc dưới bên phải màn hình khi người dùng lưu form, đổi mật khẩu, hoặc chuyển đổi Role.

4. YÊU CẦU KỸ THUẬT & TRẢI NGHIỆM UX

Single-File Standalone: Toàn bộ HTML, CSS (Tailwind CSS CDN) và JavaScript logic nằm gọn trong 01 File duy nhất.

Dark Mode Aesthetic: Sử dụng hiệu ứng Glassmorphism (backdrop-filter: blur), ambient glow đốm sáng phát sáng chìm dưới nền.

Responsive 100%: Hiển thị mượt mà trên Điện thoại, Tablet và Màn hình máy tính lớn.

Mã nguồn sạch & Đầy đủ: Khởi tạo dữ liệu mẫu ROLE_DATA bằng JavaScript object để hỗ trợ việc switch đổi thông tin thời gian thực mượt mà.

Hãy viết mã nguồn hoàn chỉnh theo đúng cấu trúc trên!

Điểm mạnh:

Role-based IA đúng: mỗi role (Admin/Manager/Staff/Customer) có KPI, thẻ thông tin, nhật ký riêng, nhưng dùng chung một khung tab (Thông tin / Bảo mật / Nhật ký / Thiết bị) — tránh viết 4 trang profile riêng lẻ, dễ maintain.
Layout 8-4 + Identity Card + Loyalty Card + Danger Zone là các pattern quen thuộc, an toàn về UX.
Tab Bảo mật khá đầy đủ theo chuẩn hiện đại: 2FA, đo độ mạnh mật khẩu, quản lý thiết bị/phiên đăng nhập, logout toàn bộ thiết bị khác.
QR vé điện tử cho Customer là điểm nhấn đúng chất domain rạp phim, không phải feature "cho có".
Có bảng màu và hiệu ứng glass/glow cụ thể ngay từ đầu — code sẽ không bị mơ hồ về mặt thị giác.

Cần làm rõ trước khi build:

Mâu thuẫn stack: đầu bài nói "React / Blade" nhưng yêu cầu kỹ thuật lại là "Single-File HTML/CSS/JS thuần" + Tailwind qua CDN. File tĩnh rất hợp để demo duyệt UI nhanh, nhưng Tailwind CDN không khuyến nghị cho production (không purge được class thừa, bundle nặng), và file này cũng không map thẳng vào component React hay Blade partial — sẽ phải viết lại sau. Nên xác định trước đây là bản prototype để duyệt UI hay code build thẳng production, vì hai hướng dẫn tới cấu trúc khác nhau.
Dữ liệu mẫu: ROLE_DATA mới là object JS tĩnh, chưa có hợp đồng API (endpoint, shape response) cho profile/KPI/nhật ký/thiết bị/quyền hạn. Nên phác trước để lúc nối backend thật không phải đổi lại cấu trúc state.
Mục đích "Switch Role" chưa rõ: công cụ demo/QA để xem nhanh UI 4 role, hay tính năng thật cho user đổi vai trò. Nếu là demo, nên tách riêng bằng cờ preview-mode ẩn, tránh vô tình để phía client (một tài khoản Customer) tự đổi sang xem giao diện/dữ liệu Admin.
Chi tiết bảo mật: đổi mật khẩu nên có tuỳ chọn đăng xuất các thiết bị khác kèm theo; 2FA nên có mã backup/khôi phục phòng khi mất thiết bị; email "đóng băng" thì cần một luồng đổi email riêng có xác thực, chứ không phải khoá vĩnh viễn không có cách nào thay đổi.
QR Code: nếu cần quét được thật (mã hoá dữ liệu ghế/suất chiếu) thì phải dùng thư viện sinh QR (vd qrcode.js) — SVG trang trí thuần không mã hoá được gì cả. Nên chốt rõ: mockup thị giác hay QR chức năng thật.
Avatar upload: spec mới yêu cầu xem trước bằng FileReader, chưa có lưu lên server, giới hạn dung lượng/định dạng hay crop ảnh — nên ghi rõ là "preview-only" ở giai đoạn này.
Bảng Quyền hạn (Tab 4) nên khẳng định rõ là read-only, chỉ hiển thị quyền của chính user đó — tránh hiểu nhầm thành màn hình cấp quyền cho người khác.
Accessibility chưa được nhắc tới: tương phản đỏ/tím trên nền tối (nhất là text trạng thái/danger), focus trap + phím ESC cho modal QR, ARIA cho tab điều hướng — quan trọng vì đây đúng là các luồng nhạy cảm (đổi mật khẩu, xoá tài khoản).
Responsive cho lưới KPI và layout 8-4 chưa mô tả hành vi cụ thể trên mobile. Gợi ý: KPI 4 cột desktop → 2 cột tablet → 1-2 cột mobile; cột phải (Identity Card) nên lên đầu khi stack trên mobile, để user thấy ngay "đây là ai" trước khi đọc nội dung tab.
Form validate/loading/error state (định dạng SĐT, ngày sinh, lưu thất bại...) chưa có trong spec — nên thêm dù chỉ ở mức happy-path cho bản đầu tiên.