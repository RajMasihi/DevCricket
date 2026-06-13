# FROM php:8.3-fpm

# RUN apt-get update && apt-get install -y \
#     git \
#     curl \
#     unzip \
#     zip \
#     libzip-dev \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev

# RUN docker-php-ext-install \
#     pdo_mysql \
#     mbstring \
#     zip \
#     exif \
#     pcntl \
#     bcmath

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# WORKDIR /var/www

# COPY composer.json composer.lock ./

# # RUN composer install \
# #     --prefer-source \
# #         --no-dev \
# #         --optimize-autoloader \
# #     --no-interaction

# COPY . .

# RUN chown -R www-data:www-data \
#     storage bootstrap/cache

# EXPOSE 9000

# CMD ["php-fpm"]



FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN chown -R www-data:www-data \
    storage bootstrap/cache

RUN chmod -R 775 \
    storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]