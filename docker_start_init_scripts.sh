[ ! -d "vendor" ] && composer install && composer dump-a

[ ! -f ".env" ] && echo ".env file doesnot exists"

php artisan clear
php artisan cache:clear
php artisan config:clear

php artisan migrate:fresh
php artisan migrate --seed;

php artisan serve --host 0.0.0.0 --port 8000;
