#!/bin/sh

# マイグレーションを実行
php artisan migrate --force

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# PHP-FPMとNginxを起動
php-fpm &
nginx -g 'daemon off;'