<?php

/**
 * Laravel 链接聚合应用 - 性能优化配置
 * 
 * 此配置文件包含应用的性能优化相关设置
 */

return [

    /*
    |--------------------------------------------------------------------------
    | 缓存优化配置
    |--------------------------------------------------------------------------
    |
    | 配置各种缓存策略和优化设置
    |
    */
    'cache' => [
        // 页面缓存设置
        'page_cache' => [
            'enabled' => env('PAGE_CACHE_ENABLED', true),
            'ttl' => env('PAGE_CACHE_TTL', 3600), // 1小时
            'exclude_routes' => [
                'admin.*',
                'user.profile',
                'api.*',
            ],
        ],
        
        // 数据库查询缓存
        'query_cache' => [
            'enabled' => env('QUERY_CACHE_ENABLED', true),
            'ttl' => env('QUERY_CACHE_TTL', 1800), // 30分钟
            'tags' => [
                'links' => 3600,
                'users' => 7200,
                'comments' => 1800,
                'votes' => 900,
            ],
        ],
        
        // 视图缓存
        'view_cache' => [
            'enabled' => env('VIEW_CACHE_ENABLED', true),
            'compile_path' => storage_path('framework/views'),
        ],
        
        // 路由缓存
        'route_cache' => [
            'enabled' => env('ROUTE_CACHE_ENABLED', true),
            'path' => base_path('bootstrap/cache/routes-v7.php'),
        ],
        
        // 配置缓存
        'config_cache' => [
            'enabled' => env('CONFIG_CACHE_ENABLED', true),
            'path' => base_path('bootstrap/cache/config.php'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 数据库优化配置
    |--------------------------------------------------------------------------
    |
    | 配置数据库性能优化设置
    |
    */
    'database' => [
        // 连接池设置
        'connection_pool' => [
            'min_connections' => env('DB_MIN_CONNECTIONS', 5),
            'max_connections' => env('DB_MAX_CONNECTIONS', 20),
            'idle_timeout' => env('DB_IDLE_TIMEOUT', 300), // 5分钟
        ],
        
        // 查询优化
        'query_optimization' => [
            // 慢查询日志阈值（秒）
            'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 2),
            
            // 是否启用查询日志
            'log_queries' => env('DB_LOG_QUERIES', false),
            
            // 批量操作大小
            'batch_size' => env('DB_BATCH_SIZE', 1000),
            
            // 预加载关系
            'eager_loading' => [
                'links' => ['user', 'category', 'votes'],
                'comments' => ['user', 'link'],
                'users' => ['profile'],
            ],
        ],
        
        // 索引优化建议
        'indexes' => [
            'links' => [
                ['user_id'],
                ['category_id'],
                ['created_at'],
                ['score'],
                ['status'],
                ['user_id', 'created_at'],
                ['category_id', 'score'],
            ],
            'comments' => [
                ['link_id'],
                ['user_id'],
                ['parent_id'],
                ['created_at'],
                ['link_id', 'created_at'],
            ],
            'votes' => [
                ['user_id'],
                ['votable_type', 'votable_id'],
                ['user_id', 'votable_type', 'votable_id'],
            ],
            'users' => [
                ['email'],
                ['username'],
                ['created_at'],
                ['status'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 前端性能优化
    |--------------------------------------------------------------------------
    |
    | 配置前端资源优化设置
    |
    */
    'frontend' => [
        // 资源压缩
        'compression' => [
            'css_minify' => env('CSS_MINIFY', true),
            'js_minify' => env('JS_MINIFY', true),
            'html_minify' => env('HTML_MINIFY', true),
        ],
        
        // 资源合并
        'bundling' => [
            'css_bundle' => env('CSS_BUNDLE', true),
            'js_bundle' => env('JS_BUNDLE', true),
        ],
        
        // CDN 配置
        'cdn' => [
            'enabled' => env('CDN_ENABLED', false),
            'url' => env('CDN_URL', ''),
            'assets' => ['css', 'js', 'images', 'fonts'],
        ],
        
        // 图片优化
        'image_optimization' => [
            'enabled' => env('IMAGE_OPTIMIZATION_ENABLED', true),
            'quality' => env('IMAGE_QUALITY', 85),
            'formats' => ['webp', 'jpg', 'png'],
            'lazy_loading' => env('IMAGE_LAZY_LOADING', true),
        ],
        
        // 字体优化
        'font_optimization' => [
            'preload' => true,
            'display' => 'swap',
            'subset' => 'latin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 会话优化配置
    |--------------------------------------------------------------------------
    |
    | 配置会话存储和性能优化
    |
    */
    'session' => [
        // 会话驱动优化
        'driver_optimization' => [
            'redis' => [
                'serializer' => 'php', // php, igbinary
                'compression' => env('SESSION_COMPRESSION', false),
            ],
            'database' => [
                'table' => 'sessions',
                'cleanup_probability' => 2,
                'cleanup_divisor' => 100,
            ],
        ],
        
        // 会话垃圾回收
        'garbage_collection' => [
            'probability' => env('SESSION_GC_PROBABILITY', 1),
            'divisor' => env('SESSION_GC_DIVISOR', 100),
            'max_lifetime' => env('SESSION_LIFETIME', 7200), // 2小时
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 队列优化配置
    |--------------------------------------------------------------------------
    |
    | 配置队列处理性能优化
    |
    */
    'queue' => [
        // 工作进程配置
        'workers' => [
            'default' => [
                'processes' => env('QUEUE_WORKERS', 3),
                'timeout' => env('QUEUE_TIMEOUT', 60),
                'sleep' => env('QUEUE_SLEEP', 3),
                'tries' => env('QUEUE_TRIES', 3),
            ],
            'high' => [
                'processes' => env('QUEUE_HIGH_WORKERS', 2),
                'timeout' => env('QUEUE_HIGH_TIMEOUT', 30),
                'sleep' => env('QUEUE_HIGH_SLEEP', 1),
                'tries' => env('QUEUE_HIGH_TRIES', 3),
            ],
        ],
        
        // 批量处理
        'batching' => [
            'enabled' => env('QUEUE_BATCHING_ENABLED', true),
            'size' => env('QUEUE_BATCH_SIZE', 100),
        ],
        
        // 失败任务处理
        'failed_jobs' => [
            'retention_days' => env('FAILED_JOBS_RETENTION', 7),
            'auto_retry' => env('FAILED_JOBS_AUTO_RETRY', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API 性能优化
    |--------------------------------------------------------------------------
    |
    | 配置 API 响应性能优化
    |
    */
    'api' => [
        // 响应缓存
        'response_cache' => [
            'enabled' => env('API_CACHE_ENABLED', true),
            'ttl' => env('API_CACHE_TTL', 300), // 5分钟
            'vary_by' => ['user', 'query_params'],
        ],
        
        // 分页优化
        'pagination' => [
            'default_per_page' => env('API_DEFAULT_PER_PAGE', 20),
            'max_per_page' => env('API_MAX_PER_PAGE', 100),
            'cursor_pagination' => env('API_CURSOR_PAGINATION', true),
        ],
        
        // 数据转换优化
        'transformation' => [
            'cache_transformers' => env('API_CACHE_TRANSFORMERS', true),
            'lazy_loading' => env('API_LAZY_LOADING', true),
        ],
        
        // 压缩
        'compression' => [
            'enabled' => env('API_COMPRESSION_ENABLED', true),
            'algorithm' => env('API_COMPRESSION_ALGORITHM', 'gzip'),
            'level' => env('API_COMPRESSION_LEVEL', 6),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 搜索性能优化
    |--------------------------------------------------------------------------
    |
    | 配置搜索功能性能优化
    |
    */
    'search' => [
        // 搜索缓存
        'cache' => [
            'enabled' => env('SEARCH_CACHE_ENABLED', true),
            'ttl' => env('SEARCH_CACHE_TTL', 1800), // 30分钟
        ],
        
        // 全文搜索优化
        'fulltext' => [
            'min_word_length' => env('SEARCH_MIN_WORD_LENGTH', 3),
            'max_results' => env('SEARCH_MAX_RESULTS', 1000),
            'relevance_threshold' => env('SEARCH_RELEVANCE_THRESHOLD', 0.1),
        ],
        
        // 搜索建议
        'suggestions' => [
            'enabled' => env('SEARCH_SUGGESTIONS_ENABLED', true),
            'max_suggestions' => env('SEARCH_MAX_SUGGESTIONS', 10),
            'cache_ttl' => env('SEARCH_SUGGESTIONS_CACHE_TTL', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 监控和分析配置
    |--------------------------------------------------------------------------
    |
    | 配置性能监控和分析
    |
    */
    'monitoring' => [
        // 性能指标收集
        'metrics' => [
            'enabled' => env('METRICS_ENABLED', true),
            'collect_interval' => env('METRICS_COLLECT_INTERVAL', 60), // 秒
            'retention_days' => env('METRICS_RETENTION_DAYS', 30),
        ],
        
        // 慢查询监控
        'slow_queries' => [
            'enabled' => env('SLOW_QUERY_MONITORING', true),
            'threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // 毫秒
            'log_stack_trace' => env('SLOW_QUERY_LOG_STACK_TRACE', false),
        ],
        
        // 内存使用监控
        'memory' => [
            'enabled' => env('MEMORY_MONITORING', true),
            'threshold' => env('MEMORY_THRESHOLD', 128), // MB
            'alert_threshold' => env('MEMORY_ALERT_THRESHOLD', 256), // MB
        ],
        
        // APM 集成
        'apm' => [
            'enabled' => env('APM_ENABLED', false),
            'service_name' => env('APM_SERVICE_NAME', 'laravel-links'),
            'environment' => env('APM_ENVIRONMENT', env('APP_ENV')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 优化建议和最佳实践
    |--------------------------------------------------------------------------
    |
    | 性能优化建议和配置提示
    |
    */
    'optimization_tips' => [
        'php' => [
            'opcache_enabled' => 'PHP OPcache 应该在生产环境中启用',
            'memory_limit' => '建议设置 PHP memory_limit 至少 256M',
            'max_execution_time' => '建议设置合适的 max_execution_time',
        ],
        
        'laravel' => [
            'config_cache' => '生产环境应该启用配置缓存: php artisan config:cache',
            'route_cache' => '生产环境应该启用路由缓存: php artisan route:cache',
            'view_cache' => '生产环境应该启用视图缓存: php artisan view:cache',
            'autoloader_optimization' => '使用 composer install --optimize-autoloader',
        ],
        
        'database' => [
            'indexes' => '确保为经常查询的字段创建索引',
            'query_optimization' => '使用 EXPLAIN 分析慢查询',
            'connection_pooling' => '考虑使用数据库连接池',
        ],
        
        'caching' => [
            'redis' => '推荐使用 Redis 作为缓存驱动',
            'cache_tags' => '使用缓存标签进行精确的缓存失效',
            'cache_warming' => '实施缓存预热策略',
        ],
    ],

];