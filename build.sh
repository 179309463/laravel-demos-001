#!/bin/bash

# 设置错误处理
set -e  # 遇到错误立即退出
set -u  # 使用未定义变量时退出
set -o pipefail  # 管道中任何命令失败都会导致整个管道失败

# 定义颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 错误处理函数
error_exit() {
    echo -e "${RED}错误: $1${NC}" >&2
    exit 1
}

# 成功信息函数
success_msg() {
    echo -e "${GREEN}✓ $1${NC}"
}

# 警告信息函数
warn_msg() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

echo "开始 Vercel 构建..."

# 检查网络连接
echo "检查网络连接..."
if curl -s --max-time 10 https://packagist.org > /dev/null; then
    success_msg "Packagist连接正常"
else
    warn_msg "Packagist连接失败，可能影响Composer依赖安装"
fi

if curl -s --max-time 10 https://registry.npmjs.org > /dev/null; then
    success_msg "npm registry连接正常"
else
    warn_msg "npm registry连接失败，可能影响npm依赖安装"
fi

# 检查 PHP 版本
echo "检查 PHP 版本..."
PHP_VERSION=$(php --version | head -n 1)
echo "PHP 版本: $PHP_VERSION"

# 检查 Node.js 版本
echo "检查 Node.js 版本..."
NODE_VERSION=$(node --version)
NPM_VERSION=$(npm --version)
echo "Node.js 版本: $NODE_VERSION"
echo "npm 版本: $NPM_VERSION"

# 安装 Composer 依赖
echo "安装 Composer 依赖..."
if [ -f "composer.json" ]; then
    if composer install --no-dev --optimize-autoloader --no-interaction; then
        success_msg "Composer 依赖安装成功"
    else
        error_exit "Composer 依赖安装失败"
    fi
else
    warn_msg "composer.json 文件不存在"
fi

# 检查环境文件
if [ ! -f ".env" ]; then
    if [ -f ".env.production" ]; then
        echo "复制生产环境配置..."
        cp .env.production .env
    elif [ -f ".env.example" ]; then
        echo "复制示例环境配置..."
        cp .env.example .env
    else
        error_exit "找不到环境配置文件"
    fi
fi

# 生成应用密钥（如果需要）
if ! grep -q "APP_KEY=base64:" .env || grep -q "APP_KEY=base64:YOUR_PRODUCTION_APP_KEY_HERE" .env; then
    echo "生成应用密钥..."
    php artisan key:generate --force
fi

# 清理和优化
echo "清除应用缓存..."
if php artisan config:clear && php artisan route:clear && php artisan view:clear; then
    success_msg "缓存清除成功"
else
    warn_msg "缓存清除失败，继续构建"
fi

echo "优化应用性能..."
if php artisan config:cache && php artisan route:cache && php artisan view:cache; then
    success_msg "应用优化成功"
else
    warn_msg "应用优化失败，继续构建"
fi

# 安装前端依赖（如果存在）
if [ -f "package.json" ]; then
    echo "安装前端依赖..."
    if npm ci; then
        success_msg "前端依赖安装成功"
    else
        error_exit "前端依赖安装失败"
    fi
    
    echo "构建前端资源..."
    if npm run build:production; then
        success_msg "前端资源构建成功"
    else
        error_exit "前端资源构建失败"
    fi
fi

# 设置权限
echo "设置文件权限..."
chmod -R 755 storage bootstrap/cache || true

success_msg "Vercel 构建完成！"