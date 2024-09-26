FROM php:8.3-fpm
RUN apt-get update && apt-get install -y \
      unzip \
      libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    docker-php-ext-enable pdo pdo_pgsql

# install composer
COPY --from=composer:2.6.5 /usr/bin/composer /usr/local/bin/composer

COPY . /var/www/app
WORKDIR /var/www/app

RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

# Set the default command to run php-fpm
CMD ["php-fpm"]
