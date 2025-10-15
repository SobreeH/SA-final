FROM php:8.2.12-apache

WORKDIR /var/www/html

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
        libmariadb-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set ownership and permissions for uploads folder only
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads
