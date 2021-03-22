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
    && apk add --no-cache libpng libpng-dev \
    && docker-php-ext-install gd \
    && apk del libpng-dev \
    && docker-php-ext-install pdo_mysql \
    && apk add --no-cache --virtual pjb-webviewer nodejs yarn \
    && apk add --no-cache --virtual pjb-barcode-extractor openjdk8 \
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
    && pecl install xdebug-2.9.5 \
    && docker-php-ext-enable xdebug \
    && apk add --no-cache --virtual pbj-mysql \
        mysql-client \
        mariadb-connector-c

COPY docker/php-fpm/php.dev.ini /usr/local/etc/php/php.ini

ARG XDEBUG_REMOTE_HOST="172.17.0.1"
RUN sed -i "s|xdebug.remote_host = localhost|xdebug.remote_host = $XDEBUG_REMOTE_HOST|g" /usr/local/etc/php/php.ini

FROM base AS ci

ARG DB_USER
ARG DB_PASSWORD

ENV DB_USER $DB_USER
ENV DB_PASSWORD $DB_PASSWORD

COPY ./ /app

RUN apk add --no-cache $PHPIZE_DEPS && \
    pecl install xdebug-2.9.5 && \
    docker-php-ext-enable xdebug && \
    composer validate --strict && \
    composer self-update --1 && \
        composer install \
    --optimize-autoloader \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --no-suggest

FROM base AS prod

ARG AWS_ACCOUNT_ID
ENV AWS_ACCOUNT_ID $AWS_ACCOUNT_ID

ARG AWS_REGION
ENV AWS_REGION $AWS_REGION

ARG AWS_SQS_NAME
ENV AWS_SQS_NAME $AWS_SQS_NAME

ARG INFRA_ENVIRONMENT=prod
ENV INFRA_ENVIRONMENT $INFRA_ENVIRONMENT

ARG SYMFONY_DECRYPTION_SECRET
ENV SYMFONY_DECRYPTION_SECRET $SYMFONY_DECRYPTION_SECRET

ARG DEPLOY_CMD=php-fpm
ENV DEPLOY_CMD $DEPLOY_CMD

COPY docker/php-fpm/php.prod.ini /usr/local/etc/php/php.ini
COPY docker/php-fpm/docker-entrypoint.prod.sh /usr/local/bin/docker-entrypoint.sh
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN install-php-extensions intl mysqli opcache

COPY ./composer.json  /app/composer.json
COPY ./composer.lock  /app/composer.lock

RUN composer self-update --1 && \
    composer install \
    --no-dev --optimize-autoloader \
    --no-ansi --no-interaction --no-progress --no-scripts --no-suggest

COPY ./bin       /app/bin
COPY ./config    /app/config
COPY ./public    /app/public
COPY ./src       /app/src
COPY ./templates /app/templates
COPY ./.env.prod /app/.env
COPY ./.env.staging /app/.env.staging

RUN mkdir /app/var/ \
    && mkdir /app/var/cache \
    && mkdir /app/var/log \
    && chown -R www-data:www-data /app/var \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["sh", "/usr/local/bin/docker-entrypoint.sh"]
CMD ${DEPLOY_CMD}
