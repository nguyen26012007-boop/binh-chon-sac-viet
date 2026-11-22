# PHP built-in server: không dùng Apache nên không còn lỗi "More than one MPM loaded"
FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data /var/www/html/uploads

ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT} -t /var/www/html"]
