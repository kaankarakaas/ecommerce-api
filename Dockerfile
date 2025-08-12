FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    netcat-traditional \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan jwt:secret \
    && php artisan config:clear \
    && php artisan clear-compiled \
    && php artisan package:discover --ansi

RUN php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan l5-swagger:generate

CMD ["php-fpm"]
