FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
git unzip zip libzip-dev \
&& docker-php-ext-install zip pdo pdo_mysql

RUN a2enmod rewrite

COPY . /var/www/html

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data \
storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
/etc/apache2/sites-available/*.conf

EXPOSE 80