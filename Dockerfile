FROM php:7.4-fpm

RUN apt-get update
RUN apt-get install --yes --force-yes cron g++ gettext zip unzip git libicu-dev openssl libc-client-dev libkrb5-dev libxml2-dev libfreetype6-dev libgd-dev libmcrypt-dev bzip2 libbz2-dev libtidy-dev libcurl4-openssl-dev libz-dev libmemcached-dev libxslt-dev

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

RUN docker-php-ext-configure gd --with-freetype=/usr --with-jpeg=/usr
RUN docker-php-ext-install gd

RUN docker-php-ext-install xml

# Install Composer
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer