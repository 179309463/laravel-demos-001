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

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 配置变量
APP_NAME="Laravel 链接聚合应用"
APP_DIR="/var/www/laravel-links"
BACKUP_DIR="/var/backups/laravel-links"
GIT_REPO="https://github.com/your-username/laravel-links.git"
BRANCH="main"
PHP_VERSION="8.2"
WEB_USER="www-data"

# 函数定义
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查是否以 root 用户运行
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要以 root 用户运行"
        exit 1
    fi
}

# 检查必要的命令
check_dependencies() {
    log_info "检查系统依赖..."
    
    local deps=("git" "php" "composer" "npm" "mysql")
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            log_error "缺少依赖: $dep"
            log_info "请安装 $dep 后重试"
            exit 1
        fi
    done
    
    # 检查网络连接
    log_info "检查网络连接..."
    if ! curl -s --connect-timeout 10 https://packagist.org > /dev/null; then
        log_error "无法连接到 Packagist，请检查网络连接"
        exit 1
    fi
    
    if ! curl -s --connect-timeout 10 https://registry.npmjs.org > /dev/null; then
        log_error "无法连接到 npm registry，请检查网络连接"
        exit 1
    fi
    
    log_success "所有依赖检查通过"
}

# 创建备份
create_backup() {
    log_info "创建应用备份..."
    
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_path="$BACKUP_DIR/backup_$timestamp"
    
    mkdir -p "$BACKUP_DIR"
    
    if [ -d "$APP_DIR" ]; then
        # 备份应用文件
        tar -czf "$backup_path.tar.gz" -C "$(dirname $APP_DIR)" "$(basename $APP_DIR)"
        
        # 备份数据库
        if [ -f "$APP_DIR/.env" ]; then
            source "$APP_DIR/.env"
            if [ ! -z "$DB_DATABASE" ]; then
                mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$backup_path.sql"
                log_success "数据库备份完成: $backup_path.sql"
            fi
        fi
        
        log_success "应用备份完成: $backup_path.tar.gz"
    else
        log_warning "应用目录不存在，跳过备份"
    fi
}

# 部署应用
deploy_application() {
    log_info "开始部署应用..."
    
    # 克隆或更新代码
    if [ ! -d "$APP_DIR" ]; then
        log_info "克隆代码仓库..."
        git clone -b "$BRANCH" "$GIT_REPO" "$APP_DIR"
    else
        log_info "更新代码仓库..."
        cd "$APP_DIR"
        git fetch origin
        git reset --hard "origin/$BRANCH"
    fi
    
    cd "$APP_DIR"
    
    # 复制环境配置文件
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            log_info "复制生产环境配置文件..."
            cp .env.production .env
        else
            log_error "未找到环境配置文件"
            exit 1
        fi
    fi
    
    # 安装 Composer 依赖
    log_info "安装 Composer 依赖..."
    if ! composer install --no-dev --optimize-autoloader --no-interaction --timeout=300; then
        log_error "Composer 依赖安装失败"
        log_info "尝试清理缓存后重试..."
        composer clear-cache
        if ! composer install --no-dev --optimize-autoloader --no-interaction --timeout=300; then
            log_error "Composer 依赖安装最终失败"
            exit 1
        fi
    fi
    
    # 生成应用密钥
    if ! grep -q "APP_KEY=base64:" .env || grep -q "APP_KEY=base64:YOUR_PRODUCTION_APP_KEY_HERE" .env; then
        log_info "生成应用密钥..."
        php artisan key:generate --force
    fi
    
    # 安装前端依赖并构建
    log_info "安装前端依赖..."
    if ! npm ci --only=production --timeout=300000; then
        log_error "npm 依赖安装失败"
        log_info "尝试清理缓存后重试..."
        npm cache clean --force
        if ! npm ci --only=production --timeout=300000; then
            log_error "npm 依赖安装最终失败"
            exit 1
        fi
    fi
    
    log_info "构建前端资源..."
    if ! npm run build; then
        log_error "前端资源构建失败"
        exit 1
    fi
    
    # 数据库迁移
    log_info "运行数据库迁移..."
    php artisan migrate --force
    
    # Laravel 优化
    log_info "执行 Laravel 优化..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # 创建存储链接
    php artisan storage:link
    
    # 设置文件权限
    log_info "设置文件权限..."
    chown -R "$WEB_USER:$WEB_USER" "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage"
    chmod -R 775 "$APP_DIR/bootstrap/cache"
    chmod 600 "$APP_DIR/.env"
    
    log_success "应用部署完成"
}

# 重启服务
restart_services() {
    log_info "重启相关服务..."
    
    # 重启 PHP-FPM
    if systemctl is-active --quiet "php$PHP_VERSION-fpm"; then
        systemctl restart "php$PHP_VERSION-fpm"
        log_success "PHP-FPM 重启完成"
    fi
    
    # 重启 Web 服务器
    if systemctl is-active --quiet nginx; then
        systemctl reload nginx
        log_success "Nginx 重新加载完成"
    elif systemctl is-active --quiet apache2; then
        systemctl reload apache2
        log_success "Apache 重新加载完成"
    fi
    
    # 重启队列工作进程
    if systemctl is-active --quiet laravel-worker; then
        systemctl restart laravel-worker
        log_success "Laravel 队列工作进程重启完成"
    fi
}

# 验证部署
verify_deployment() {
    log_info "验证部署结果..."
    
    cd "$APP_DIR"
    
    # 检查文件权限
    if [ ! -w "storage" ] || [ ! -w "bootstrap/cache" ]; then
        log_error "存储目录权限不正确"
        return 1
    fi
    
    # 检查缓存文件
    if [ ! -f "bootstrap/cache/config.php" ]; then
        log_error "配置缓存文件不存在"
        return 1
    fi
    
    # 检查前端资源
    if [ ! -d "public/build" ]; then
        log_error "前端构建资源不存在"
        return 1
    fi
    
    # 测试应用响应
    local app_url=$(grep "APP_URL=" .env | cut -d '=' -f2)
    if [ ! -z "$app_url" ]; then
        if curl -s -o /dev/null -w "%{http_code}" "$app_url" | grep -q "200\|301\|302"; then
            log_success "应用响应正常"
        else
            log_warning "应用响应异常，请检查配置"
        fi
    fi
    
    log_success "部署验证完成"
}

# 清理旧备份
cleanup_backups() {
    log_info "清理旧备份文件..."
    
    if [ -d "$BACKUP_DIR" ]; then
        # 保留最近 7 天的备份
        find "$BACKUP_DIR" -name "backup_*.tar.gz" -mtime +7 -delete
        find "$BACKUP_DIR" -name "backup_*.sql" -mtime +7 -delete
        log_success "旧备份清理完成"
    fi
}

# 发送通知
send_notification() {
    local status=$1
    local message=$2
    
    # 这里可以添加邮件、Slack、钉钉等通知逻辑
    log_info "部署通知: $status - $message"
    
    # 示例：发送邮件通