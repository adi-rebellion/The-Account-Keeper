FROM php:8.1-fpm as php
# Set the env variables

ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=1
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_REVALIDATE_FREQ=0
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=512
ENV PHP_OPCACHE_INTERNED_STRINGS_BUFFER=64
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=16229
## THIS 16229 IS A SPECIAL NUMBER..

# Update package list and install sudo
RUN apt-get update && \
    apt-get install -y sudo

# # Create a non-root user (replace 'www-data' with your desired username)
# RUN addgroup --gid 1000 appgroup && \
#     adduser --uid 1000 --gid 1000 --disabled-password --gecos "" www-data

# Grant sudo privileges to the non-root user
RUN echo 'www-data ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers

RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data && \
    usermod -aG root www-data

##adding www-data to the root group

##Install all dependencies & PHP extensions
RUN apt-get update -y && \
    apt-get install -y findutils iputils-ping libfreetype6-dev libjpeg62-turbo-dev libpng-dev unzip libpq-dev libcurl4-gnutls-dev nginx supervisor libmemcached-dev zlib1g-dev && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-install gd pdo pdo_mysql bcmath curl opcache pcntl

# RUN docker-php-ext-enable opcache
# RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
## https://medium.com/@mdiazr2000/manage-your-laravel-queues-with-redis-and-horizon-9ae72916287c
# One of the most important thing in the dockerfile above is that in order that Horizon runs correctly we need to enable the PCNTL(Process Control Extensions) extension for PHP in order to allow PHP controls the job execution timeout period in Laravel.

RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install \
    pcntl

##added this for memcached support
RUN pecl install memcached && docker-php-ext-enable memcached && pecl install redis && docker-php-ext-enable redis

## SET WORKING DIR TO /var/www
WORKDIR /var/www

### Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data . .
# RUN chmod -R 755 ./

## COPY CONFIG FILES
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./docker/php/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

# Create laravel caching folders.
RUN mkdir -p /var/www/storage/framework && \
    mkdir -p /var/www/storage/framework/cache && \
    mkdir -p /var/www/storage/framework/testing && \
    mkdir -p /var/www/storage/framework/sessions && \
    mkdir -p /var/www/storage/framework/views && \
    mkdir -p /var/www/storage/logs && \
    # Set permissions for directories to 775
    find /var/www/storage -type d -exec chmod 775 {} \; && \
    find /var/www/bootstrap/cache -type d -exec chmod 775 {} \; && \
    # Set permissions for files to 755..changed to 765
    find /var/www/storage -type f -exec chmod 765 {} \; && \
    find /var/www/bootstrap/cache -type f -exec chmod 765 {} \; && \
    ## give the newly created files/directories the group of the parent directory
    find /var/www/storage -type f -exec chmod g+ws {} \; && \
    find /var/www/bootstrap/cache -type f -exec chmod g+w {} \; && \
    sudo chmod -R g+ws /var/www/storage && \
    sudo chmod -R g+ws /var/www/bootstrap/cache

# Fix files ownership.
#RUN chown -R www-data /var/www/storage
#RUN chown -R www-data /var/www/storage/framework
#RUN chown -R www-data /var/www/storage/framework/sessions






# RUN sudo chmod -R 775 /var/www/storage && \
#     sudo chmod -R 775 /var/www/bootstrap/cache


# RUN chmod -R 755 /var/www/storage/logs
# RUN chmod -R 755 /var/www/storage/framework
# RUN chmod -R 755 /var/www/storage/framework/sessions
# RUN chmod -R 755 /var/www/bootstrap

RUN chown -R www-data:www-data /var/www/ && \
    touch /var/www/storage/logs/laravel.log

USER www-data
ENTRYPOINT [ "docker/entrypoint.sh" ]

## remember to run this for composer when going to production
## composer install --prefer-dist --no-dev -o
