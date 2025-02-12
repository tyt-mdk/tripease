#!/bin/sh

# 環境変数の設定
export APP_ENV=production
export APP_DEBUG=true

# ストレージディレクトリの権限設定
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# ストレージのシンボリックリンクを作成
php artisan storage:link

# アプリケーションのキャッシュを最適化
php artisan optimize

# エラーログの権限設定
touch /var/log/php-fpm.log
chmod 666 /var/log/php-fpm.log

# PHP-FPMの起動
php-fpm --nodaemonize --force-stderr &

# Nginxの設定テスト
nginx -t

# Nginxを起動
nginx -g 'daemon off;'