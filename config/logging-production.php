<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    /*
    |--------------------------------------------------------------------------
    | 生产环境日志配置
    |--------------------------------------------------------------------------
    |
    | 生产环境专用的日志配置，包含性能优化和安全考虑
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'security', 'performance'],
            'ignore_exceptions' => false,
        ],

        // 应用日志 - 按日期分割
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 30,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => false,
            ],
        ],

        // 安全日志
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'warning',
            'days' => 90,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 性能日志
        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => 'info',
            'days' => 14,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 错误日志
        'error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/error.log'),
            'level' => 'error',
            'days' => 60,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 审计日志
        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => 365,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 数据库查询日志
        'database' => [
            'driver' => 'daily',
            'path' => storage_path('logs/database.log'),
            'level' => 'debug',
            'days' => 7,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // API访问日志
        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 队列日志
        'queue' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queue.log'),
            'level' => 'info',
            'days' => 14,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 缓存日志
        'cache' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cache.log'),
            'level' => 'info',
            'days' => 7,
            'replace_placeholders' => true,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
        ],

        // 单文件日志
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // Syslog
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        // 系统错误日志
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // 空日志（用于禁用某些日志）
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        // 紧急情况日志
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // Slack通知
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        // 邮件通知
        'mail' => [
            'driver' => 'mail',
            'to' => [
                [
                    'address' => env('LOG_MAIL_TO', 'admin@example.com'),
                    'name' => 'Admin',
                ],
            ],
            'subject' => 'Laravel Application Error',
            'level' => 'error',
        ],

        // Elasticsearch
        'elasticsearch' => [
            'driver' => 'custom',
            'via' => \App\Logging\ElasticsearchLogger::class,
            'level' => env('LOG_LEVEL', 'debug'),
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'localhost:9200'),
            ],
            'index' => env('ELASTICSEARCH_LOG_INDEX', 'laravel-logs'),
        ],

        // 远程Syslog
        'remote_syslog' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('SYSLOG_HOST', 'localhost'),
                'port' => env('SYSLOG_PORT', 514),
                'facility' => LOG_USER,
            ],
            'processors' => [
                PsrLogMessageProcessor::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志上下文
    |--------------------------------------------------------------------------
    |
    | 自动添加到所有日志记录的上下文信息
    |
    */
    'context' => [
        'app_name' => env('APP_NAME', 'Laravel'),
        'app_env' => env('APP_ENV', 'production'),
        'app_version' => env('APP_VERSION', '1.0.0'),
        'server_name' => gethostname(),
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志处理器
    |--------------------------------------------------------------------------
    |
    | 全局日志处理器配置
    |
    */
    'processors' => [
        // 添加请求ID
        \App\Logging\RequestIdProcessor::class,
        // 添加用户信息
        \App\Logging\UserContextProcessor::class,
        // 添加性能信息
        \App\Logging\PerformanceProcessor::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志过滤器
    |--------------------------------------------------------------------------
    |
    | 敏感信息过滤配置
    |
    */
    'filters' => [
        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'phone',
            'email',
        ],
        'replacement' => '[FILTERED]',
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志轮转
    |--------------------------------------------------------------------------
    |
    | 日志文件轮转和清理配置
    |
    */
    'rotation' => [
        'enabled' => true,
        'max_files' => 30,
        'compress' => true,
        'cleanup_command' => 'find ' . storage_path('logs') . ' -name "*.log" -mtime +30 -delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | 性能配置
    |--------------------------------------------------------------------------
    |
    | 日志性能优化配置
    |
    */
    'performance' => [
        'buffer_size' => 1000,
        'flush_interval' => 60, // 秒
        'async_logging' => true,
        'queue_connection' => 'redis',
    ],
];