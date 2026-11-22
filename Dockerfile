# Sử dụng PHP 8.2 với Apache
FROM php:8.2-apache

# Cài đặt các extension cần thiết cho PHP (PDO, MySQL, mbstring)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

# Chỉ giữ một MPM (prefork, cần cho mod_php): xoá hẳn các file load của MPM khác
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Cấu hình thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào thư mục web của Apache
COPY . /var/www/html/

# Tạo các thư mục cần thiết (data, uploads) và cấp quyền ghi (777)
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

# Railway cấp PORT; Apache lắng nghe cổng đó
ENV PORT=8080
EXPOSE 8080
CMD ["sh", "/var/www/html/docker-start.sh"]
