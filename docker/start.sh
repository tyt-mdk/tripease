#!/bin/sh

# PHP-FPMを起動
php-fpm &

# Nginxをフォアグラウンドで起動
nginx -g "daemon off;"