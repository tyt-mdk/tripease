# PHPのベースイメージを使用
FROM php:8.1-fpm

# 作業ディレクトリを設定
WORKDIR /var/www/html

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev \
    postgresql \
    postgresql-contrib

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN docker-php-ext-install pgsql pdo_pgsql

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Composerの設定
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# アプリケーションファイルをコピー
COPY . .

# Composerの依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# laravel/uiパッケージをインストール
RUN composer require laravel/ui

# npmパッケージをインストール
RUN npm install
RUN npm run build

# 環境設定
RUN cp .env.example .env
RUN php artisan key:generate --force

# Nginxをインストールして設定
RUN apt-get install -y nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# PHP-FPMの設定
RUN sed -i 's/listen = \/run\/php\/php8.1-fpm.sock/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;php_flag[display_errors] = off/php_flag[display_errors] = on/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;php_admin_value[error_log] = .*/php_admin_value[error_log] = \/var\/log\/php-fpm.log/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;php_admin_flag[log_errors] = .*/php_admin_flag[log_errors] = on/g' /usr/local/etc/php-fpm.d/www.conf

# ログディレクトリの作成
RUN touch /var/log/php-fpm.log
RUN chown www-data:www-data /var/log/php-fpm.log

# キャッシュをクリア
RUN php artisan config:clear
RUN php artisan cache:clear
RUN php artisan view:clear
RUN php artisan route:clear

# ストレージディレクトリの準備
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
RUN mkdir -p /var/www/html/storage/logs

# パーミッションの設定
RUN chown -R www-data:www-data /var/www/html
RUN find /var/www/html/storage -type f -exec chmod 644 {} \;
RUN find /var/www/html/storage -type d -exec chmod 755 {} \;
RUN chmod -R 775 /var/www/html/bootstrap/cache

# ポート設定
EXPOSE 80

# 起動スクリプトを作成
RUN echo '#!/bin/sh\nnginx\nphp-fpm\n' > /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# 起動コマンド
CMD ["/usr/local/bin/start.sh"]