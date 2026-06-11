FROM php:8.1-apache

# Install DB-related PHP extensions once at build time for faster, reliable container startup.
RUN docker-php-ext-install mysqli pdo pdo_mysql
