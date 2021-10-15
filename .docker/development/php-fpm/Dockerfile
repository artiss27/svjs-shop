FROM php:8-fpm

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql

RUN apt-get update \
    && apt-get install -y \
        apt-utils \
        man \
        curl \
        git \
        bash \
        vim \
        zip unzip \
        acl \
        iproute2 \
        dnsutils \
        fonts-freefont-ttf \
        fontconfig \
        dbus \
        openssh-client \
        sendmail \
        icu-devtools \
        libicu-dev libcurl4-openssl-dev libjpeg-dev libldap2-dev \
        libldb-dev libpng-dev libxslt-dev libxml2-dev libssl-dev \
        libzip-dev libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev \
        libwebp-dev libmagickwand-dev \
        libmcrypt4 \
        zlib1g-dev \
        libonig-dev \
        graphviz \
        pkg-config \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install \
        intl \
        ctype \
        ldap \
        pdo_mysql \
        xsl \
        xml \
        iconv \
        mbstring \
        bcmath \
        curl \
        fileinfo \
        gettext \
        xmlwriter \
        simplexml \
        opcache \
        zip \
        pcntl \
        exif \
    && docker-php-source delete

RUN docker-php-ext-enable \
        opcache \
        zip \
        pdo_pgsql

#develop layer
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN pecl install ast
RUN docker-php-ext-enable ast

COPY php.ini /usr/local/etc/php/

WORKDIR /app