FROM php:8-cli

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

RUN apt-get update && apt-get install -y libpq-dev libzip-dev zip unzip \
        libxslt1.1 libxslt1-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql \
        xsl \
        zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

ENV COMPOSER_ALLOW_SUPERUSER 1

COPY php.ini /usr/local/etc/php/

WORKDIR /app