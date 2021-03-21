FROM php:7.4-fpm-alpine AS base

ARG DB_SERVER
ARG DB_PORT
ARG DB_NAME
ARG NEW_RELIC_ENABLED
ARG NEW_RELIC_LICENSE_KEY
ARG NEW_RELIC_APP_NAME

ENV DB_SERVER $DB_SERVER
ENV DB_PORT $DB_PORT
ENV DB_NAME $DB_NAME
ENV NEW_RELIC_ENABLED $NEW_RELIC_ENABLED
ENV NEW_RELIC_LICENSE_KEY $NEW_RELIC_LICENSE_KEY
ENV NEW_RELIC_APP_NAME $NEW_RELIC_APP_NAME

ENV COMPOSER_ALLOW_SUPERUSER="1"
ENV PATH="/app/bin:/app/vendor/bin:${PATH}"

RUN docker-php-ext-install pdo_mysql \
    && apk add --no-cache --virtual pjb-webviewer nodejs yarn \
    && cd ~ \
    && export NEWRELIC_VERSION="$(curl -sS https://download.newrelic.com/php_agent/release/ | sed -n 's/.*>\(.*linux-musl\).tar.gz<.*/\1/p')" \
    && curl -sS "https://download.newrelic.com/php_agent/release/${NEWRELIC_VERSION}.tar.gz" | gzip -dc | tar xf - \
    && cd "${NEWRELIC_VERSION}" \
    && NR_INSTALL_SILENT=true ./newrelic-install install \
    && cd .. \
    && unset NEWRELIC_VERSION \
    && sed -i \
        -e "s/;\?newrelic.enabled =.*/newrelic.enabled = \${NEW_RELIC_ENABLED}/" \
        -e "s/newrelic.license =.*/newrelic.license = \${NEW_RELIC_LICENSE_KEY}/" \
        -e "s/newrelic.appname =.*/newrelic.appname = \${NEW_RELIC_APP_NAME}/" \
        /usr/local/etc/php/conf.d/newrelic.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 9000

FROM base AS dev

ARG DB_USER
ARG DB_PASSWORD

ENV DB_USER $DB_USER
ENV DB_PASSWORD $DB_PASSWORD

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-3.0.0 \
    && docker-php-ext-enable xdebug \
    && apk add --no-cache --virtual pbj-mysql \
        mysql-client \
        mariadb-connector-c \
    && echo "Symfony dependency: PHP intl extension." \
    && apk add --no-cache icu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

COPY docker/php-fpm/php.dev.ini /usr/local/etc/php/php.ini

ARG XDEBUG_REMOTE_HOST="172.17.0.1"
RUN sed -i "s|xdebug.client_host=localhost|xdebug.client_host=$XDEBUG_REMOTE_HOST|g" /usr/local/etc/php/php.ini

CMD php-fpm
