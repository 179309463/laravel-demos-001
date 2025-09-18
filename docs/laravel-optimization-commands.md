# Laravel 生产环境优化命令详解

本文档详细说明了 Laravel 应用在生产环境中使用的各种优化命令及其作用。

## 🧹 清理命令

### 清理所有缓存
```bash
# 清理应用缓存
php artisan cache:clear

# 清理配置缓存
php artisan config:clear

# 清理路由缓存
php artisan route:clear

# 清理视图缓存
php artisan view:clear

# 清理事件缓存
php artisan event:clear

# 清理编译的类文件
rm -rf bootstrap/cache/*.php
```

## ⚡ 生产环境优化命令

### 1. 配置缓存
```bash
php artisan config:cache
```
**作用**: 将所有配置文件合并为单个缓存文件，提高配置加载速度。
**注意**: 生产环境必须执行，开发环境不建议使用。

### 2. 路由缓存
```bash
php artisan route:cache
```
**作用**: 将所有路由信息缓存到单个文件中，大幅提升路由解析速度。
**注意**: 只适用于不使用闭包路由的应用。

### 3. 视图缓存
```bash
php artisan view:cache
```
**作用**: 预编译所有 Blade 模板，减少首次访问时的编译时间。
**效果**: 提升页面首次加载速度。

### 4. 事件缓存
```bash
php artisan event:cache
```
**作用**: 缓存事件监听器映射，提高事件系统性能。
**适用**: Laravel 8.0+ 版本。

### 5. Composer 自动加载优化
```bash
# 基础优化
composer dump-autoload --optimize

# 生产环境优化（推荐）
composer dump-autoload --optimize --classmap-authoritative

# 安装时优化
composer install --optimize-autoloader --no-dev
```
**作用**: 优化类自动加载性能，减少文件系统查找。

## 📦 依赖管理

### 生产环境依赖安装
```bash
# 安装生产环境依赖（排除开发依赖）
composer install --no-dev --optimize-autoloader --no-interaction

# 前端依赖安装
npm ci --only=production
```

## 🏗️ 前端资源构建

### Vite 生产构建
```bash
# 构建生产版本
npm run build

# 检查构建结果
ls -la public/build/
```

## 🔗 存储链接

### 创建存储符号链接
```bash
php artisan storage:link
```
**作用**: 创建 `public/storage` 到 `storage/app/public` 的符号链接。
**用途**: 使上传的文件可以通过 Web 访问。

## 🗄️ 数据库相关

### 数据库迁移
```bash
# 生产环境迁移（强制执行）
php artisan migrate --force

# 回滚迁移
php artisan migrate:rollback

# 重置并重新迁移
php artisan migrate:fresh --force
```

### 数据库填充
```bash
# 运行数据填充
php artisan db:seed --force

# 运行特定填充器
php artisan db:seed --class=UserSeeder --force
```

## 🔒 文件权限设置

### 设置正确的文件权限
```bash
# 设置存储目录权限
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 设置所有者（Web 服务器用户）
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# 设置 .env 文件权限（安全）
chmod 600 .env
```

## 🔍 性能检查命令

### 检查优化状态
```bash
# 检查配置缓存状态
php artisan config:show

# 检查路由缓存状态
php artisan route:list

# 检查应用状态
php artisan about

# 检查队列状态
php artisan queue:work --once
```

## 📊 性能监控

### 启用 OPcache（推荐）
在 `php.ini` 中配置：
```ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=12
opcache.max_accelerated_files=60000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 队列处理
```bash
# 启动队列工作进程
php artisan queue:work --daemon

# 重启队列工作进程
php artisan queue:restart

# 监控队列
php artisan queue:monitor
```

## ⚠️ 重要注意事项

1. **开发环境**: 不要在开发环境使用缓存命令，会影响开发体验
2. **部署顺序**: 先清理缓存，再执行优化命令
3. **配置更新**: 修改配置后需要重新执行 `config:cache`
4. **路由更新**: 修改路由后需要重新执行 `route:cache`
5. **权限问题**: 确保 Web 服务器用户有正确的文件权限
6. **环境变量**: 生产环境必须设置 `APP_ENV=production` 和 `APP_DEBUG=false`

## 🚀 完整的部署优化流程

```bash
# 1. 清理
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. 安装依赖
composer install --no-dev --optimize-autoloader
npm ci --only=production

# 3. 构建资源
npm run build

# 4. 数据库
php artisan migrate --force

# 5. 优化缓存
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. 存储链接
php artisan storage:link

# 7. 权限设置
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

这个流程确保了应用在生产环境中的最佳性能表现。