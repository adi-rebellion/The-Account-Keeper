#!/bin/bash

echo "USER >>>"
echo `whoami`

if [ ! -f "vendor/autoload.php" ]; then
#    composer install --no-progress --no-interaction
    composer install --optimize-autoloader --no-dev --no-progress --no-interaction
fi

# if [ ! -f ".env" ]; then
#     echo "Creating env file for env $APP_ENV"
#     cp .env.prod.example .env
# else
#     echo "env file exists."
# fi
php artisan createenv

sudo chown -R www-data:www-data /var/www/
sudo chmod -R 755 /var/www/storage
sudo chmod -R 755 /var/www/bootstrap

php artisan key:generate

## dont cache if doing debugging or if env is not prod mode
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

composer update

php artisan db:create

php artisan migrate
php artisan migrate --seed;
php artisan db:seed

php artisan createpassport
# composer dump-autoload --optimize
apt-get clean
apt-get autoclean
apt-get autoremove

# sudo -u www-data php-fpm -D
# sudo php-fpm -D

php-fpm -D
sudo nginx -g "daemon off;"

