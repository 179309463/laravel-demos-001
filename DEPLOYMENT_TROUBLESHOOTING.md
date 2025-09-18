# Laravel 链接聚合应用 - 部署故障排除指南

本指南帮助您诊断和解决部署过程中可能遇到的常见问题。

## 🚨 常见错误类型

### 1. TypeError: fetch failed

**症状**: 部署时出现 `TypeError: fetch failed` 错误

**可能原因**:
- 网络连接问题
- DNS 解析失败
- 防火墙阻止连接
- 依赖包仓库不可访问

**解决方案**:
```bash
# 检查网络连接
curl -I https://packagist.org
curl -I https://registry.npmjs.org

# 如果连接失败，尝试使用代理或更换镜像源
npm config set registry https://registry.npmmirror.com/
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### 2. APP_KEY 配置错误

**症状**: 应用无法启动，提示 APP_KEY 未设置

**解决方案**:
```bash
# 生成新的 APP_KEY
php artisan key:generate --show

# 将生成的密钥添加到 .env.production 文件
APP_KEY=base64:生成的密钥
```

### 3. Vite 构建失败

**症状**: `vite: command not found` 或前端资源构建失败

**解决方案**:
```bash
# 确保安装了所有依赖（包括开发依赖）
npm ci

# 手动运行构建
npm run build:production

# 检查 vite.config.js 配置是否正确
```

### 4. Composer 依赖安装失败

**症状**: Composer 无法安装依赖包

**解决方案**:
```bash
# 清除 Composer 缓存
composer clear-cache

# 重新安装依赖
composer install --no-dev --optimize-autoloader

# 如果仍然失败，检查 composer.json 语法
composer validate
```

### 5. 权限问题

**症状**: 无法写入缓存或日志文件

**解决方案**:
```bash
# 设置正确的文件权限
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🔧 部署前检查清单

### 环境配置
- [ ] `.env.production` 文件存在且配置正确
- [ ] `APP_KEY` 已设置为有效值（非占位符）
- [ ] 数据库连接信息正确
- [ ] `APP_URL` 设置为正确的域名
- [ ] `APP_DEBUG` 设置为 `false`

### 依赖和构建
- [ ] `composer.json` 和 `package.json` 文件存在
- [ ] 所有必需的 PHP 扩展已安装
- [ ] Node.js 和 npm 版本兼容
- [ ] Vite 配置文件正确

### 网络和权限
- [ ] 服务器可以访问外部包仓库
- [ ] 文件权限设置正确
- [ ] 防火墙配置允许必要的连接

## 🛠️ 调试工具和命令

### 检查系统信息
```bash
# 检查 PHP 版本和扩展
php --version
php -m

# 检查 Node.js 和 npm 版本
node --version
npm --version

# 检查 Composer 版本
composer --version
```

### 测试网络连接
```bash
# 测试包仓库连接
curl -I https://packagist.org
curl -I https://registry.npmjs.org

# 测试 DNS 解析
nslookup packagist.org
nslookup registry.npmjs.org
```

### Laravel 应用调试
```bash
# 清除所有缓存
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 检查配置
php artisan config:show

# 检查路由
php artisan route:list
```

## 📋 部署脚本使用说明

### build.sh - Vercel 构建脚本
```bash
# 使脚本可执行
chmod +x build.sh

# 运行构建
./build.sh
```

**脚本功能**:
- 检查网络连接
- 验证 PHP 和 Node.js 版本
- 安装 Composer 和 npm 依赖
- 构建前端资源
- 优化 Laravel 应用
- 设置文件权限

### deploy.sh - 生产环境部署脚本
```bash
# 使脚本可执行
chmod +x deploy.sh

# 运行部署
./deploy.sh
```

## 🆘 紧急恢复步骤

如果部署完全失败，按以下步骤恢复：

1. **回滚到上一个工作版本**
```bash
git checkout HEAD~1
```

2. **清除所有缓存和构建文件**
```bash
rm -rf node_modules
rm -rf vendor
rm -rf public/build
npm cache clean --force
composer clear-cache
```

3. **重新安装依赖**
```bash
composer install
npm install
```

4. **重新构建**
```bash
npm run build:production
php artisan config:cache
```

## 📞 获取帮助

如果问题仍然存在，请：

1. 检查服务器日志文件
2. 启用 Laravel 调试模式（仅限开发环境）
3. 查看浏览器开发者工具的网络和控制台选项卡
4. 联系系统管理员或开发团队

---

**注意**: 在生产环境中，请确保 `APP_DEBUG=false` 以避免泄露敏感信息。