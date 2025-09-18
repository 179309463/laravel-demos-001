#!/bin/sh

# Laravel 链接聚合应用 - Docker 启动脚本

set -e

echo "启动 Laravel 链接聚合应用..."

# 等待数据库连接
echo "等待数据库连接..."
while ! nc -z mysql 3306; do
    echo "等待 MySQL 启动..."
    sleep 2
done
echo "MySQL 已启动"

# 等待 Redis 连接
echo "等待 Redis 连接..."
while ! nc -z redis 6379; do
    echo "等待 Redis 启动..."
    sleep 2
done
echo "Redis 已启动"

# 切换到应用目录
cd /var/www/html

# 生成应用密钥（如果不存在）
if [ ! -f ".env" ]; then
    echo "复制环境配置文件..."
    cp .env.production .env
fi

# 检查并生成应用密钥
if ! grep -q "APP_KEY=base64:" .env || grep -q "APP_KEY=base64:YOUR_PRODUCTION_APP_KEY_HERE" .env; then
    echo "生成应用密钥..."
    php artisan key:generate --force
fi

# 运行数据库迁移
echo "运行数据库迁移..."
php artisan migrate --force

# 创建存储链接
echo "创建存储链接..."
php artisan storage:link

# 清理并缓存配置
echo "优化应用性能..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 设置正确的文件权限
echo "设置文件权限..."
chmod -R 775 storage bootstrap/cache

# 启动 Supervisor（管理 PHP-FPM 和其他服务）
echo "启动服务..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf