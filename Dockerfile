FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Tắt module mpm_event (gây xung đột) và bật mpm_prefork
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork

# Copy mã nguồn vào thư mục web
COPY . /var/www/html/

# Tạo thư mục và phân quyền
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

# Mở cổng 80
EXPOSE 80

# Khởi động Apache
CMD ["apache2-foreground"]