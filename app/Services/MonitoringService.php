<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

/**
 * 系统监控服务
 * 
 * 负责监控系统性能、健康状态和安全指标
 */
class MonitoringService
{
    /**
     * 执行健康检查
     */
    public function performHealthCheck(): array
    {
        $results = [];
        $config = config('monitoring.health_checks', []);
        
        if (!($config['enabled'] ?? true)) {
            return ['status' => 'disabled'];
        }
        
        // 数据库连接检查
        if ($config['checks']['database']['enabled'] ?? true) {
            $results['database'] = $this->checkDatabase();
        }
        
        // Redis连接检查
        if ($config['checks']['redis']['enabled'] ?? true) {
            $results['redis'] = $this->checkRedis();
        }
        
        // 磁盘空间检查
        if ($config['checks']['disk_space']['enabled'] ?? true) {
            $results['disk_space'] = $this->checkDiskSpace();
        }
        
        // 队列检查
        if ($config['checks']['queue']['enabled'] ?? true) {
            $results['queue'] = $this->checkQueue();
        }
        
        // 计算总体健康状态
        $results['overall'] = $this->calculateOverallHealth($results);
        
        return $results;
    }
    
    /**
     * 检查数据库连接
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;
            
            $timeout = config('monitoring.health_checks.checks.database.timeout', 5) * 1000;
            
            return [
                'status' => $responseTime < $timeout ? 'healthy' : 'slow',
                'response_time' => round($responseTime, 2),
                'message' => $responseTime < $timeout ? 'Database connection is healthy' : 'Database response is slow'
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'response_time' => null,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查Redis连接
     */
    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $responseTime = (microtime(true) - $start) * 1000;
            
            $timeout = config('monitoring.health_checks.checks.redis.timeout', 3) * 1000;
            
            return [
                'status' => $responseTime < $timeout ? 'healthy' : 'slow',
                'response_time' => round($responseTime, 2),
                'message' => $responseTime < $timeout ? 'Redis connection is healthy' : 'Redis response is slow'
            ];
        } catch (\Exception $e) {
            Log::error('Redis health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'response_time' => null,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查磁盘空间
     */
    private function checkDiskSpace(): array
    {
        try {
            $path = storage_path();
            $totalBytes = disk_total_space($path);
            $freeBytes = disk_free_space($path);
            $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
            
            $threshold = config('monitoring.health_checks.checks.disk_space.threshold', 90);
            
            return [
                'status' => $usedPercent < $threshold ? 'healthy' : 'warning',
                'used_percent' => round($usedPercent, 2),
                'free_space' => $this->formatBytes($freeBytes),
                'total_space' => $this->formatBytes($totalBytes),
                'message' => $usedPercent < $threshold ? 'Disk space is sufficient' : 'Disk space is running low'
            ];
        } catch (\Exception $e) {
            Log::error('Disk space check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'message' => 'Disk space check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查队列状态
     */
    private function checkQueue(): array
    {
        try {
            // 这里需要根据实际使用的队列驱动来实现
            // 示例使用Redis队列
            $pendingJobs = Redis::llen('queues:default');
            $maxPendingJobs = config('monitoring.health_checks.checks.queue.max_pending_jobs', 1000);
            
            return [
                'status' => $pendingJobs < $maxPendingJobs ? 'healthy' : 'warning',
                'pending_jobs' => $pendingJobs,
                'max_pending_jobs' => $maxPendingJobs,
                'message' => $pendingJobs < $maxPendingJobs ? 'Queue is processing normally' : 'Queue has too many pending jobs'
            ];
        } catch (\Exception $e) {
            Log::error('Queue health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'message' => 'Queue check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 计算总体健康状态
     */
    private function calculateOverallHealth(array $results): array
    {
        $statuses = array_column($results, 'status');
        
        if (in_array('unhealthy', $statuses)) {
            $status = 'unhealthy';
            $message = 'One or more critical services are unhealthy';
        } elseif (in_array('warning', $statuses) || in_array('slow', $statuses)) {
            $status = 'warning';
            $message = 'Some services are experiencing issues';
        } else {
            $status = 'healthy';
            $message = 'All services are healthy';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
    }
    
    /**
     * 收集性能指标
     */
    public function collectPerformanceMetrics(): array
    {
        return [
            'memory' => $this->getMemoryMetrics(),
            'cpu' => $this->getCpuMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'response_time' => $this->getResponseTimeMetrics(),
        ];
    }
    
    /**
     * 获取内存使用指标
     */
    private function getMemoryMetrics(): array
    {
        return [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
            'formatted' => [
                'current' => $this->formatBytes(memory_get_usage(true)),
                'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ]
        ];
    }
    
    /**
     * 获取CPU使用指标
     */
    private function getCpuMetrics(): array
    {
        // 简单的CPU负载检查（仅在Linux系统上有效）
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2],
            ];
        }
        
        return ['message' => 'CPU metrics not available on this system'];
    }
    
    /**
     * 获取数据库性能指标
     */
    private function getDatabaseMetrics(): array
    {
        try {
            $start = microtime(true);
            $connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
            $queryTime = (microtime(true) - $start) * 1000;
            
            return [
                'query_time' => round($queryTime, 2),
                'connections' => $connections[0]->Value ?? 'unknown',
                'status' => 'available'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取缓存性能指标
     */
    private function getCacheMetrics(): array
    {
        try {
            $start = microtime(true);
            Cache::get('health_check_test', 'default');
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'response_time' => round($responseTime, 2),
                'status' => 'available'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取响应时间指标
     */
    private function getResponseTimeMetrics(): array
    {
        // 从缓存中获取最近的响应时间统计
        $key = 'response_time_stats';
        $stats = Cache::get($key, []);
        
        if (empty($stats)) {
            return ['message' => 'No response time data available'];
        }
        
        return [
            'average' => array_sum($stats) / count($stats),
            'min' => min($stats),
            'max' => max($stats),
            'count' => count($stats)
        ];
    }
    
    /**
     * 记录性能指标
     */
    public function recordPerformanceMetric(string $type, float $value, array $context = []): void
    {
        $data = [
            'type' => $type,
            'value' => $value,
            'timestamp' => now()->toISOString(),
            'context' => $context
        ];
        
        Log::channel('performance')->info('Performance metric recorded', $data);
        
        // 存储到缓存用于实时监控
        $key = "performance_metric:{$type}";
        $metrics = Cache::get($key, []);
        $metrics[] = $value;
        
        // 只保留最近100个数据点
        if (count($metrics) > 100) {
            $metrics = array_slice($metrics, -100);
        }
        
        Cache::put($key, $metrics, now()->addHours(1));
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 发送监控告警
     */
    public function sendAlert(string $level, string $message, array $context = []): void
    {
        $notificationConfig = config('monitoring.notifications', []);
        
        if (!($notificationConfig['enabled'] ?? true)) {
            return;
        }
        
        $data = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'server' => gethostname(),
            'app' => config('app.name')
        ];
        
        // 记录告警日志
        Log::channel('security')->log($level, $message, $data);
        
        // 发送邮件通知
        if ($this->shouldSendNotification('email', $level)) {
            // 这里可以集成邮件发送逻辑
        }
        
        // 发送Slack通知
        if ($this->shouldSendNotification('slack', $level)) {
            // 这里可以集成Slack通知逻辑
        }
        
        // 发送短信通知
        if ($this->shouldSendNotification('sms', $level)) {
            // 这里可以集成短信发送逻辑
        }
    }
    
    /**
     * 判断是否应该发送通知
     */
    private function shouldSendNotification(string $channel, string $level): bool
    {
        $config = config("monitoring.notifications.{$channel}", []);
        
        if (!($config['enabled'] ?? false)) {
            return false;
        }
        
        $threshold = $config['severity_threshold'] ?? 'error';
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
        
        return ($levels[$level] ?? 0) >= ($levels[$threshold] ?? 3);
    }
}