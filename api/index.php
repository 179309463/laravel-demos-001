<?php

/**
 * Laravel 应用 Vercel 入口点
 * 此文件用于在 Vercel 平台上运行 Laravel 应用
 */

// 设置错误报告
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 为 Vercel serverless 环境创建必要的存储目录
if (!file_exists('/tmp/storage')) {
    mkdir('/tmp/storage', 0755, true);
}
if (!file_exists('/tmp/storage/framework')) {
    mkdir('/tmp/storage/framework', 0755, true);
}
if (!file_exists('/tmp/storage/framework/views')) {
    mkdir('/tmp/storage/framework/views', 0755, true);
}
if (!file_exists('/tmp/storage/framework/cache')) {
    mkdir('/tmp/storage/framework/cache', 0755, true);
}
if (!file_exists('/tmp/storage/framework/sessions')) {
    mkdir('/tmp/storage/framework/sessions', 0755, true);
}

// 定义应用路径
define('LARAVEL_START', microtime(true));

// 注册自动加载器
require __DIR__ . '/../vendor/autoload.php';

// 引导应用
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 处理请求
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);