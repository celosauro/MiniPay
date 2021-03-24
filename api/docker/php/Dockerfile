FROM php:7.4-fpm-alpine

ENV PATH="/app/bin:/app/vendor/bin:${PATH}"

ENV COMPOSER_ALLOW_SUPERUSER="1"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN docker-php-ext-install pdo_mysql \
    && install-php-extensions intl mysqli opcache \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.9.5 \
    && docker-php-ext-enable xdebug \
    && apk add --no-cache --virtual pbj-mysql \
        mysql-client \
        mariadb-connector-c

WORKDIR /app

EXPOSE 9000

COPY docker/php/php.dev.ini /usr/local/etc/php/php.ini

ARG XDEBUG_REMOTE_HOST="172.17.0.1"
RUN sed -i "s|xdebug.remote_host = localhost|xdebug.remote_host = $XDEBUG_REMOTE_HOST|g" /usr/local/etc/php/php.ini

RUN mkdir /app/var/ \
    && mkdir /app/var/cache \
    && mkdir /app/var/log \
    && chown -R www-data:www-data /app/var

CMD php-fpm
