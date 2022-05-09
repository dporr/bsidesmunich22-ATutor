FROM php:5.4-apache
COPY ./ /var/www/html/
RUN docker-php-ext-install mysql\
 && docker-php-ext-install mbstring\
#used later during installation nad yes... they requiere 777 and a+rwx :( 
RUN mkdir -p /var/www/html/content\
 && chmod 2777 content\
 && touch /var/www/html/include/config.inc.php\
 && chmod a+rw /var/www/html/include/config.inc.php
 COPY php.ini "$PHP_INI_DIR/php.ini"