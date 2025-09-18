# Laravel 链接聚合应用运维手册

## 目录

1. [日常运维任务](#日常运维任务)
2. [监控和告警](#监控和告警)
3. [性能调优](#性能调优)
4. [安全管理](#安全管理)
5. [备份和恢复](#备份和恢复)
6. [故障处理](#故障处理)
7. [版本更新](#版本更新)
8. [容量规划](#容量规划)
9. [应急响应](#应急响应)
10. [运维工具](#运维工具)

## 日常运维任务

### 每日检查清单

#### 系统健康检查

```bash
# 1. 检查系统资源使用情况
echo "=== 系统资源使用情况 ==="
free -h
df -h
uptime

# 2. 检查关键服务状态
echo "\n=== 服务状态检查 ==="
sudo systemctl status nginx php8.1-fpm mysql redis-server

# 3. 检查应用健康状态
echo "\n=== 应用健康检查 ==="
curl -s http://localhost/health | jq .

# 4. 检查错误日志
echo "\n=== 最近的错误日志 ==="
tail -20 /var/www/laravel-links-app/storage/logs/laravel.log
tail -20 /var/log/nginx/error.log
```

#### 性能指标检查

```bash
# 检查数据库性能
mysql -u root -p -e "
    SELECT 
        SCHEMA_NAME as '数据库',
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as '大小(MB)'
    FROM information_schema.tables 
    WHERE SCHEMA_NAME = 'laravel_links'
    GROUP BY SCHEMA_NAME;
"

# 检查慢查询
mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log';"
mysql -u root -p -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';"

# 检查Redis内存使用
redis-cli info memory | grep used_memory_human

# 检查PHP-FPM进程
sudo systemctl status php8.1-fpm --no-pager -l
```

### 每周维护任务

#### 系统更新

```bash
#!/bin/bash
# weekly-maintenance.sh

echo "开始每周维护任务..."

# 1. 系统包更新
echo "更新系统包..."
sudo apt update
sudo apt list --upgradable

# 2. 清理日志文件
echo "清理旧日志文件..."
sudo find /var/log -name "*.log" -mtime +30 -delete
sudo find /var/www/laravel-links-app/storage/logs -name "*.log" -mtime +7 -delete

# 3. 清理临时文件
echo "清理临时文件..."
sudo find /tmp -mtime +7 -delete
sudo find /var/tmp -mtime +7 -delete

# 4. 数据库优化
echo "优化数据库..."
mysql -u root -p laravel_links -e "OPTIMIZE TABLE links, users, categories;"

# 5. 检查磁盘使用情况
echo "检查磁盘使用情况..."
df -h
du -sh /var/www/laravel-links-app/*

echo "每周维护任务完成"
```

#### 安全检查

```bash
#!/bin/bash
# security-check.sh

echo "开始安全检查..."

# 1. 检查失败的登录尝试
echo "检查失败的登录尝试..."
sudo grep "Failed password" /var/log/auth.log | tail -10

# 2. 检查可疑的网络连接
echo "检查网络连接..."
sudo netstat -tulpn | grep LISTEN

# 3. 检查系统用户
echo "检查系统用户..."
cat /etc/passwd | grep -v nologin | grep -v false

# 4. 检查文件权限
echo "检查关键文件权限..."
ls -la /var/www/laravel-links-app/.env
ls -la /var/www/laravel-links-app/storage

# 5. 检查SSL证书有效期
echo "检查SSL证书..."
sudo certbot certificates

echo "安全检查完成"
```

### 每月任务

#### 容量规划检查

```bash
#!/bin/bash
# capacity-planning.sh

echo "开始容量规划检查..."

# 1. 数据库增长趋势
echo "数据库增长趋势..."
mysql -u root -p -e "
    SELECT 
        table_name as '表名',
        table_rows as '行数',
        ROUND(((data_length + index_length) / 1024 / 1024), 2) as '大小(MB)'
    FROM information_schema.tables 
    WHERE table_schema = 'laravel_links'
    ORDER BY (data_length + index_length) DESC;
"

# 2. 存储使用趋势
echo "存储使用趋势..."
du -sh /var/www/laravel-links-app/storage/app/public/*

# 3. 访问量统计
echo "访问量统计..."
awk '{print $1}' /var/log/nginx/access.log | sort | uniq -c | sort -nr | head -10

# 4. 性能指标汇总
echo "性能指标汇总..."
echo "平均负载: $(uptime | awk -F'load average:' '{print $2}')"
echo "内存使用: $(free | awk 'NR==2{printf "%.2f%%", $3*100/$2}')"
echo "磁盘使用: $(df / | awk 'NR==2{print $5}')"

echo "容量规划检查完成"
```

## 监控和告警

### 监控指标

#### 系统级监控

1. **CPU使用率**
   - 阈值：> 80% 警告，> 90% 严重
   - 检查命令：`top`, `htop`, `iostat`

2. **内存使用率**
   - 阈值：> 85% 警告，> 95% 严重
   - 检查命令：`free -h`, `ps aux --sort=-%mem`

3. **磁盘使用率**
   - 阈值：> 80% 警告，> 90% 严重
   - 检查命令：`df -h`, `du -sh /*`

4. **网络连接**
   - 监控：连接数、带宽使用
   - 检查命令：`netstat -an`, `iftop`, `nethogs`

#### 应用级监控

1. **响应时间**
   - 阈值：> 2秒警告，> 5秒严重
   - 监控端点：`/health`, `/api/status`

2. **错误率**
   - 阈值：> 1% 警告，> 5% 严重
   - 日志位置：`storage/logs/laravel.log`

3. **队列任务**
   - 监控：队列长度、失败任务数
   - 检查命令：`php artisan queue:monitor`

4. **数据库连接**
   - 监控：连接数、慢查询
   - 检查命令：`SHOW PROCESSLIST`, `SHOW STATUS`

### 告警配置

#### 邮件告警脚本

```bash
#!/bin/bash
# alert-system.sh

# 配置
ALERT_EMAIL="admin@example.com"
APP_NAME="Laravel Links App"
SERVER_NAME=$(hostname)

# 发送告警邮件
send_alert() {
    local level=$1
    local message=$2
    local subject="[$level] $APP_NAME - $SERVER_NAME"
    
    echo "时间: $(date)" > /tmp/alert.txt
    echo "服务器: $SERVER_NAME" >> /tmp/alert.txt
    echo "级别: $level" >> /tmp/alert.txt
    echo "消息: $message" >> /tmp/alert.txt
    echo "" >> /tmp/alert.txt
    echo "系统状态:" >> /tmp/alert.txt
    uptime >> /tmp/alert.txt
    free -h >> /tmp/alert.txt
    df -h >> /tmp/alert.txt
    
    mail -s "$subject" $ALERT_EMAIL < /tmp/alert.txt
    rm /tmp/alert.txt
}

# 检查CPU使用率
check_cpu() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
    if (( $(echo "$cpu_usage > 90" | bc -l) )); then
        send_alert "CRITICAL" "CPU使用率过高: ${cpu_usage}%"
    elif (( $(echo "$cpu_usage > 80" | bc -l) )); then
        send_alert "WARNING" "CPU使用率较高: ${cpu_usage}%"
    fi
}

# 检查内存使用率
check_memory() {
    local mem_usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
    if [ $mem_usage -gt 95 ]; then
        send_alert "CRITICAL" "内存使用率过高: ${mem_usage}%"
    elif [ $mem_usage -gt 85 ]; then
        send_alert "WARNING" "内存使用率较高: ${mem_usage}%"
    fi
}

# 检查磁盘使用率
check_disk() {
    local disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ $disk_usage -gt 90 ]; then
        send_alert "CRITICAL" "磁盘使用率过高: ${disk_usage}%"
    elif [ $disk_usage -gt 80 ]; then
        send_alert "WARNING" "磁盘使用率较高: ${disk_usage}%"
    fi
}

# 检查服务状态
check_services() {
    local services=("nginx" "php8.1-fpm" "mysql" "redis-server")
    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet $service; then
            send_alert "CRITICAL" "服务 $service 未运行"
        fi
    done
}

# 检查应用健康状态
check_app_health() {
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)
    if [ $response -ne 200 ]; then
        send_alert "CRITICAL" "应用健康检查失败，HTTP状态码: $response"
    fi
}

# 执行所有检查
check_cpu
check_memory
check_disk
check_services
check_app_health
```

#### 设置定时监控

```bash
# 添加到crontab
sudo crontab -e

# 每5分钟检查一次
*/5 * * * * /usr/local/bin/alert-system.sh

# 每小时生成性能报告
0 * * * * /usr/local/bin/performance-report.sh

# 每天凌晨2点执行备份
0 2 * * * /usr/local/bin/backup-database.sh

# 每周日凌晨3点执行维护任务
0 3 * * 0 /usr/local/bin/weekly-maintenance.sh
```

## 性能调优

### 数据库优化

#### MySQL配置优化

```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
# 基础配置
max_connections = 200
wait_timeout = 600
interactive_timeout = 600

# InnoDB优化
innodb_buffer_pool_size = 2G  # 设置为可用内存的70-80%
innodb_log_file_size = 512M
innodb_log_buffer_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1

# 查询缓存
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 2M

# 临时表
tmp_table_size = 128M
max_heap_table_size = 128M

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 2
log_queries_not_using_indexes = 1
```

#### 数据库索引优化

```sql
-- 检查缺失的索引
SELECT 
    table_name,
    column_name,
    cardinality,
    sub_part,
    packed,
    nullable,
    index_type
FROM information_schema.statistics 
WHERE table_schema = 'laravel_links'
ORDER BY table_name, seq_in_index;

-- 分析表使用情况
SELECT 
    table_name,
    table_rows,
    avg_row_length,
    data_length,
    index_length,
    (data_length + index_length) as total_size
FROM information_schema.tables 
WHERE table_schema = 'laravel_links'
ORDER BY total_size DESC;

-- 优化表
OPTIMIZE TABLE links, users, categories, tags;

-- 分析表
ANALYZE TABLE links, users, categories, tags;
```

### PHP-FPM优化

#### 进程池配置

```ini
# /etc/php/8.1/fpm/pool.d/www.conf

[www]
user = www-data
group = www-data

# 监听配置
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
listen.backlog = 511

# 进程管理
pm = dynamic
pm.max_children = 50        # 根据内存调整
pm.start_servers = 10       # 启动时的进程数
pm.min_spare_servers = 5    # 最少空闲进程
pm.max_spare_servers = 20   # 最多空闲进程
pm.max_requests = 1000      # 每个进程处理的最大请求数

# 性能调优
request_terminate_timeout = 300
rlimit_files = 65536
rlimit_core = 0

# PHP配置
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[max_file_uploads] = 20

# OPcache配置
php_admin_value[opcache.enable] = 1
php_admin_value[opcache.memory_consumption] = 256
php_admin_value[opcache.interned_strings_buffer] = 16
php_admin_value[opcache.max_accelerated_files] = 10000
php_admin_value[opcache.validate_timestamps] = 0  # 生产环境设为0
php_admin_value[opcache.save_comments] = 1
php_admin_value[opcache.fast_shutdown] = 1
```

### Nginx优化

#### 性能配置

```nginx
# /etc/nginx/nginx.conf

user www-data;
worker_processes auto;
worker_rlimit_nofile 65535;

events {
    worker_connections 2048;
    use epoll;
    multi_accept on;
}

http {
    # 基础配置
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    keepalive_requests 1000;
    types_hash_max_size 2048;
    server_tokens off;
    
    # 缓冲区配置
    client_body_buffer_size 128k;
    client_max_body_size 50m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    output_buffers 1 32k;
    postpone_output 1460;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
    
    # 缓存配置
    open_file_cache max=200000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # 日志格式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    '$request_time $upstream_response_time';
    
    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log warn;
    
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

### Redis优化

#### 配置优化

```conf
# /etc/redis/redis.conf

# 内存配置
maxmemory 1gb
maxmemory-policy allkeys-lru

# 持久化配置
save 900 1
save 300 10
save 60 10000

# AOF配置
appendonly yes
appendfsync everysec
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb

# 网络配置
tcp-keepalive 300
timeout 0

# 性能配置
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
list-compress-depth 0
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64
```

## 安全管理

### 访问控制

#### 防火墙配置

```bash
# UFW防火墙配置
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing

# 允许SSH（限制IP范围）
sudo ufw allow from 192.168.1.0/24 to any port 22

# 允许HTTP和HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# 允许MySQL（仅本地）
sudo ufw allow from 127.0.0.1 to any port 3306

# 允许Redis（仅本地）
sudo ufw allow from 127.0.0.1 to any port 6379

# 启用防火墙
sudo ufw --force enable

# 查看状态
sudo ufw status numbered
```

#### Fail2Ban配置

```ini
# /etc/fail2ban/jail.local

[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3
ignoreip = 127.0.0.1/8 192.168.1.0/24

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10

[laravel]
enabled = true
filter = laravel
logpath = /var/www/laravel-links-app/storage/logs/laravel.log
maxretry = 5
bantime = 7200
```

### SSL/TLS管理

#### 证书更新脚本

```bash
#!/bin/bash
# ssl-renewal.sh

echo "开始SSL证书更新检查..."

# 检查证书有效期
certbot certificates | grep "Expiry Date" | while read line; do
    expiry_date=$(echo $line | awk '{print $3" "$4}')
    days_left=$(( ($(date -d "$expiry_date" +%s) - $(date +%s)) / 86400 ))
    
    if [ $days_left -lt 30 ]; then
        echo "证书将在 $days_left 天后过期，开始更新..."
        
        # 更新证书
        certbot renew --quiet
        
        # 重启Nginx
        sudo systemctl reload nginx
        
        echo "证书更新完成"
    else
        echo "证书还有 $days_left 天有效期"
    fi
done

echo "SSL证书检查完成"
```

### 安全审计

#### 日志分析脚本

```bash
#!/bin/bash
# security-audit.sh

echo "开始安全审计..."

# 1. 分析访问日志中的可疑活动
echo "分析可疑访问..."
awk '$9 ~ /^4/ {print $1, $7, $9}' /var/log/nginx/access.log | \
    sort | uniq -c | sort -nr | head -20

# 2. 检查SQL注入尝试
echo "检查SQL注入尝试..."
grep -i "union\|select\|insert\|update\|delete" /var/log/nginx/access.log | \
    head -10

# 3. 检查XSS尝试
echo "检查XSS尝试..."
grep -i "script\|javascript\|onerror\|onload" /var/log/nginx/access.log | \
    head -10

# 4. 检查暴力破解尝试
echo "检查暴力破解尝试..."
grep "Failed password" /var/log/auth.log | \
    awk '{print $(NF-3)}' | sort | uniq -c | sort -nr | head -10

# 5. 检查异常大的请求
echo "检查异常大的请求..."
awk '$10 > 1000000 {print $1, $7, $10}' /var/log/nginx/access.log | \
    sort -k3 -nr | head -10

echo "安全审计完成"
```

## 备份和恢复

### 自动备份策略

#### 完整备份脚本

```bash
#!/bin/bash
# full-backup.sh

# 配置
BACKUP_DIR="/var/backups/laravel-links"
APP_DIR="/var/www/laravel-links-app"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# 创建备份目录
mkdir -p $BACKUP_DIR/{database,files,config}

echo "开始完整备份 - $DATE"

# 1. 数据库备份
echo "备份数据库..."
DB_NAME=$(grep "^DB_DATABASE=" $APP_DIR/.env | cut -d'=' -f2)
DB_USER=$(grep "^DB_USERNAME=" $APP_DIR/.env | cut -d'=' -f2)
DB_PASS=$(grep "^DB_PASSWORD=" $APP_DIR/.env | cut -d'=' -f2)

mysqldump -u$DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    $DB_NAME | gzip > $BACKUP_DIR/database/db_$DATE.sql.gz

# 2. 应用文件备份
echo "备份应用文件..."
tar -czf $BACKUP_DIR/files/app_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    -C $(dirname $APP_DIR) $(basename $APP_DIR)

# 3. 配置文件备份
echo "备份配置文件..."
tar -czf $BACKUP_DIR/config/config_$DATE.tar.gz \
    /etc/nginx/sites-available \
    /etc/php/8.1/fpm \
    /etc/mysql/mysql.conf.d \
    /etc/redis

# 4. 创建备份清单
echo "创建备份清单..."
cat > $BACKUP_DIR/backup_$DATE.info << EOF
备份时间: $DATE
数据库备份: database/db_$DATE.sql.gz
应用文件备份: files/app_$DATE.tar.gz
配置文件备份: config/config_$DATE.tar.gz
备份大小:
EOF

du -sh $BACKUP_DIR/database/db_$DATE.sql.gz >> $BACKUP_DIR/backup_$DATE.info
du -sh $BACKUP_DIR/files/app_$DATE.tar.gz >> $BACKUP_DIR/backup_$DATE.info
du -sh $BACKUP_DIR/config/config_$DATE.tar.gz >> $BACKUP_DIR/backup_$DATE.info

# 5. 清理旧备份
echo "清理旧备份..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.info" -mtime +$RETENTION_DAYS -delete

echo "备份完成 - $DATE"
```

### 恢复程序

#### 数据库恢复

```bash
#!/bin/bash
# restore-database.sh

if [ $# -ne 1 ]; then
    echo "使用方法: $0 <备份文件>"
    echo "示例: $0 /var/backups/laravel-links/database/db_20231201_120000.sql.gz"
    exit 1
fi

BACKUP_FILE=$1
APP_DIR="/var/www/laravel-links-app"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "错误: 备份文件不存在 - $BACKUP_FILE"
    exit 1
fi

echo "开始数据库恢复..."
echo "备份文件: $BACKUP_FILE"

# 读取数据库配置
DB_NAME=$(grep "^DB_DATABASE=" $APP_DIR/.env | cut -d'=' -f2)
DB_USER=$(grep "^DB_USERNAME=" $APP_DIR/.env | cut -d'=' -f2)
DB_PASS=$(grep "^DB_PASSWORD=" $APP_DIR/.env | cut -d'=' -f2)

echo "数据库: $DB_NAME"
echo "用户: $DB_USER"

# 确认恢复
read -p "确认要恢复数据库吗？这将覆盖现有数据 (y/N): " confirm
if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "恢复已取消"
    exit 0
fi

# 启用维护模式
cd $APP_DIR
php artisan down --message="数据库恢复中，请稍后访问"

# 恢复数据库
echo "恢复数据库..."
zcat $BACKUP_FILE | mysql -u$DB_USER -p$DB_PASS $DB_NAME

if [ $? -eq 0 ]; then
    echo "数据库恢复成功"
    
    # 运行迁移（如果需要）
    php artisan migrate --force
    
    # 清理缓存
    php artisan cache:clear
    php artisan config:cache
    
    # 关闭维护模式
    php artisan up
    
    echo "数据库恢复完成"
else
    echo "数据库恢复失败"
    php artisan up
    exit 1
fi
```

#### 应用文件恢复

```bash
#!/bin/bash
# restore-files.sh

if [ $# -ne 1 ]; then
    echo "使用方法: $0 <备份文件>"
    echo "示例: $0 /var/backups/laravel-links/files/app_20231201_120000.tar.gz"
    exit 1
fi

BACKUP_FILE=$1
APP_DIR="/var/www/laravel-links-app"
TEMP_DIR="/tmp/laravel-restore-$(date +%s)"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "错误: 备份文件不存在 - $BACKUP_FILE"
    exit 1
fi

echo "开始应用文件恢复..."
echo "备份文件: $BACKUP_FILE"

# 确认恢复
read -p "确认要恢复应用文件吗？这将覆盖现有文件 (y/N): " confirm
if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "恢复已取消"
    exit 0
fi

# 创建临时目录
mkdir -p $TEMP_DIR

# 解压备份文件到临时目录
echo "解压备份文件..."
tar -xzf $BACKUP_FILE -C $TEMP_DIR

# 启用维护模式
if [ -f "$APP_DIR/artisan" ]; then
    cd $APP_DIR
    php artisan down --message="文件恢复中，请稍后访问"
fi

# 备份当前.env文件
if [ -f "$APP_DIR/.env" ]; then
    cp $APP_DIR/.env /tmp/current.env
fi

# 恢复文件
echo "恢复应用文件..."
sudo rm -rf $APP_DIR
sudo mv $TEMP_DIR/$(basename $APP_DIR) $APP_DIR

# 恢复.env文件
if [ -f "/tmp/current.env" ]; then
    cp /tmp/current.env $APP_DIR/.env
    rm /tmp/current.env
fi

# 设置权限
sudo chown -R $USER:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# 安装依赖
echo "安装依赖..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 优化应用
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 重启服务
sudo systemctl restart php8.1-fpm nginx

# 关闭维护模式
php artisan up

# 清理临时文件
rm -rf $TEMP_DIR

echo "应用文件恢复完成"
```

## 故障处理

### 常见故障诊断

#### 应用无法访问

```bash
#!/bin/bash
# diagnose-app-down.sh

echo "诊断应用无法访问问题..."

# 1. 检查Nginx状态
echo "=== Nginx状态 ==="
sudo systemctl status nginx --no-pager
echo ""

# 2. 检查PHP-FPM状态
echo "=== PHP-FPM状态 ==="
sudo systemctl status php8.1-fpm --no-pager
echo ""

# 3. 检查端口监听
echo "=== 端口监听状态 ==="
sudo netstat -tlnp | grep -E ':80|:443|:9000'
echo ""

# 4. 检查Nginx错误日志
echo "=== Nginx错误日志（最近10行）==="
sudo tail -10 /var/log/nginx/error.log
echo ""

# 5. 检查PHP-FPM错误日志
echo "=== PHP-FPM错误日志（最近10行）==="
sudo tail -10 /var/log/php8.1-fpm.log
echo ""

# 6. 测试Nginx配置
echo "=== Nginx配置测试 ==="
sudo nginx -t
echo ""

# 7. 检查磁盘空间
echo "=== 磁盘空间 ==="
df -h
echo ""

# 8. 检查内存使用
echo "=== 内存使用 ==="
free -h
echo ""

echo "诊断完成"
```

#### 数据库连接问题

```bash
#!/bin/bash
# diagnose-database.sh

APP_DIR="/var/www/laravel-links-app"

echo "诊断数据库连接问题..."

# 1. 检查MySQL服务状态
echo "=== MySQL服务状态 ==="
sudo systemctl status mysql --no-pager
echo ""

# 2. 检查MySQL进程
echo "=== MySQL进程 ==="
ps aux | grep mysql
echo ""

# 3. 检查MySQL端口
echo "=== MySQL端口监听 ==="
sudo netstat -tlnp | grep :3306
echo ""

# 4. 读取数据库配置
if [ -f "$APP_DIR/.env" ]; then
    echo "=== 数据库配置 ==="
    grep "^DB_" $APP_DIR/.env
    echo ""
    
    # 5. 测试数据库连接
    DB_HOST=$(grep "^DB_HOST=" $APP_DIR/.env | cut -d'=' -f2)
    DB_PORT=$(grep "^DB_PORT=" $APP_DIR/.env | cut -d'=' -f2)
    DB_DATABASE=$(grep "^DB_DATABASE=" $APP_DIR/.env | cut -d'=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" $APP_DIR/.env | cut -d'=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" $APP_DIR/.env | cut -d'=' -f2)
    
    echo "=== 数据库连接测试 ==="
    mysql -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD -e "SELECT 1" 2>&1
    echo ""
fi

# 6. 检查MySQL错误日志
echo "=== MySQL错误日志（最近10行）==="
sudo tail -10 /var/log/mysql/error.log
echo ""

# 7. 检查MySQL慢查询日志
if [ -f "/var/log/mysql/mysql-slow.log" ]; then
    echo "=== MySQL慢查询日志（最近5行）==="
    sudo tail -5 /var/log/mysql/mysql-slow.log
    echo ""
fi

echo "诊断完成"
```

#### 性能问题诊断

```bash
#!/bin/bash
# diagnose-performance.sh

echo "诊断性能问题..."

# 1. 系统负载
echo "=== 系统负载 ==="
uptime
echo ""

# 2. CPU使用情况
echo "=== CPU使用情况 ==="
top -bn1 | head -20
echo ""

# 3. 内存使用情况
echo "=== 内存使用情况 ==="
free -h
echo "=== 内存使用排行 ==="
ps aux --sort=-%mem | head -10
echo ""

# 4. 磁盘I/O
echo "=== 磁盘I/O ==="
iostat -x 1 3
echo ""

# 5. 网络连接
echo "=== 网络连接统计 ==="
ss -s
echo "=== 活跃连接 ==="
ss -tuln
echo ""

# 6. PHP-FPM进程状态
echo "=== PHP-FPM进程状态 ==="
ps aux | grep php-fpm | wc -l
echo "PHP-FPM进程数量: $(ps aux | grep php-fpm | grep -v grep | wc -l)"
echo ""

# 7. 数据库连接数
echo "=== 数据库连接数 ==="
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
mysql -u root -p -e "SHOW PROCESSLIST;"
echo ""

# 8. 检查慢查询
echo "=== 慢查询统计 ==="
mysql -u root -p -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';"
echo ""

echo "诊断完成"
```

### 应急响应流程

#### 服务中断响应

1. **立即响应（0-5分钟）**
   ```bash
   # 快速检查服务状态
   sudo systemctl status nginx php8.1-fpm mysql redis-server
   
   # 重启关键服务
   sudo systemctl restart nginx php8.1-fpm
   
   # 检查应用健康状态
   curl -I http://localhost/health
   ```

2. **问题诊断（5-15分钟）**
   ```bash
   # 运行诊断脚本
   ./diagnose-app-down.sh
   
   # 检查错误日志
   tail -50 /var/www/laravel-links-app/storage/logs/laravel.log
   tail -50 /var/log/nginx/error.log
   
   # 检查系统资源
   top
   df -h
   free -h
   ```

3. **问题修复（15-30分钟）**
   ```bash
   # 根据诊断结果执行相应修复
   # 如果是配置问题，恢复配置
   # 如果是资源问题，清理或扩容
   # 如果是代码问题，回滚版本
   ```

4. **服务恢复验证（30-45分钟）**
   ```bash
   # 验证服务恢复
   curl -I https://your-domain.com
   
   # 运行健康检查
   php artisan health:check
   
   # 监控关键指标
   ./performance-monitor.sh
   ```

#### 数据丢失响应

1. **立即停止服务**
   ```bash
   # 启用维护模式
   php artisan down --message="系统维护中"
   
   # 停止写入操作
   sudo systemctl stop nginx
   ```

2. **评估损失范围**
   ```bash
   # 检查数据库状态
   mysql -u root -p -e "SHOW TABLES;" laravel_links
   
   # 检查最近的备份
   ls -la /var/backups/laravel-links/
   ```

3. **执行数据恢复**
   ```bash
   # 恢复最近的备份
   ./restore-database.sh /var/backups/laravel-links/database/latest.sql.gz
   ```

4. **验证数据完整性**
   ```bash
   # 检查数据一致性
   php artisan db:check
   
   # 验证关键功能
   php artisan test --group=critical
   ```

## 运维工具

### 监控脚本集合

#### 综合监控脚本

```bash
#!/bin/bash
# monitor-all.sh

echo "Laravel Links App 综合监控报告"
echo "生成时间: $(date)"
echo "========================================"

# 系统信息
echo "\n=== 系统信息 ==="
echo "主机名: $(hostname)"
echo "系统版本: $(lsb_release -d | cut -f2)"
echo "内核版本: $(uname -r)"
echo "运行时间: $(uptime -p)"

# 系统资源
echo "\n=== 系统资源 ==="
echo "CPU使用率: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "内存使用率: $(free | awk 'NR==2{printf "%.2f%%", $3*100/$2}')" 
echo "磁盘使用率: $(df / | awk 'NR==2{print $5}')"
echo "平均负载: $(uptime | awk -F'load average:' '{print $2}')"

# 服务状态
echo "\n=== 服务状态 ==="
services=("nginx" "php8.1-fpm" "mysql" "redis-server")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        echo "$service: ✓ 运行中"
    else
        echo "$service: ✗ 未运行"
    fi
done

# 应用状态
echo "\n=== 应用状态 ==="
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)
if [ $response -eq 200 ]; then
    echo "应用健康检查: ✓ 正常"
else
    echo "应用健康检查: ✗ 异常 (HTTP $response)"
fi

# 数据库状态
echo "\n=== 数据库状态 ==="nif mysql -u root -p -e "SELECT 1" &> /dev/null; then
    echo "数据库连接: ✓ 正常"
    connections=$(mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';" | awk 'NR==2{print $2}')
    echo "当前连接数: $connections"
else
    echo "数据库连接: ✗ 异常"
fi

# Redis状态
echo "\n=== Redis状态 ==="
if redis-cli ping &> /dev/null; then
    echo "Redis连接: ✓ 正常"
    memory=$(redis-cli info memory | grep used_memory_human | cut -d: -f2 | tr -d '\r')
    echo "内存使用: $memory"
else
    echo "Redis连接: ✗ 异常"
fi

# 最近错误
echo "\n=== 最近错误 ==="
echo "应用错误:"
tail -5 /var/www/laravel-links-app/storage/logs/laravel.log | grep ERROR || echo "无错误"
echo "\nNginx错误:"
tail -5 /var/log/nginx/error.log || echo "无错误"

echo "\n========================================"
echo "监控报告完成"
```

#### 性能基准测试

```bash
#!/bin/bash
# benchmark.sh

APP_URL="http://localhost"
TEST_DURATION=60
CONCURRENCY=10

echo "Laravel Links App 性能基准测试"
echo "测试URL: $APP_URL"
echo "测试时长: ${TEST_DURATION}秒"
echo "并发数: $CONCURRENCY"
echo "========================================"

# 安装ab工具（如果未安装）
if ! command -v ab &> /dev/null; then
    echo "安装Apache Bench工具..."
    sudo apt install -y apache2-utils
fi

# 测试主页
echo "\n=== 主页性能测试 ==="
ab -t $TEST_DURATION -c $CONCURRENCY $APP_URL/ | grep -E "Requests per second|Time per request|Transfer rate"

# 测试API端点
echo "\n=== API性能测试 ==="
ab -t $TEST_DURATION -c $CONCURRENCY $APP_URL/api/links | grep -E "Requests per second|Time per request|Transfer rate"

# 测试健康检查端点
echo "\n=== 健康检查性能测试 ==="
ab -t $TEST_DURATION -c $CONCURRENCY $APP_URL/health | grep -E "Requests per second|Time per request|Transfer rate"

echo "\n========================================"
echo "性能测试完成"
```

### 自动化运维脚本

#### 一键运维脚本

```bash
#!/bin/bash
# ops-toolkit.sh

show_menu() {
    echo "Laravel Links App 运维工具箱"
    echo "========================================"
    echo "1. 系统监控"
    echo "2. 性能测试"
    echo "3. 安全检查"
    echo "4. 备份数据"
    echo "5. 恢复数据"
    echo "6. 更新应用"
    echo "7. 重启服务"
    echo "8. 查看日志"
    echo "9. 清理缓存"
    echo "0. 退出"
    echo "========================================"
    read -p "请选择操作 (0-9): " choice
}

while true; do
    show_menu
    
    case $choice in
        1)
            echo "执行系统监控..."
            ./monitor-all.sh
            ;;
        2)
            echo "执行性能测试..."
            ./benchmark.sh
            ;;
        3)
            echo "执行安全检查..."
            ./security-check.sh
            ;;
        4)
            echo "执行数据备份..."
            ./full-backup.sh
            ;;
        5)
            echo "数据恢复功能"
            echo "请手动运行: ./restore-database.sh <备份文件>"
            ;;
        6)
            echo "更新应用..."
            ./quick-deploy.sh production update
            ;;
        7)
            echo "重启服务..."
            sudo systemctl restart nginx php8.1-fpm mysql redis-server
            echo "服务重启完成"
            ;;
        8)
            echo "查看日志选项:"
            echo "1) 应用日志  2) Nginx日志  3) MySQL日志"
            read -p "选择日志类型: " log_choice
            case $log_choice in
                1) tail -f /var/www/laravel-links-app/storage/logs/laravel.log ;;
                2) tail -f /var/log/nginx/error.log ;;
                3) tail -f /var/log/mysql/error.log ;;
            esac
            ;;
        9)
            echo "清理缓存..."
            cd /var/www/laravel-links-app
            php artisan cache:clear
            php artisan config:clear
            php artisan route:clear
            php artisan view:clear
            echo "缓存清理完成"
            ;;
        0)
            echo "退出运维工具箱"
            exit 0
            ;;
        *)
            echo "无效选择，请重新输入"
            ;;
    esac
    
    echo ""
    read -p "按回车键继续..."
    clear
done
```

## 总结

本运维手册提供了Laravel链接聚合应用的完整运维指南，涵盖：

- ✅ 日常运维任务和检查清单
- ✅ 监控和告警系统配置
- ✅ 性能调优和优化策略
- ✅ 安全管理和访问控制
- ✅ 备份和恢复程序
- ✅ 故障诊断和应急响应
- ✅ 自动化运维工具

### 运维最佳实践

1. **预防为主**: 建立完善的监控和告警机制
2. **自动化优先**: 使用脚本自动化重复性任务
3. **文档化**: 记录所有操作和变更
4. **定期演练**: 定期进行故障恢复演练
5. **持续改进**: 根据实际情况优化运维流程

### 紧急联系信息

- **技术负责人**: [联系方式]
- **系统管理员**: [联系方式]
- **服务提供商**: [联系方式]
- **应急响应**: [联系方式]

遵循本手册的指导，可以确保Laravel链接聚合应用的稳定运行和高效维护。