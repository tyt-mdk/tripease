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
    npm

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリケーションファイルをコピー
COPY . .

# 所有者を変更
RUN chown -R www-data:www-data /var/www/html

# www-dataユーザーとしてcomposerを実行
USER www-data

# 依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

# rootユーザーに戻す
USER root

# 環境設定
RUN cp .env.example .env
RUN php artisan key:generate

# パーミッションを設定
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage

# Nginxをインストールして設定
RUN apt-get install -y nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# ポート設定
EXPOSE 80

# 起動スクリプト
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# コンテナ起動時のコマンド
CMD ["/usr/local/bin/start.sh"]