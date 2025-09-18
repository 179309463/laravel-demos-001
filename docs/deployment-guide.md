# Laravel 链接聚合应用部署指南

## 目录

1. [部署前准备](#部署前准备)
2. [服务器环境配置](#服务器环境配置)
3. [应用部署步骤](#应用部署步骤)
4. [数据库配置](#数据库配置)
5. [Web服务器配置](#web服务器配置)
6. [SSL证书配置](#ssl证书配置)
7. [性能优化](#性能优化)
8. [安全配置](#安全配置)
9. [监控和日志](#监控和日志)
10. [备份策略](#备份策略)
11. [故障排除](#故障排除)
12. [维护指南](#维护指南)

## 部署前准备

### 系统要求

- **操作系统**: Ubuntu 20.04 LTS 或 CentOS 8+
- **PHP**: 8.1 或更高版本
- **数据库**: MySQL 8.0 或 MariaDB 10.6+
- **Web服务器**: Nginx 1.18+ 或 Apache 2.4+
- **内存**: 最少 2GB RAM（推荐 4GB+）
- **存储**: 最少 20GB 可用空间
- **网络**: 稳定的互联网连接

### 必需的PHP扩展

```bash
# 检查PHP扩展
php -m | grep -E "(bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|zip|gd|curl|redis)"
```

### 准备清单

- [ ] 服务器访问权限（SSH密钥）
- [ ] 域名和DNS配置
- [ ] SSL证书（Let's Encrypt或商业证书）
- [ ] 数据库访问凭据
- [ ] 第三方服务API密钥
- [ ] 备份存储配置

## 服务器环境配置

### 1. 更新系统包

```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 2. 安装基础软件

```bash
# Ubuntu/Debian
sudo apt install -y curl wget git unzip software-properties-common

# CentOS/RHEL
sudo yum install -y curl wget git unzip epel-release
```

### 3. 安装PHP 8.1

```bash
# Ubuntu/Debian
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis \
    php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-curl \
    php8.1-gd php8.1-zip php8.1-intl php8.1-soap

# CentOS/RHEL
sudo yum install -y php81 php81-php-fpm php81-php-mysql php81-php-redis \
    php81-php-mbstring php81-php-xml php81-php-bcmath php81-php-curl \
    php81-php-gd php81-php-zip php81-php-intl php81-php-soap
```

### 4. 安装Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 5. 安装Node.js和npm

```bash
# 使用NodeSource仓库
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# 或使用nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 18
nvm use 18
```

### 6. 安装MySQL

```bash
# Ubuntu/Debian
sudo apt install -y mysql-server mysql-client

# 安全配置
sudo mysql_secure_installation
```

### 7. 安装Redis

```bash
# Ubuntu/Debian
sudo apt install -y redis-server

# 启动并启用Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### 8. 安装Nginx

```bash
# Ubuntu/Debian
sudo apt install -y nginx

# 启动并启用Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

## 应用部署步骤

### 1. 创建部署用户

```bash
# 创建专用部署用户
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG www-data deploy

# 设置SSH密钥
sudo mkdir -p /home/deploy/.ssh
sudo cp ~/.ssh/authorized_keys /home/deploy/.ssh/
sudo chown -R deploy:deploy /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo chmod 600 /home/deploy/.ssh/authorized_keys
```

### 2. 克隆项目代码

```bash
# 切换到部署用户
sudo su - deploy

# 创建项目目录
mkdir -p /var/www
cd /var/www

# 克隆代码
git clone https://github.com/your-username/laravel-links-app.git
cd laravel-links-app
```

### 3. 安装依赖

```bash
# 安装PHP依赖
composer install --no-dev --optimize-autoloader

# 安装Node.js依赖
npm ci --production

# 构建前端资源
npm run build
```

### 4. 配置环境文件

```bash
# 复制生产环境配置
cp .env.production .env

# 生成应用密钥
php artisan key:generate

# 编辑环境配置
nano .env
```

**重要配置项**:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_links
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### 5. 设置文件权限

```bash
# 设置目录所有者
sudo chown -R deploy:www-data /var/www/laravel-links-app

# 设置目录权限
sudo chmod -R 755 /var/www/laravel-links-app
sudo chmod -R 775 /var/www/laravel-links-app/storage
sudo chmod -R 775 /var/www/laravel-links-app/bootstrap/cache

# 设置SELinux上下文（如果启用）
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_can_network_connect_db 1
```

## 数据库配置

### 1. 创建数据库和用户

```sql
-- 登录MySQL
mysql -u root -p

-- 创建数据库
CREATE DATABASE laravel_links CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password';

-- 授权
GRANT ALL PRIVILEGES ON laravel_links.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;

-- 退出
EXIT;
```

### 2. 运行数据库迁移

```bash
# 运行迁移
php artisan migrate --force

# 填充基础数据
php artisan db:seed --force

# 创建存储链接
php artisan storage:link
```

### 3. 数据库优化

```bash
# 应用数据库优化配置
sudo cp config/database-production.php config/database.php

# 重启MySQL以应用配置
sudo systemctl restart mysql
```

## Web服务器配置

### Nginx配置

```bash
# 复制Nginx配置
sudo cp config/nginx/laravel-links.conf /etc/nginx/sites-available/

# 启用站点
sudo ln -s /etc/nginx/sites-available/laravel-links.conf /etc/nginx/sites-enabled/

# 删除默认站点
sudo rm /etc/nginx/sites-enabled/default

# 测试配置
sudo nginx -t

# 重启Nginx
sudo systemctl restart nginx
```

### Apache配置（可选）

```bash
# 复制Apache配置
sudo cp config/apache/laravel-links.conf /etc/apache2/sites-available/

# 启用站点和模块
sudo a2ensite laravel-links.conf
sudo a2enmod rewrite ssl headers

# 禁用默认站点
sudo a2dissite 000-default

# 测试配置
sudo apache2ctl configtest

# 重启Apache
sudo systemctl restart apache2
```

## SSL证书配置

### 使用Let's Encrypt

```bash
# 安装Certbot
sudo apt install -y certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# 设置自动续期
sudo crontab -e
# 添加以下行：
# 0 12 * * * /usr/bin/certbot renew --quiet
```

### 使用商业证书

```bash
# 创建证书目录
sudo mkdir -p /etc/ssl/certs/laravel-links
sudo mkdir -p /etc/ssl/private/laravel-links

# 复制证书文件
sudo cp your-certificate.crt /etc/ssl/certs/laravel-links/
sudo cp your-private-key.key /etc/ssl/private/laravel-links/
sudo cp ca-bundle.crt /etc/ssl/certs/laravel-links/

# 设置权限
sudo chmod 644 /etc/ssl/certs/laravel-links/*
sudo chmod 600 /etc/ssl/private/laravel-links/*
```

## 性能优化

### 1. Laravel优化

```bash
# 运行优化脚本
./build-production.sh

# 或手动执行优化命令
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer dump-autoload --optimize
```

### 2. PHP-FPM优化

```bash
# 编辑PHP-FPM配置
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

**关键配置**:

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
```

### 3. 数据库优化

```bash
# 编辑MySQL配置
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

**添加优化配置**:

```ini
[mysqld]
# InnoDB优化
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# 查询缓存
query_cache_type = 1
query_cache_size = 128M

# 连接优化
max_connections = 200
wait_timeout = 600
interactive_timeout = 600
```

## 安全配置

### 1. 防火墙配置

```bash
# 启用UFW防火墙
sudo ufw enable

# 允许SSH
sudo ufw allow ssh

# 允许HTTP和HTTPS
sudo ufw allow 80
sudo ufw allow 443

# 检查状态
sudo ufw status
```

### 2. 安全更新

```bash
# 启用自动安全更新
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 3. 应用安全配置

```bash
# 注册安全中间件
php artisan make:middleware SecurityHeaders
php artisan make:middleware RateLimitMiddleware
php artisan make:middleware InputSanitization
```

## 监控和日志

### 1. 配置日志轮转

```bash
# 创建logrotate配置
sudo nano /etc/logrotate.d/laravel-links
```

**配置内容**:

```
/var/www/laravel-links-app/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 deploy www-data
    postrotate
        /bin/systemctl reload php8.1-fpm > /dev/null 2>&1 || true
    endscript
}
```

### 2. 设置监控

```bash
# 创建健康检查脚本
sudo nano /usr/local/bin/health-check.sh
```

**脚本内容**:

```bash
#!/bin/bash

# 检查应用健康状态
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

if [ $response -eq 200 ]; then
    echo "Application is healthy"
    exit 0
else
    echo "Application health check failed with status: $response"
    # 发送告警通知
    # mail -s "Application Health Alert" admin@example.com < /dev/null
    exit 1
fi
```

```bash
# 设置执行权限
sudo chmod +x /usr/local/bin/health-check.sh

# 添加到crontab
sudo crontab -e
# 添加：*/5 * * * * /usr/local/bin/health-check.sh
```

## 备份策略

### 1. 数据库备份

```bash
# 创建备份脚本
sudo nano /usr/local/bin/backup-database.sh
```

**脚本内容**:

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/laravel-links"
DB_NAME="laravel_links"
DB_USER="laravel_user"
DB_PASS="your_password"

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# 删除7天前的备份
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

echo "Database backup completed: db_backup_$DATE.sql.gz"
```

### 2. 文件备份

```bash
# 创建文件备份脚本
sudo nano /usr/local/bin/backup-files.sh
```

**脚本内容**:

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/laravel-links"
APP_DIR="/var/www/laravel-links-app"

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份应用文件（排除不必要的目录）
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    -C $(dirname $APP_DIR) $(basename $APP_DIR)

# 删除7天前的备份
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete

echo "Files backup completed: files_backup_$DATE.tar.gz"
```

### 3. 自动备份

```bash
# 设置执行权限
sudo chmod +x /usr/local/bin/backup-database.sh
sudo chmod +x /usr/local/bin/backup-files.sh

# 添加到crontab
sudo crontab -e
# 添加以下行：
# 0 2 * * * /usr/local/bin/backup-database.sh
# 0 3 * * * /usr/local/bin/backup-files.sh
```

## 故障排除

### 常见问题

#### 1. 500内部服务器错误

```bash
# 检查错误日志
tail -f /var/www/laravel-links-app/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# 检查文件权限
ls -la /var/www/laravel-links-app/storage
ls -la /var/www/laravel-links-app/bootstrap/cache

# 重新设置权限
sudo chmod -R 775 /var/www/laravel-links-app/storage
sudo chmod -R 775 /var/www/laravel-links-app/bootstrap/cache
```

#### 2. 数据库连接失败

```bash
# 测试数据库连接
mysql -u laravel_user -p laravel_links

# 检查MySQL状态
sudo systemctl status mysql

# 检查配置文件
cat /var/www/laravel-links-app/.env | grep DB_
```

#### 3. 队列任务不执行

```bash
# 检查队列工作进程
ps aux | grep "queue:work"

# 手动启动队列工作进程
php artisan queue:work --daemon

# 检查Redis连接
redis-cli ping
```

#### 4. 性能问题

```bash
# 检查系统资源
top
free -h
df -h

# 检查PHP-FPM状态
sudo systemctl status php8.1-fpm

# 检查慢查询日志
sudo tail -f /var/log/mysql/mysql-slow.log
```

### 日志文件位置

- **应用日志**: `/var/www/laravel-links-app/storage/logs/`
- **Nginx日志**: `/var/log/nginx/`
- **Apache日志**: `/var/log/apache2/`
- **MySQL日志**: `/var/log/mysql/`
- **PHP-FPM日志**: `/var/log/php8.1-fpm.log`
- **系统日志**: `/var/log/syslog`

## 维护指南

### 日常维护任务

#### 1. 系统更新

```bash
# 每周执行系统更新
sudo apt update && sudo apt upgrade -y

# 重启服务（如需要）
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
```

#### 2. 日志清理

```bash
# 清理旧日志文件
sudo find /var/log -name "*.log" -mtime +30 -delete
sudo find /var/www/laravel-links-app/storage/logs -name "*.log" -mtime +30 -delete

# 清理临时文件
sudo find /tmp -mtime +7 -delete
```

#### 3. 性能监控

```bash
# 检查系统性能
sudo iotop
sudo htop
sudo nethogs

# 检查数据库性能
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G"
```

### 应用更新流程

#### 1. 准备更新

```bash
# 创建备份
/usr/local/bin/backup-database.sh
/usr/local/bin/backup-files.sh

# 启用维护模式
php artisan down --message="系统维护中，请稍后访问"
```

#### 2. 执行更新

```bash
# 拉取最新代码
git pull origin main

# 更新依赖
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 运行迁移
php artisan migrate --force

# 清理缓存
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3. 完成更新

```bash
# 重启服务
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# 关闭维护模式
php artisan up

# 验证更新
curl -I https://your-domain.com
```

### 安全维护

#### 1. 定期安全检查

```bash
# 检查失败的登录尝试
sudo grep "Failed password" /var/log/auth.log | tail -20

# 检查可疑的网络连接
sudo netstat -tulpn | grep LISTEN

# 检查系统用户
cat /etc/passwd | grep -v nologin
```

#### 2. 更新安全配置

```bash
# 更新防火墙规则
sudo ufw status numbered

# 检查SSL证书有效期
sudo certbot certificates

# 更新应用密钥（如需要）
php artisan key:generate --force
```

### 监控和告警

#### 1. 设置监控脚本

```bash
# 创建综合监控脚本
sudo nano /usr/local/bin/system-monitor.sh
```

**脚本内容**:

```bash
#!/bin/bash

# 检查磁盘使用率
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    echo "WARNING: Disk usage is ${DISK_USAGE}%"
fi

# 检查内存使用率
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEM_USAGE -gt 90 ]; then
    echo "WARNING: Memory usage is ${MEM_USAGE}%"
fi

# 检查CPU负载
LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
if (( $(echo "$LOAD_AVG > 2.0" | bc -l) )); then
    echo "WARNING: High CPU load: $LOAD_AVG"
fi

# 检查关键服务状态
for service in nginx php8.1-fpm mysql redis-server; do
    if ! systemctl is-active --quiet $service; then
        echo "ERROR: $service is not running"
    fi
done
```

#### 2. 设置告警通知

```bash
# 安装邮件工具
sudo apt install -y mailutils

# 配置邮件发送
sudo nano /etc/postfix/main.cf

# 测试邮件发送
echo "Test message" | mail -s "Test Subject" admin@example.com
```

## 总结

本部署指南涵盖了Laravel链接聚合应用的完整部署流程，包括：

- ✅ 服务器环境配置
- ✅ 应用部署和配置
- ✅ 数据库设置和优化
- ✅ Web服务器配置
- ✅ SSL证书配置
- ✅ 性能优化
- ✅ 安全配置
- ✅ 监控和日志
- ✅ 备份策略
- ✅ 故障排除
- ✅ 维护指南

按照本指南操作，您的Laravel链接聚合应用将能够在生产环境中稳定、安全、高效地运行。

### 重要提醒

1. **定期备份**: 确保数据库和文件的定期备份
2. **安全更新**: 及时应用系统和应用的安全更新
3. **监控告警**: 设置完善的监控和告警机制
4. **性能优化**: 根据实际使用情况调整性能配置
5. **文档更新**: 保持部署文档的及时更新

如有问题，请参考故障排除部分或联系技术支持团队。