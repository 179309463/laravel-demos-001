<?php

/**
 * Laravel 链接聚合应用 - 安全配置
 * 
 * 此配置文件包含应用的安全相关设置
 */

return [

    /*
    |--------------------------------------------------------------------------
    | 安全头部配置
    |--------------------------------------------------------------------------
    |
    | 配置各种安全相关的 HTTP 头部
    |
    */
    'headers' => [
        // 内容安全策略
        'content_security_policy' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            'font-src' => "'self' https://fonts.gstatic.com",
            'img-src' => "'self' data: https: blob:",
            'connect-src' => "'self' https:",
            'frame-ancestors' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self'",
        ],
        
        // HTTP 严格传输安全
        'strict_transport_security' => [
            'max_age' => 31536000, // 1年
            'include_subdomains' => true,
            'preload' => true,
        ],
        
        // 其他安全头部
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => [
            'camera' => '()',
            'microphone' => '()',
            'geolocation' => '()',
            'payment' => '()',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 速率限制配置
    |--------------------------------------------------------------------------
    |
    | 配置各种操作的速率限制
    |
    */
    'rate_limiting' => [
        // API 请求限制
        'api' => [
            'requests' => 60,
            'per_minute' => 1,
        ],
        
        // 登录尝试限制
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        
        // 注册限制
        'register' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        
        // 密码重置限制
        'password_reset' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        
        // 链接提交限制
        'link_submission' => [
            'max_attempts' => 10,
            'decay_minutes' => 60,
        ],
        
        // 评论限制
        'comment' => [
            'max_attempts' => 20,
            'decay_minutes' => 60,
        ],
        
        // 投票限制
        'vote' => [
            'max_attempts' => 100,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 输入验证和过滤
    |--------------------------------------------------------------------------
    |
    | 配置输入数据的验证和过滤规则
    |
    */
    'input_filtering' => [
        // 允许的 HTML 标签（用于富文本内容）
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'blockquote', 'code', 'pre',
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'title'],
        ],
        
        // 禁止的文件扩展名
        'forbidden_extensions' => [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8',
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
            'jar', 'sh', 'py', 'pl', 'rb',
        ],
        
        // 允许的图片类型
        'allowed_image_types' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        ],
        
        // 最大文件大小（字节）
        'max_file_size' => 5 * 1024 * 1024, // 5MB
    ],

    /*
    |--------------------------------------------------------------------------
    | 会话安全配置
    |--------------------------------------------------------------------------
    |
    | 配置会话相关的安全设置
    |
    */
    'session' => [
        // 会话超时时间（分钟）
        'timeout' => 120,
        
        // 强制 HTTPS
        'secure' => env('APP_ENV') === 'production',
        
        // HttpOnly Cookie
        'http_only' => true,
        
        // SameSite 设置
        'same_site' => 'strict',
        
        // 会话轮换
        'regenerate_on_login' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 密码安全配置
    |--------------------------------------------------------------------------
    |
    | 配置密码相关的安全要求
    |
    */
    'password' => [
        // 最小长度
        'min_length' => 8,
        
        // 最大长度
        'max_length' => 255,
        
        // 复杂度要求
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        
        // 禁止常见密码
        'forbid_common_passwords' => true,
        
        // 密码历史记录
        'remember_previous' => 5,
        
        // 密码过期时间（天）
        'expires_after_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | 双因素认证配置
    |--------------------------------------------------------------------------
    |
    | 配置双因素认证相关设置
    |
    */
    'two_factor' => [
        // 是否启用双因素认证
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        
        // TOTP 设置
        'totp' => [
            'issuer' => env('APP_NAME', 'Laravel Links'),
            'digits' => 6,
            'period' => 30,
            'algorithm' => 'sha1',
        ],
        
        // 恢复代码数量
        'recovery_codes_count' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP 白名单和黑名单
    |--------------------------------------------------------------------------
    |
    | 配置 IP 访问控制
    |
    */
    'ip_filtering' => [
        // 管理员 IP 白名单
        'admin_whitelist' => [
            // '127.0.0.1',
            // '192.168.1.0/24',
        ],
        
        // IP 黑名单
        'blacklist' => [
            // 在这里添加需要封禁的 IP
        ],
        
        // 是否启用地理位置过滤
        'geo_filtering' => [
            'enabled' => false,
            'allowed_countries' => ['CN', 'US', 'GB'], // ISO 国家代码
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 审计日志配置
    |--------------------------------------------------------------------------
    |
    | 配置安全审计日志
    |
    */
    'audit_log' => [
        // 是否启用审计日志
        'enabled' => true,
        
        // 需要记录的事件
        'events' => [
            'user_login',
            'user_logout',
            'user_register',
            'password_change',
            'password_reset',
            'admin_action',
            'failed_login',
            'suspicious_activity',
        ],
        
        // 日志保留时间（天）
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | 恶意内容检测
    |--------------------------------------------------------------------------
    |
    | 配置恶意内容检测和过滤
    |
    */
    'content_filtering' => [
        // 是否启用内容过滤
        'enabled' => true,
        
        // 垃圾邮件检测
        'spam_detection' => [
            'enabled' => true,
            'threshold' => 0.8, // 0-1 之间，越高越严格
        ],
        
        // 恶意链接检测
        'malicious_url_detection' => [
            'enabled' => true,
            'check_against_blacklist' => true,
        ],
        
        // 内容审核
        'content_moderation' => [
            'auto_moderate' => true,
            'require_approval' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 备份和恢复配置
    |--------------------------------------------------------------------------
    |
    | 配置数据备份和恢复策略
    |
    */
    'backup' => [
        // 自动备份间隔（小时）
        'auto_backup_interval' => 24,
        
        // 备份保留时间（天）
        'retention_days' => 30,
        
        // 备份加密
        'encryption' => [
            'enabled' => true,
            'algorithm' => 'AES-256-CBC',
        ],
        
        // 远程备份
        'remote_backup' => [
            'enabled' => env('BACKUP_REMOTE_ENABLED', false),
            'driver' => env('BACKUP_REMOTE_DRIVER', 's3'),
        ],
    ],

];