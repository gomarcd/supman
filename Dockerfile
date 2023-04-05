FROM composer:latest as composer
WORKDIR /var/www

ADD ./src/ /var/www/
RUN test -d storage/framework/views || mkdir -p storage/framework/views \
    && test -d storage/framework/sessions || mkdir -p storage/framework/sessions \
    && test -d storage/framework/cache || mkdir -p storage/framework/cache \
    && test -d storage/logs || mkdir -p storage/logs && touch storage/logs/laravel.log \
    composer install

FROM node:latest AS node
COPY --from=composer /var/www /var/www/
WORKDIR /var/www

RUN npm install && npm run build && rm -rf node_modules

FROM php:8.2.4-fpm-alpine
COPY --from=node /var/www /var/www

RUN docker-php-ext-install pdo_mysql && apk add --no-cache autoconf build-base nginx dcron redis supervisor \
    && pecl install redis && docker-php-ext-enable redis

# RUN docker-php-ext-install pdo_mysql && apt-get update \ 
#    && apt-get update && apt-get install -y nginx cron redis-server supervisor \
#    && pecl install redis && docker-php-ext-enable redis

COPY ./src/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./src/cron /etc/crontabs/root
#COPY ./src/cron /etc/cron.d/api
COPY nginx/conf.d/app.conf /etc/nginx/conf.d/app.conf
COPY nginx/nginx.conf /etc/nginx/nginx.conf

RUN chmod 0644 /etc/crontabs/root \
    && chown -R www-data:www-data /var/www/public /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/bootstrap/cache

# RUN chmod 0644 /etc/cron.d/api && chown root:root /etc/cron.d/api && crontab /etc/cron.d/api \
#    && chown -R www-data:www-data /var/www/public /var/www/storage /var/www/bootstrap/cache \
#    && chmod -R 755 /var/www/bootstrap/cache \
#    && apt-get update && apt-get upgrade -y

WORKDIR /var/www
RUN rm -rf /var/www/html && apk del build-base && apk add --update-cache && rm -rf /var/cache/apk/*

CMD ["/usr/bin/supervisord", "--nodaemon", "-c", "/etc/supervisor/conf.d/supervisord.conf"]