FROM php:8.2-apache

# Install ekstensi database untuk PHP native
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan rewrite module
RUN a2enmod rewrite