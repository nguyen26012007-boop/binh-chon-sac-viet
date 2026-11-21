FROM php:8.3-apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring \
    && rm -rf /var/lib/apt/lists/*
ENV VOTING_DATA_DIR=/var/www/data PORT=8080
COPY web/ /var/www/html/
COPY seed/ /opt/pageant-seed/
COPY apache-security.conf /etc/apache2/conf-enabled/pageant-security.conf
COPY php-production.ini /usr/local/etc/php/conf.d/pageant-production.ini
COPY start.sh /usr/local/bin/pageant-start
RUN sed -i 's/\r$//' /usr/local/bin/pageant-start \
    && chmod +x /usr/local/bin/pageant-start
EXPOSE 8080
CMD ["/usr/local/bin/pageant-start"]
