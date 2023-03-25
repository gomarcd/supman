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

RUN apt-get update && apt-get install -y nginx
COPY nginx/conf.d/app.conf /etc/nginx/conf.d/app.conf
COPY nginx/nginx.conf /etc/nginx/nginx.conf

COPY --from=node /var/www /var/www
RUN rm -r /var/www/html

RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

RUN apt-get update && apt-get install -y redis-server
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www

CMD ["sh", "-c", "redis-server & /usr/sbin/nginx & php-fpm"]