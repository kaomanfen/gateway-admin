FROM php:7.2-fpm-alpine

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        curl-dev \
        imagemagick-dev \
        libtool \
        libxml2-dev \
        postgresql-dev \
        sqlite-dev \
    && apk add --no-cache \
        openssh-client \
        curl \
        git \
        imagemagick \
        mysql-client \
        postgresql-libs \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install \
        curl \
        iconv \
        mbstring \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        pcntl \
        tokenizer \
        xml \
        zip \
    && apk del -f .build-deps

RUN wget -O /usr/bin/composer https://dl.laravel-china.org/composer.phar 

RUN chmod a+x /usr/bin/composer

# 修改 composer 为国内镜像
RUN composer config -g repo.packagist composer https://packagist.laravel-china.org

WORKDIR /srv/app/admin-api

ADD . .

RUN composer install && php artisan key:generate && php artisan jwt:secret

RUN chmod -Rf 777 ./storage

RUN rm -Rf /root/.ssh && rm -Rf ./ssh