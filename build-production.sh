#!/bin/bash

# Laravel 链接聚合应用 - 生产环境构建脚本
# 此脚本用于构建和优化应用以供生产环境部署

set -e  # 遇到错误时退出

echo "🚀 开始构建 Laravel 链接聚合应用生产版本..."

# 检查必要的命令是否存在
command -v php >/dev/null 2>&1 || { echo "❌ 错误: 需要安装 PHP" >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "❌ 错误: 需要安装 Composer" >&2; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ 错误: 需要安装 Node.js 和 npm" >&2; exit 1; }

# 1. 清理缓存和临时文件
echo "🧹 清理缓存和临时文件..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
rm -rf bootstrap/cache/*.php

# 2. 安装生产环境依赖
echo "📦 安装 Composer 生产环境依赖..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. 安装前端依赖
echo "📦 安装前端依赖..."
npm ci --only=production

# 4. 构建前端资源
echo "🏗️ 构建前端生产资源..."
npm run build

# 5. 生成应用密钥（如果不存在）
if [ ! -f .env ]; then
    echo "⚙️ 复制生产环境配置文件..."
    cp .env.production .env
    echo "🔑 生成应用密钥..."
    php artisan key:generate --force
fi

# 6. Laravel 生产环境优化
echo "⚡ 执行 Laravel 生产环境优化..."

# 缓存配置文件
echo "📋 缓存配置文件..."
php artisan config:cache

# 缓存路由
echo "🛣️ 缓存路由..."
php artisan route:cache

# 缓存视图
echo "👁️ 缓存视图模板..."
php artisan view:cache

# 缓存事件
echo "📅 缓存事件..."
php artisan event:cache

# 优化自动加载器
echo "🔄 优化 Composer 自动加载器..."
composer dump-autoload --optimize --classmap-authoritative

# 7. 数据库迁移（可选，根据需要启用）
# echo "🗄️ 运行数据库迁移..."
# php artisan migrate --force

# 8. 创建符号链接（用于存储）
echo "🔗 创建存储符号链接..."
php artisan storage:link

# 9. 设置文件权限
echo "🔒 设置文件权限..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# 10. 验证构建结果
echo "✅ 验证构建结果..."
if [ -d "public/build" ]; then
    echo "✅ 前端资源构建成功"
else
    echo "❌ 前端资源构建失败"
    exit 1
fi

if [ -f "bootstrap/cache/config.php" ]; then
    echo "✅ Laravel 配置缓存成功"
else
    echo "❌ Laravel 配置缓存失败"
    exit 1
fi

echo "🎉 生产环境构建完成！"
echo "📝 构建摘要:"
echo "   - 前端资源已构建并优化"
echo "   - Laravel 配置、路由、视图已缓存"
echo "   - Composer 自动加载器已优化"
echo "   - 文件权限已设置"
echo "   - 存储符号链接已创建"
echo ""
echo "🚀 应用已准备好部署到生产环境！"
echo "📋 下一步: 上传文件到服务器并配置 Web 服务器"