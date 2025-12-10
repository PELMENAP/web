FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    librdkafka-dev \
    && docker-php-ext-install sockets

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY composer.json /var/www/html/
RUN composer install --no-interaction

COPY ./www /var/www/html

CMD ["php-fpm"]