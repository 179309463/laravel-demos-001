# Web 服务器配置指南

本文档详细说明如何为 Laravel 链接聚合应用配置 Nginx 和 Apache Web 服务器。

## 📋 目录

- [Nginx 配置](#nginx-配置)
- [Apache 配置](#apache-配置)
- [SSL 证书配置](#ssl-证书配置)
- [性能优化](#性能优化)
- [安全配置](#安全配置)
- [故障排除](#故障排除)

## 🚀 Nginx 配置

### 1. 安装 Nginx

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx

# CentOS/RHEL
sudo yum install nginx
# 或者 (CentOS 8+)
sudo dnf install nginx

# macOS
brew install nginx
```

### 2. 配置虚拟主机

1. 复制配置文件：
```bash
sudo cp config/nginx/laravel-links.conf /etc/nginx/sites-available/
```

2. 创建符号链接：
```bash
sudo ln -s /etc/nginx/sites-available/laravel-links.conf /etc/nginx/sites-enabled/
```

3. 修改配置文件中的域名和路径：
```bash
sudo nano /etc/nginx/sites-available/laravel-links.conf
```

需要修改的内容：
- `your-domain.com` → 你的实际域名
- `/var/www/laravel-links` → 你的应用路径
- SSL 证书路径
- PHP-FPM socket 路径

4. 测试配置：
```bash
sudo nginx -t
```

5. 重启 Nginx：
```bash
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 3. PHP-FPM 配置

安装和配置 PHP-FPM：

```bash
# Ubuntu/Debian
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd

# 配置 PHP-FPM
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

重要配置项：
```ini
; 进程管理
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

; 性能优化
pm.max_requests = 500
request_terminate_timeout = 300

; 安全配置
security.limit_extensions = .php
```

## 🔧 Apache 配置

### 1. 安装 Apache

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2

# CentOS/RHEL
sudo yum install httpd
# 或者 (CentOS 8+)
sudo dnf install httpd

# macOS
brew install httpd
```

### 2. 启用必要的模块

```bash
# 启用模块
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo a2enmod proxy_fcgi
sudo a2enmod setenvif
```

### 3. 配置虚拟主机

1. 复制配置文件：
```bash
sudo cp config/apache/laravel-links.conf /etc/apache2/sites-available/
```

2. 启用站点：
```bash
sudo a2ensite laravel-links.conf
```

3. 修改配置文件：
```bash
sudo nano /etc/apache2/sites-available/laravel-links.conf
```

需要修改的内容：
- `your-domain.com` → 你的实际域名
- `/var/www/laravel-links` → 你的应用路径
- SSL 证书路径

4. 测试配置：
```bash
sudo apache2ctl configtest
```

5. 重启 Apache：
```bash
sudo systemctl restart apache2
sudo systemctl enable apache2
```

## 🔒 SSL 证书配置

### 1. 使用 Let's Encrypt（推荐）

```bash
# 安装 Certbot
sudo apt install certbot python3-certbot-nginx  # Nginx
# 或者
sudo apt install certbot python3-certbot-apache  # Apache

# 获取证书
sudo certbot --nginx -d your-domain.com -d www.your-domain.com  # Nginx
# 或者
sudo certbot --apache -d your-domain.com -d www.your-domain.com  # Apache

# 设置自动续期
sudo crontab -e
# 添加以下行：
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. 使用自签名证书（仅用于测试）

```bash
# 创建证书目录
sudo mkdir -p /etc/ssl/private
sudo mkdir -p /etc/ssl/certs

# 生成私钥
sudo openssl genrsa -out /etc/ssl/private/your-domain.com.key 2048

# 生成证书
sudo openssl req -new -x509 -key /etc/ssl/private/your-domain.com.key -out /etc/ssl/certs/your-domain.com.crt -days 365

# 设置权限
sudo chmod 600 /etc/ssl/private/your-domain.com.key
sudo chmod 644 /etc/ssl/certs/your-domain.com.crt
```

## ⚡ 性能优化

### 1. 启用 Gzip 压缩

**Nginx**：
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_comp_level 6;
```

**Apache**：
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript
</IfModule>
```

### 2. 配置缓存头部

静态文件缓存配置已包含在配置文件中，可以根据需要调整缓存时间。

### 3. 启用 HTTP/2

**Nginx**：
```nginx
listen 443 ssl http2;
```

**Apache**：
```bash
# 启用 HTTP/2 模块
sudo a2enmod http2

# 在虚拟主机中添加
Protocols h2 http/1.1
```

## 🛡️ 安全配置

### 1. 防火墙配置

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# firewalld (CentOS)
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 2. 文件权限

```bash
# 设置应用目录权限
sudo chown -R www-data:www-data /var/www/laravel-links
sudo find /var/www/laravel-links -type f -exec chmod 644 {} \;
sudo find /var/www/laravel-links -type d -exec chmod 755 {} \;

# 设置存储目录权限
sudo chmod -R 775 /var/www/laravel-links/storage
sudo chmod -R 775 /var/www/laravel-links/bootstrap/cache
```

### 3. 隐藏敏感信息

配置文件中已包含隐藏服务器版本和敏感文件的配置。

## 🔍 故障排除

### 1. 常见问题

**403 Forbidden 错误**：
- 检查文件权限
- 确认 Web 服务器用户有访问权限
- 检查 SELinux 设置（CentOS/RHEL）

**500 Internal Server Error**：
- 检查 PHP 错误日志
- 确认 .env 文件配置正确
- 检查 Laravel 日志文件

**SSL 证书错误**：
- 验证证书文件路径
- 检查证书有效期
- 确认域名匹配

### 2. 日志文件位置

**Nginx**：
- 访问日志：`/var/log/nginx/laravel-links.access.log`
- 错误日志：`/var/log/nginx/laravel-links.error.log`

**Apache**：
- 访问日志：`/var/log/apache2/laravel-links-ssl-access.log`
- 错误日志：`/var/log/apache2/laravel-links-ssl-error.log`

**Laravel**：
- 应用日志：`/var/www/laravel-links/storage/logs/laravel.log`

### 3. 性能监控

```bash
# 监控 Web 服务器状态
sudo systemctl status nginx  # 或 apache2

# 查看进程
sudo ps aux | grep nginx  # 或 apache2

# 监控资源使用
top
htop
```

## 📊 监控和维护

### 1. 日志轮转

```bash
# 配置 logrotate
sudo nano /etc/logrotate.d/laravel-links
```

内容：
```
/var/log/nginx/laravel-links*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data adm
    postrotate
        systemctl reload nginx
    endscript
}
```

### 2. 健康检查

配置文件中已包含 `/health` 端点，可以用于监控服务状态。

### 3. 备份策略

- 定期备份应用文件
- 备份数据库
- 备份配置文件
- 测试恢复流程

通过遵循这些配置和最佳实践，你的 Laravel 链接聚合应用将能够在生产环境中稳定、安全、高效地运行。