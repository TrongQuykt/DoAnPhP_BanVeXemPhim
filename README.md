# 🎬 BanVeXemPhim - Website Đặt Vé Xem Phim Online
BanVeXemPhim là một website đặt vé xem phim trực tuyến hiện đại, được thiết kế để mang lại trải nghiệm tiện lợi và nhanh chóng cho người dùng. Với giao diện thân thiện, tính năng đa dạng và hệ thống quản lý suất chiếu thông minh, dự án này không chỉ phục vụ người dùng cuối mà còn hỗ trợ quản lý rạp chiếu phim một cách hiệu quả.
# 📋 Mô tả dự án
BanVeXemPhim là một nền tảng đặt vé xem phim trực tuyến, cho phép người dùng:
- Tìm kiếm và đặt vé nhanh chóng: Xem lịch chiếu phim, chọn rạp, phòng chiếu và ghế ngồi chỉ trong vài bước.
- Quản lý rạp chiếu phim: Hỗ trợ admin quản lý phim, suất chiếu, rạp, phòng chiếu và khu vực một cách dễ dàng.
- Hệ thống bảo mật: Đảm bảo thông tin người dùng được bảo vệ an toàn.
- Giao diện responsive: Tương thích trên cả máy tính và thiết bị di động.
  
Dự án được xây dựng với mục tiêu tối ưu hóa trải nghiệm người dùng và quản lý hệ thống rạp chiếu phim một cách chuyên nghiệp.

# 🚀 Tính năng nổi bật
1. Đối với người dùng (User)
- 🔍 Tìm kiếm phim và lịch chiếu: Xem danh sách phim đang chiếu, xem Trailer, lịch chiếu theo ngày, rạp, phòng và khu vực.
- 🎟️ Đặt vé trực tuyến: Chọn ghế, chọn combo, đặt vé và thanh toán online (hỗ trợ VNPAY, MOMO, PayPal, Stripe).
- 📅 Xem lịch sử đặt vé: Theo dõi các vé đã đặt và trạng thái vé.
- 🦢 Cập nhật hồ sơ: Thay đổi Avatar, thông tin tài khoản, mật khẩu.
- ☎️ Liên hệ: Gửi liên hệ hỗ trợ cho admin.
- 📕 Blog: Xem các tin tức phim, các thông tin về chương trình khuyến mãi
2. Đối với quản trị viên (Admin)
- 🎬 Quản lý phim: Thêm, xóa, sửa thông tin phim (tên phim, thể loại, phân loại, đạo diễn, diễn viên, mô tả, thời lượng, poster, trailer, v.v.).
- 📆 Quản lý suất chiếu:
  - Thêm suất chiếu cho nhiều rạp và phòng chiếu cùng lúc.
  - Kiểm tra trùng lặp suất chiếu để tránh xung đột.
  - Cập nhật trạng thái suất chiếu (Online/Offline).
- 🏬 Quản lý rạp và phòng chiếu:
  - Thêm, sửa, xóa thông tin khu vực, rạp chiếu phim và phòng chiếu.
  - Gán phòng chiếu cho từng rạp.
- 👥 Quản lý người dùng: Xem danh sách người dùng, quản lý tài khoản và lịch sử đặt vé.
- 📊 Thống kê và báo cáo: Xem báo cáo doanh thu, số lượng vé bán ra theo phim, rạp hoặc thời gian.
- 🗂️ Quản lý bài viết, chủ đề, thanh navbar, combo, ghế,...

# 🛠️ Công nghệ sử dụng
Dự án được xây dựng với các công nghệ hiện đại, đảm bảo hiệu suất và khả năng mở rộng:
- Frontend:
  - HTML, CSS, JavaScript, Boostrap
- Backend:
  - PHP 7.4+
  - MySQL
- Công cụ hỗ trợ:
  - XAMPP (môi trường phát triển local)
  - Git & GitHub (quản lý mã nguồn)
  - Visual Studio Code (trình soạn thảo mã)
# 📦 Cài đặt và triển khai
Dưới đây là hướng dẫn chi tiết để cài đặt và chạy dự án trên máy local của bạn.
## Yêu cầu hệ thống
- Hệ điều hành: Windows, macOS hoặc Linux
- XAMPP (bao gồm Apache và MySQL) phiên bản 7.4 trở lên
- Trình duyệt: Chrome, Firefox, Edge (phiên bản mới nhất)
- Git (để clone dự án từ GitHub)
### Hướng dẫn cài đặt
#### Bước 1: Clone dự án từ GitHub
Mở terminal và chạy lệnh sau để clone dự án:
`git clone https://github.com/TronqQuyk/DoAnPHP_BanVeXemPhim.git`
### Bước 2: Tải và cài đặt PHP (nếu máy tính chưa có PHP)
PHP là một ngôn ngữ lập trình phía server, và bạn cần cài đặt nó trên máy tính để chạy các file PHP. Dưới đây là các bước chi tiết:
#### 1: Tải PHP
- Truy cập trang chính thức của PHP: [PHP](https://www.php.net/downloads.php)
#### 2: Chọn phiên bản PHP phù hợp
- Chọn bản **Thread Safe (TS)** nếu bạn không sử dụng PHP với các server như Apache hoặc Nginx trong môi trường đa luồng. Nếu dùng XAMPP (như bạn đã đề cập), bạn có thể chọn **Non-Thread Safe (NTS)**.
#### 3: Thêm PHP vào biến môi trường PATH:
- Nhấn Win + R, gõ sysdm.cpl và nhấn Enter để mở System Properties.
- Chuyển sang tab Advanced → Nhấn Environment Variables.
- Trong phần System Variables, tìm biến Path → Nhấn Edit → Thêm đường dẫn C:\php → Nhấn OK.
#### 4: Kiểm tra cài đặt
Mở Command Prompt (cmd) và chạy:
`php -v`
Nếu hiện phiên bản PHP (ví dụ: PHP 7.4.33), bạn đã cài đặt thành công.
### Bước 3: Cài đặt XAMPP
- Tải và cài đặt XAMPP từ [XAMP](https://www.apachefriends.org/index.html).
- Khởi động Apache và MySQL trong XAMPP Control Panel.
### Bước 4: Cấu hình cơ sở dữ liệu
1. Mở phpMyAdmin (truy cập http://localhost/phpmyadmin).
2. Import file SQL **project_film.sql** trong dự án:
### Bước 5: Cấu hình kết nối cơ sở dữ liệu
1. Mở file **config/dbcon.php** trong dự án.
2. Cập nhật thông tin kết nối MySQL (thường không cần thay đổi nếu bạn sử dụng XAMPP mặc định):

`<?php
$host = 'localhost';
$dbname = 'project_film';
$username = 'root';
$password = ''; // Mặc định trên XAMPP là rỗng
?>`

### Bước 6: Cài đặt extension cần thiết
Để chạy PHP server trên VS Code và sử dụng port 3000, bạn cần cài đặt extension **PHP Server**.
Ngoài ra, nếu bạn muốn có tính năng live reload (tự động làm mới trang khi lưu file), bạn có thể kết hợp với **Live Server**.
**Extension cần cài đặt:**
- **PHP Server**: Dùng để chạy PHP trên local server.
- **Live Server** (tùy chọn): Hỗ trợ live reload, nhưng cần thêm cấu hình để hoạt động với PHP.
- **PHP Intelephense** (khuyến nghị): Cung cấp tính năng hỗ trợ code PHP như gợi ý code, kiểm tra lỗi cú pháp.

### Bước 7: Chạy dự án
1. Sao chép thư mục dự án vào thư mục htdocs của XAMPP (thường là C:\xampp\htdocs trên Windows).
2. Mở trình duyệt và truy cập:
- `http://localhost:3000/BanVeXemPhim/index.php` dành cho **User**
- `http://localhost:3000/BanVeXemPhim/admin/sign-up.php` dành cho **Admin**
### Bước 8: Đăng nhập
1. Tài khoản admin:
- Username: Admin
- Password: 123123123
3. Tài khoản người dùng:
- Username: trongquy
- Password: Bigbang2003.
# 📧 Liên hệ
Nếu bạn có bất kỳ câu hỏi hoặc cần hỗ trợ, hãy liên hệ với chúng tôi:
- Email: **vyquy633@gmail.com**
# Hình ảnh Website
