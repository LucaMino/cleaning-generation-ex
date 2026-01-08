FROM php:8.2-fpm-alpine

# set workdir
WORKDIR /var/www/html

# install dependencies
RUN apk add --no-cache \
    oniguruma-dev \
    fontconfig \
    ttf-dejavu

# install php extensions (mbstring needs oniguruma-dev)
RUN docker-php-ext-install mbstring

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN composer dump-autoload --optimize

# set permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 9000

# start php-fpm server
CMD ["php-fpm"]