<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 监控配置
    |--------------------------------------------------------------------------
    |
    | 系统性能监控和安全审计配置
    |
    */

    // 性能监控
    'performance' => [
        'enabled' => env('MONITORING_ENABLED', true),
        
        // 响应时间监控
        'response_time' => [
            'enabled' => true,
            'slow_threshold' => 1000, // 毫秒
            'log_slow_requests' => true,
        ],
        
        // 内存使用监控
        'memory' => [
            'enabled' => true,
            'threshold' => 128, // MB
            'log_high_usage' => true,
        ],
        
        // 数据库查询监控
        'database' => [
            'enabled' => true,
            'slow_query_threshold' => 1000, // 毫秒
            'log_slow_queries' => true,
            'log_duplicate_queries' => true,
        ],
        
        // 缓存监控
        'cache' => [
            'enabled' => true,
            'hit_rate_threshold' => 0.8, // 80%
            'log_low_hit_rate' => true,
        ],
    ],
    
    // 安全监控
    'security' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        
        // 登录监控
        'login' => [
            'track_failed_attempts' => true,
            'max_failed_attempts' => 5,
            'lockout_duration' => 15, // 分钟
            'track_successful_logins' => true,
        ],
        
        // IP监控
        'ip_monitoring' => [
            'enabled' => true,
            'whitelist' => [
                // '127.0.0.1',
                // '::1',
            ],
            'blacklist' => [
                // 恶意IP列表
            ],
            'auto_block_suspicious_ips' => true,
            'suspicious_request_threshold' => 100, // 每小时
        ],
        
        // 文件监控
        'file_monitoring' => [
            'enabled' => true,
            'watch_directories' => [
                base_path('app'),
                base_path('config'),
                base_path('routes'),
            ],
            'alert_on_changes' => true,
        ],
    ],
    
    // 业务监控
    'business' => [
        'enabled' => true,
        
        // 用户活动监控
        'user_activity' => [
            'track_page_views' => true,
            'track_api_calls' => true,
            'track_feature_usage' => true,
        ],
        
        // 错误监控
        'error_tracking' => [
            'enabled' => true,
            'track_404_errors' => true,
            'track_500_errors' => true,
            'alert_threshold' => 10, // 每小时错误数
        ],
    ],
    
    // 健康检查
    'health_checks' => [
        'enabled' => true,
        'interval' => 60, // 秒
        
        'checks' => [
            // 数据库连接检查
            'database' => [
                'enabled' => true,
                'timeout' => 5, // 秒
            ],
            
            // Redis连接检查
            'redis' => [
                'enabled' => true,
                'timeout' => 3, // 秒
            ],
            
            // 磁盘空间检查
            'disk_space' => [
                'enabled' => true,
                'threshold' => 90, // 百分比
            ],
            
            // 队列检查
            'queue' => [
                'enabled' => true,
                'max_pending_jobs' => 1000,
            ],
        ],
    ],
    
    // 通知配置
    'notifications' => [
        'enabled' => true,
        
        // 邮件通知
        'email' => [
            'enabled' => env('MONITORING_EMAIL_ENABLED', false),
            'recipients' => [
                env('ADMIN_EMAIL', 'admin@example.com'),
            ],
            'severity_threshold' => 'warning', // debug, info, warning, error, critical
        ],
        
        // Slack通知
        'slack' => [
            'enabled' => env('MONITORING_SLACK_ENABLED', false),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'channel' => env('SLACK_CHANNEL', '#alerts'),
            'severity_threshold' => 'error',
        ],
        
        // 短信通知
        'sms' => [
            'enabled' => env('MONITORING_SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'aliyun'),
            'recipients' => [
                // '+86138xxxxxxxx',
            ],
            'severity_threshold' => 'critical',
        ],
    ],
    
    // 数据保留
    'data_retention' => [
        'performance_logs' => 30, // 天
        'security_logs' => 90, // 天
        'business_logs' => 365, // 天
        'health_check_logs' => 7, // 天
    ],
    
    // 第三方集成
    'integrations' => [
        // Sentry错误追踪
        'sentry' => [
            'enabled' => env('SENTRY_ENABLED', false),
            'dsn' => env('SENTRY_LARAVEL_DSN'),
            'environment' => env('APP_ENV', 'production'),
        ],
        
        // New Relic性能监控
        'newrelic' => [
            'enabled' => env('NEWRELIC_ENABLED', false),
            'license_key' => env('NEWRELIC_LICENSE_KEY'),
            'app_name' => env('NEWRELIC_APP_NAME', 'Laravel Links App'),
        ],
        
        // Elasticsearch日志存储
        'elasticsearch' => [
            'enabled' => env('ELASTICSEARCH_ENABLED', false),
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'localhost:9200'),
            ],
            'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'laravel-links'),
        ],
        
        // Prometheus指标
        'prometheus' => [
            'enabled' => env('PROMETHEUS_ENABLED', false),
            'namespace' => 'laravel_links',
            'metrics_path' => '/metrics',
        ],
    ],
];