#!/bin/bash

# Laravel 链接聚合应用快速部署脚本
# 使用方法: ./quick-deploy.sh [环境] [操作]
# 环境: production, staging, development
# 操作: deploy, update, rollback, backup

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 配置变量
APP_NAME="laravel-links-app"
APP_DIR="/var/www/${APP_NAME}"
BACKUP_DIR="/var/backups/${APP_NAME}"
GIT_REPO="https://github.com/your-username/laravel-links-app.git"
BRANCH="main"
PHP_VERSION="8.1"

# 默认参数
ENVIRONMENT=${1:-production}
ACTION=${2:-deploy}

# 日志函数
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

# 检查系统要求
check_requirements() {
    log_info "检查系统要求..."
    
    # 检查是否为root用户或有sudo权限
    if [[ $EUID -eq 0 ]]; then
        log_warning "不建议使用root用户运行此脚本"
    fi
    
    # 检查必需的命令
    local required_commands=("git" "php" "composer" "npm" "mysql" "nginx")
    for cmd in "${required_commands[@]}"; do
        if ! command -v $cmd &> /dev/null; then
            log_error "缺少必需的命令: $cmd"
            exit 1
        fi
    done
    
    # 检查PHP版本
    local php_version=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    if [[ "$php_version" < "8.1" ]]; then
        log_error "PHP版本过低，需要8.1或更高版本，当前版本: $php_version"
        exit 1
    fi
    
    log_success "系统要求检查通过"
}

# 创建必要的目录
setup_directories() {
    log_info "创建必要的目录..."
    
    sudo mkdir -p $APP_DIR
    sudo mkdir -p $BACKUP_DIR
    sudo mkdir -p /var/log/$APP_NAME
    
    # 设置目录权限
    sudo chown -R $USER:www-data $APP_DIR
    sudo chown -R $USER:$USER $BACKUP_DIR
    
    log_success "目录创建完成"
}

# 备份当前版本
backup_current() {
    log_info "备份当前版本..."
    
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_name="backup_${timestamp}"
    
    if [ -d "$APP_DIR" ]; then
        # 备份数据库
        if [ -f "$APP_DIR/.env" ]; then
            local db_name=$(grep "^DB_DATABASE=" $APP_DIR/.env | cut -d'=' -f2)
            local db_user=$(grep "^DB_USERNAME=" $APP_DIR/.env | cut -d'=' -f2)
            local db_pass=$(grep "^DB_PASSWORD=" $APP_DIR/.env | cut -d'=' -f2)
            
            if [ ! -z "$db_name" ]; then
                log_info "备份数据库: $db_name"
                mysqldump -u$db_user -p$db_pass $db_name | gzip > $BACKUP_DIR/db_${backup_name}.sql.gz
            fi
        fi
        
        # 备份应用文件
        log_info "备份应用文件..."
        tar -czf $BACKUP_DIR/files_${backup_name}.tar.gz \
            --exclude='node_modules' \
            --exclude='vendor' \
            --exclude='storage/logs' \
            --exclude='storage/framework/cache' \
            --exclude='storage/framework/sessions' \
            --exclude='storage/framework/views' \
            -C $(dirname $APP_DIR) $(basename $APP_DIR)
        
        echo $backup_name > $BACKUP_DIR/latest_backup.txt
        log_success "备份完成: $backup_name"
    else
        log_warning "应用目录不存在，跳过备份"
    fi
}

# 克隆或更新代码
update_code() {
    log_info "更新应用代码..."
    
    if [ -d "$APP_DIR/.git" ]; then
        log_info "更新现有代码库..."
        cd $APP_DIR
        git fetch origin
        git reset --hard origin/$BRANCH
        git clean -fd
    else
        log_info "克隆代码库..."
        sudo rm -rf $APP_DIR
        git clone -b $BRANCH $GIT_REPO $APP_DIR
        cd $APP_DIR
    fi
    
    log_success "代码更新完成"
}

# 安装依赖
install_dependencies() {
    log_info "安装应用依赖..."
    
    cd $APP_DIR
    
    # 安装PHP依赖
    log_info "安装PHP依赖..."
    if [ "$ENVIRONMENT" = "production" ]; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        composer install --optimize-autoloader --no-interaction
    fi
    
    # 安装Node.js依赖
    log_info "安装Node.js依赖..."
    if [ "$ENVIRONMENT" = "production" ]; then
        npm ci --production
    else
        npm ci
    fi
    
    log_success "依赖安装完成"
}

# 构建前端资源
build_assets() {
    log_info "构建前端资源..."
    
    cd $APP_DIR
    
    if [ "$ENVIRONMENT" = "production" ]; then
        npm run build
    else
        npm run dev
    fi
    
    log_success "前端资源构建完成"
}

# 配置环境
setup_environment() {
    log_info "配置应用环境..."
    
    cd $APP_DIR
    
    # 复制环境配置文件
    if [ ! -f ".env" ]; then
        if [ -f ".env.$ENVIRONMENT" ]; then
            cp .env.$ENVIRONMENT .env
            log_info "使用环境配置文件: .env.$ENVIRONMENT"
        else
            cp .env.example .env
            log_warning "使用示例配置文件，请手动配置 .env 文件"
        fi
    fi
    
    # 生成应用密钥
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        log_info "生成应用密钥"
    fi
    
    # 创建存储链接
    php artisan storage:link --force
    
    log_success "环境配置完成"
}

# 数据库迁移
run_migrations() {
    log_info "运行数据库迁移..."
    
    cd $APP_DIR
    
    # 检查数据库连接
    if ! php artisan migrate:status &> /dev/null; then
        log_error "数据库连接失败，请检查配置"
        exit 1
    fi
    
    # 运行迁移
    php artisan migrate --force
    
    # 如果是首次部署，运行数据填充
    if [ "$ACTION" = "deploy" ] && [ ! -f "$APP_DIR/.deployed" ]; then
        log_info "运行数据填充..."
        php artisan db:seed --force
        touch $APP_DIR/.deployed
    fi
    
    log_success "数据库迁移完成"
}

# 优化应用
optimize_application() {
    log_info "优化应用性能..."
    
    cd $APP_DIR
    
    # 清理缓存
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    if [ "$ENVIRONMENT" = "production" ]; then
        # 生产环境优化
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan event:cache
        
        # 优化Composer自动加载
        composer dump-autoload --optimize
    fi
    
    log_success "应用优化完成"
}

# 设置文件权限
set_permissions() {
    log_info "设置文件权限..."
    
    cd $APP_DIR
    
    # 设置目录所有者
    sudo chown -R $USER:www-data .
    
    # 设置目录权限
    sudo chmod -R 755 .
    sudo chmod -R 775 storage bootstrap/cache
    
    # 设置敏感文件权限
    chmod 600 .env
    
    log_success "文件权限设置完成"
}

# 重启服务
restart_services() {
    log_info "重启相关服务..."
    
    # 重启PHP-FPM
    sudo systemctl restart php$PHP_VERSION-fpm
    
    # 重启Nginx
    sudo systemctl restart nginx
    
    # 重启队列工作进程（如果存在）
    if systemctl is-active --quiet laravel-worker; then
        sudo systemctl restart laravel-worker
    fi
    
    log_success "服务重启完成"
}

# 验证部署
verify_deployment() {
    log_info "验证部署状态..."
    
    cd $APP_DIR
    
    # 检查应用状态
    if ! php artisan --version &> /dev/null; then
        log_error "Laravel应用无法启动"
        return 1
    fi
    
    # 检查数据库连接
    if ! php artisan migrate:status &> /dev/null; then
        log_error "数据库连接失败"
        return 1
    fi
    
    # 检查Web服务器响应
    local app_url=$(grep "^APP_URL=" .env | cut -d'=' -f2)
    if [ ! -z "$app_url" ]; then
        if curl -f -s "$app_url/health" > /dev/null; then
            log_success "Web服务器响应正常"
        else
            log_warning "Web服务器响应异常，请检查配置"
        fi
    fi
    
    log_success "部署验证完成"
}

# 回滚到上一个版本
rollback() {
    log_info "回滚到上一个版本..."
    
    if [ ! -f "$BACKUP_DIR/latest_backup.txt" ]; then
        log_error "没有找到备份文件"
        exit 1
    fi
    
    local backup_name=$(cat $BACKUP_DIR/latest_backup.txt)
    
    # 启用维护模式
    if [ -f "$APP_DIR/artisan" ]; then
        cd $APP_DIR
        php artisan down --message="系统回滚中，请稍后访问"
    fi
    
    # 恢复文件
    if [ -f "$BACKUP_DIR/files_${backup_name}.tar.gz" ]; then
        log_info "恢复应用文件..."
        sudo rm -rf $APP_DIR
        sudo mkdir -p $APP_DIR
        tar -xzf $BACKUP_DIR/files_${backup_name}.tar.gz -C $(dirname $APP_DIR)
        sudo chown -R $USER:www-data $APP_DIR
    fi
    
    # 恢复数据库
    if [ -f "$BACKUP_DIR/db_${backup_name}.sql.gz" ]; then
        log_info "恢复数据库..."
        cd $APP_DIR
        local db_name=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
        local db_user=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2)
        local db_pass=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2)
        
        if [ ! -z "$db_name" ]; then
            zcat $BACKUP_DIR/db_${backup_name}.sql.gz | mysql -u$db_user -p$db_pass $db_name
        fi
    fi
    
    # 重启服务
    restart_services
    
    # 关闭维护模式
    if [ -f "$APP_DIR/artisan" ]; then
        cd $APP_DIR
        php artisan up
    fi
    
    log_success "回滚完成"
}

# 清理旧备份
cleanup_backups() {
    log_info "清理旧备份文件..."
    
    # 保留最近7天的备份
    find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
    find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
    
    log_success "备份清理完成"
}

# 显示帮助信息
show_help() {
    echo "Laravel 链接聚合应用快速部署脚本"
    echo ""
    echo "使用方法:"
    echo "  $0 [环境] [操作]"
    echo ""
    echo "环境:"
    echo "  production  - 生产环境（默认）"
    echo "  staging     - 测试环境"
    echo "  development - 开发环境"
    echo ""
    echo "操作:"
    echo "  deploy      - 完整部署（默认）"
    echo "  update      - 更新应用"
    echo "  rollback    - 回滚到上一版本"
    echo "  backup      - 仅执行备份"
    echo "  help        - 显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 production deploy    # 生产环境完整部署"
    echo "  $0 staging update       # 测试环境更新"
    echo "  $0 production rollback  # 生产环境回滚"
    echo ""
}

# 主函数
main() {
    echo "======================================"
    echo "Laravel 链接聚合应用部署脚本"
    echo "环境: $ENVIRONMENT"
    echo "操作: $ACTION"
    echo "======================================"
    echo ""
    
    case $ACTION in
        "deploy")
            check_requirements
            setup_directories
            backup_current
            update_code
            install_dependencies
            build_assets
            setup_environment
            run_migrations
            optimize_application
            set_permissions
            restart_services
            verify_deployment
            cleanup_backups
            log_success "部署完成！"
            ;;
        "update")
            check_requirements
            backup_current
            update_code
            install_dependencies
            build_assets
            run_migrations
            optimize_application
            set_permissions
            restart_services
            verify_deployment
            log_success "更新完成！"
            ;;
        "rollback")
            rollback
            log_success "回滚完成！"
            ;;
        "backup")
            backup_current
            log_success "备份完成！"
            ;;
        "help")
            show_help
            ;;
        *)
            log_error "未知操作: $ACTION"
            show_help
            exit 1
            ;;
    esac
}

# 执行主函数
main "$@"