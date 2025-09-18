<?php

/**
 * 生产环境数据库优化配置
 * 此文件包含针对生产环境的数据库连接和性能优化设置
 */

return [
    /*
    |--------------------------------------------------------------------------
    | 生产环境数据库连接配置
    |--------------------------------------------------------------------------
    |
    | 针对生产环境优化的数据库连接设置
    |
    */
    'connections' => [
        'mysql_production' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel_links_production'),
            'username' => env('DB_USERNAME', 'laravel_user'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                // 连接池配置
                PDO::ATTR_PERSISTENT => true,
                // 超时设置
                PDO::ATTR_TIMEOUT => 30,
                // 错误模式
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // 默认获取模式
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]) : [],
            // 连接池设置
            'pool' => [
                'min_connections' => 5,
                'max_connections' => 50,
                'connect_timeout' => 10,
                'wait_timeout' => 3,
                'heartbeat' => 60,
                'max_idle_time' => 60,
            ],
        ],

        'mysql_read' => [
            'driver' => 'mysql',
            'read' => [
                'host' => [
                    env('DB_READ_HOST_1', '127.0.0.1'),
                    env('DB_READ_HOST_2', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => [
                    env('DB_WRITE_HOST', '127.0.0.1'),
                ],
            ],
            'sticky' => true,
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel_links_production'),
            'username' => env('DB_USERNAME', 'laravel_user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MySQL 性能优化建议
    |--------------------------------------------------------------------------
    |
    | 以下是推荐的 MySQL 配置参数，需要在 MySQL 服务器的 my.cnf 文件中设置
    |
    */
    'mysql_optimization_suggestions' => [
        // InnoDB 缓冲池大小（建议设置为服务器内存的 70-80%）
        'innodb_buffer_pool_size' => '2G',
        
        // InnoDB 日志文件大小
        'innodb_log_file_size' => '256M',
        
        // InnoDB 日志缓冲区大小
        'innodb_log_buffer_size' => '16M',
        
        // 查询缓存大小
        'query_cache_size' => '128M',
        'query_cache_type' => 'ON',
        
        // 连接数设置
        'max_connections' => '200',
        'max_user_connections' => '100',
        
        // 超时设置
        'wait_timeout' => '600',
        'interactive_timeout' => '600',
        
        // 慢查询日志
        'slow_query_log' => 'ON',
        'long_query_time' => '2',
        
        // 字符集设置
        'character_set_server' => 'utf8mb4',
        'collation_server' => 'utf8mb4_unicode_ci',
        
        // 二进制日志
        'log_bin' => 'mysql-bin',
        'binlog_format' => 'ROW',
        'expire_logs_days' => '7',
    ],

    /*
    |--------------------------------------------------------------------------
    | 数据库索引优化建议
    |--------------------------------------------------------------------------
    */
    'index_suggestions' => [
        'links' => [
            'INDEX idx_user_created (user_id, created_at)',
            'INDEX idx_votes_count (votes_count)',
            'INDEX idx_created_at (created_at)',
        ],
        'comments' => [
            'INDEX idx_link_created (link_id, created_at)',
            'INDEX idx_user_created (user_id, created_at)',
            'INDEX idx_parent_id (parent_id)',
        ],
        'votes' => [
            'INDEX idx_user_link (user_id, link_id)',
            'INDEX idx_link_type (link_id, type)',
        ],
        'users' => [
            'INDEX idx_email (email)',
            'INDEX idx_created_at (created_at)',
        ],
    ],
];