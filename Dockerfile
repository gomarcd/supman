FROM php:8.2.2-fpm-bullseye

RUN rm /bin/sh && ln -s /bin/bash /bin/sh

RUN apt-get update && apt-get install -y \
    git curl wget zip unzip lsb-release \
    && docker-php-ext-install pdo_mysql

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN groupadd -r app && useradd -r -u 1000 -g app app && mkdir /home/app && chown -R 1000:1000 /home/app

RUN mkdir /usr/local/nvm && chown -R app:app /usr/local/nvm
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 19.6.0
ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

#USER app
RUN curl --silent -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

RUN chown -R app:app /var/www
RUN chmod -R 755 /var/www
WORKDIR /var/www

ENV COMPOSER_HOME=~/tmp/composer 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

EXPOSE 9000