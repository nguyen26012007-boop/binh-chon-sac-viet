# Sử dụng PHP 8.2 với Apache
FROM php:8.2-apache

# Cài đặt các extension cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Copy toàn bộ code vào thư mục web
COPY . /var/www/html/

# Cấu hình quyền ghi cho thư mục uploads và data (nếu cần)
RUN chmod -R 777 /var/www/html/uploads /var/www/html/data

# Mở cổng 80 (hoặc 8080 nếu Railway yêu cầu)
EXPOSE 80

# Khởi động Apache
CMD ["apache2-foreground"]