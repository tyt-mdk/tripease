#!/bin/sh

# 環境変数の設定
export APP_ENV=production
export APP_DEBUG=true

# ストレージディレクトリの権限設定
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# マイグレーションを実行
php artisan migrate --force

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# ストレージのシンボリックリンクを作成
php artisan storage:link

# アプリケーションのキャッシュを最適化
php artisan optimize

# PHP-FPMとNginxを起動（エラーログを標準出力に）
php-fpm --nodaemonize --force-stderr &
nginx -g 'daemon off;' 2>&1