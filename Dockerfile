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

# パーミッションを設定
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 storage bootstrap/cache

# Nginxをインストールして設定
RUN apt-get install -y nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
RUN rm -f /etc/nginx/sites-enabled/default

# PHP-FPMの設定
RUN sed -i 's/listen = \/run\/php\/php8.1-fpm.sock/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf

# ログディレクトリの作成とパーミッション設定
RUN mkdir -p /var/log/nginx /var/run/nginx
RUN chown -R www-data:www-data /var/log/nginx /var/run/nginx
RUN chmod -R 755 /var/log/nginx /var/run/nginx

# ポート設定
EXPOSE 80

# 起動コマンドを設定
CMD ["sh", "-c", "service nginx start && php-fpm"]