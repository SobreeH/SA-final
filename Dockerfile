FROM php:8.2.12-apache

WORKDIR /var/www/html

# Install dependencies and PHP mysqli extension
RUN apt-get update && apt-get install -y \
        libmariadb-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Make sure Apache can write to web root
RUN chmod -R a+rw /var/www/html

# Enable Apache mod_rewrite (useful for frameworks like Laravel, WordPress, etc.)
RUN a2enmod rewrite
