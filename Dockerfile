# Sử dụng PHP 8.2 với Apache
FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP (PDO, MySQL, mbstring)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào thư mục web của Apache
COPY . /var/www/html/

# Tạo các thư mục cần thiết (data, uploads) và cấp quyền ghi (777)
# Bắt buộc phải tạo thư mục trước khi chmod để tránh lỗi "No such file or directory"
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

# Mở cổng 80 (Railway sẽ tự động map cổng này ra ngoài)
EXPOSE 80

# Khởi động Apache ở chế độ foreground để container không bị tắt
CMD ["apache2-foreground"]