FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP
RUN docker-php-ext-install pdo pdo_mysql

# Chỉ giữ một MPM (prefork, cần cho mod_php): xoá hẳn các file load của MPM khác
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Copy mã nguồn vào thư mục web
COPY . /var/www/html/

# Tạo thư mục và phân quyền
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

# Railway cấp PORT; Apache lắng nghe cổng đó
ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]