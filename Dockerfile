FROM composer:latest as composer
ADD ./src/ /var/www/
WORKDIR /var/www
RUN composer install

FROM node:latest AS node
COPY --from=composer /var/www /var/www/

WORKDIR /var/www
RUN npm install && npm run build

FROM php:8.2.4-fpm-bullseye
RUN docker-php-ext-install pdo_mysql

RUN apt-get update && apt-get install -y nginx cron
COPY nginx/conf.d/app.conf /etc/nginx/conf.d/app.conf
COPY nginx/nginx.conf /etc/nginx/nginx.conf

COPY --from=node /var/www /var/www

RUN apt-get update && apt-get install -y redis-server supervisor
RUN pecl install redis && docker-php-ext-enable redis

COPY ./src/cron /etc/cron.d/api
RUN chmod 0644 /etc/cron.d/api
RUN chown root:root /etc/cron.d/api
RUN crontab /etc/cron.d/api

COPY ./src/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

WORKDIR /var/www

CMD ["/usr/bin/supervisord", "--nodaemon", "-c", "/etc/supervisor/conf.d/supervisord.conf"]