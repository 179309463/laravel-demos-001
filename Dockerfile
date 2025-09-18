# Laravel 链接聚合应用 - 生产环境 Dockerfile
FROM php:8.2-fpm-alpine

# 设置工作目录
WORKDIR /var/www/html

# 安装系统依赖
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    mysql-client \
    nginx \
    supervisor

# 安装 PHP 扩展
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    zip

# 安装 Redis 扩展
RUN pecl install redis && docker-php-ext-enable redis

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 创建应用用户
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# 复制应用文件
COPY --chown=www:www . /var/www/html

# 安装 PHP 依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 安装前端依赖并构建
RUN npm ci --only=production && \
    npm run build && \
    npm cache clean --force

# 设置权限
RUN chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# 复制配置文件
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/php.ini

# 创建必要的目录
RUN mkdir -p /var/log/supervisor && \
    mkdir -p /var/log/nginx && \
    mkdir -p /var/run

# 暴露端口
EXPOSE 80

# 启动脚本
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# 切换到应用用户
USER www

# 启动命令
CMD ["/start.sh"]