FROM php:5.4-apache
COPY ./ /var/www/html/
RUN docker-php-ext-install mysql && docker-php-ext-install mbstring