ARG PHP_IMAGE=minipay_php
ARG PHP_IMAGE_TAG=latest

FROM ${PHP_IMAGE}:${PHP_IMAGE_TAG} as assets

RUN mkdir -p public

FROM nginx:1.17.10-alpine

COPY docker/nginx/default.conf.template /etc/nginx/conf.d/default.conf.template

COPY docker/nginx/docker-entrypoint.prod.sh /docker-entrypoint.sh

COPY --from=assets /app/public /var/www/app/public

RUN chmod +x /docker-entrypoint.sh

ARG API_HOST
ARG API_PORT

ENV API_HOST $API_HOST
ENV API_PORT $API_PORT

ENTRYPOINT ["sh", "/docker-entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]
