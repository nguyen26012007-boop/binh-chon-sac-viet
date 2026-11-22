FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copy code vào thư mục web
COPY . /var/www/html/

# Tạo thư mục và phân quyền
RUN mkdir -p /var/www/html/uploads /var/www/html/data && chmod -R 777 /var/www/html/uploads /var/www/html/data

# Sửa lỗi MPM (nếu có)
RUN a2dismod mpm_event && a2enmod mpm_prefork

EXPOSE 80

CMD ["apache2-foreground"]