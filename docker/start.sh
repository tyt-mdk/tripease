#!/bin/sh

# マイグレーションを実行
php artisan migrate --force

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# ストレージのシンボリックリンクを作成
php artisan storage:link

# デバッグ用の権限確認
ls -la /var/www/html
ls -la /var/www/html/storage
ls -la /var/www/html/bootstrap/cache

# PHP-FPMとNginxを起動（エラーログを標準出力に）
php-fpm --nodaemonize --force-stderr &
nginx -g 'daemon off;' 2>&1