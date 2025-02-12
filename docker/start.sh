#!/bin/bash

# PHP-FPMを起動
service php8.2-fpm start

# Nginxをフォアグラウンドで起動
nginx -g "daemon off;"