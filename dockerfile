FROM php:7.2-cli-alpine as build_composer

WORKDIR /var/www/html

COPY . .

RUN wget https://dl.laravel-china.org/composer.phar -O /usr/local/bin/composer ; chmod a+x /usr/local/bin/composer

RUN apk add --no-cache git
RUN composer install --no-dev

FROM php:7.2-fpm-alpine


WORKDIR /var/www/html

COPY . .

COPY .env.example .env
COPY --from=build_composer  /var/www/html/vendor ./vendor

# RUN ls -al

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan optimize --force
RUN docker-php-ext-install   pdo_mysql mysqli
ADD build/docker-php-entrypoint /usr/local/bin/
# RUN chmod 777 /usr/local/bin/docker-php-entrypoint
