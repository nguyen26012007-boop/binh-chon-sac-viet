# Sử dụng PHP 8.2 với Apache
FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP (PDO, MySQL, mbstring)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

<<<<<<< fix-apache-mpm
# Chỉ giữ một MPM (prefork, cần cho mod_php): xoá hẳn các file load của MPM khác
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
=======
# Apache chỉ được phép load đúng một MPM (Apache must load exactly one MPM).
# Tắt mpm_event/mpm_worker và bật mpm_prefork (tương thích với mod_php).
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork
>>>>>>> main

# Cấu hình thư mục làm việc
WORKDIR /var/www/html
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

<<<<<<< fix-apache-mpm
# Railway cấp PORT; Apache lắng nghe cổng đó
ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
=======
# Mở cổng 80 (Railway sẽ tự động map cổng này ra ngoài)
EXPOSE 80

# Khởi động Apache ở chế độ foreground để container không bị tắt
>>>>>>> main
