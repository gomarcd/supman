FROM php:8.2.2-fpm-bullseye
RUN apt-get update && apt-get install -y \
    git curl wget zip unzip lsb-release \
    && docker-php-ext-install pdo_mysql

ENV NODE_VERSION 19.6.0
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install node && nvm use node
ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

ENV COMPOSER_HOME=~/tmp/composer 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

EXPOSE 9000
