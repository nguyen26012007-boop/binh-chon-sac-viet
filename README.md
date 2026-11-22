# Sắc Việt — Community Voting

Website bình chọn cộng đồng bằng PHP, với trang quản trị nội dung, hồ sơ thí sinh và mã tham gia riêng của chương trình.

## Tính năng

- Quản lý hồ sơ, ảnh, chiều cao, cân nặng, tuổi và ghi chú thí sinh.
- Chỉnh nội dung các bảng đăng ký và nhập lại mã trong admin.
- Đăng ký họ tên, số điện thoại; mỗi mã tham gia hợp lệ được tính một lượt bình chọn.
- Mã do admin tạo, gắn với số điện thoại, dùng một lần và hết hạn sau 24 giờ.
- Kết quả, tổng lượt và bảng xếp hạng được cập nhật sau khi bình chọn thành công.

## Triển khai Railway

Dockerfile nằm ở thư mục gốc. Cấu hình service dùng thư mục gốc của repository.

1. Gắn volume tại `/var/www/data`.
2. Thiết lập secret `ADMIN_PASSWORD_HASH` bằng kết quả PHP `password_hash()` của mật khẩu admin mới.
3. Deploy và tạo public domain HTTPS. Ứng dụng dùng `PORT` do Railway cung cấp.

```sh
docker build -t ielts-zalo .
```

Xem [hướng dẫn Docker](deployment/DOCKER.txt). Nội dung admin, ảnh tải lên, thông tin đăng ký và kết quả được lưu trên volume. Không đưa dữ liệu cá nhân hoặc secret vào Git.

## Chạy PHP trực tiếp

Yêu cầu PHP 8.3 và extension `mbstring`.

Sao chép `admin_login.example.php` thành `admin_login.php`, thiết lập biến môi trường `ADMIN_PASSWORD_HASH`, rồi mở `index.php` qua máy chủ PHP/Apache. File đăng nhập chứa cấu hình riêng của máy được Git bỏ qua.

```sh
php -S 127.0.0.1:8080
```

Mặc định bản chạy trực tiếp lưu dữ liệu bình chọn ngoài thư mục web, tại `ielts-zalo-data` cách thư mục dự án hai cấp. Đảm bảo tài khoản chạy PHP có quyền ghi tại đó và tại thư mục dự án để lưu nội dung admin.

Các file cấu hình cũ, mật khẩu local, log, nội dung admin đã lưu, dữ liệu người dùng và gói build được loại khỏi repository. Mã nguồn Docker tạo file đăng nhập dùng secret môi trường khi build.
