<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 监控控制器
 * 
 * 提供系统监控和健康检查的API端点
 */
class MonitoringController extends Controller
{
    private MonitoringService $monitoringService;
    
    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }
    
    /**
     * 健康检查端点
     * 
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $results = $this->monitoringService->performHealthCheck();
            
            $statusCode = match($results['overall']['status'] ?? 'unknown') {
                'healthy' => 200,
                'warning' => 200,
                'unhealthy' => 503,
                default => 500
            };
            
            return response()->json([
                'status' => 'success',
                'data' => $results,
                'timestamp' => now()->toISOString()
            ], $statusCode);
            
        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Health check failed',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    
    /**
     * 简单的健康检查端点（用于负载均衡器）
     * 
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * 性能指标端点
     * 
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        try {
            $metrics = $this->monitoringService->collectPerformanceMetrics();
            
            return response()->json([
                'status' => 'success',
                'data' => $metrics,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Metrics collection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Metrics collection failed',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    
    /**
     * Prometheus格式的指标端点
     * 
     * @return \Illuminate\Http\Response
     */
    public function prometheusMetrics()
    {
        try {
            $metrics = $this->monitoringService->collectPerformanceMetrics();
            $output = $this->formatPrometheusMetrics($metrics);
            
            return response($output, 200, [
                'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Prometheus metrics failed', [
                'error' => $e->getMessage()
            ]);
            
            return response('# Error collecting metrics', 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }
    
    /**
     * 系统状态概览
     * 
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $health = $this->monitoringService->performHealthCheck();
            $metrics = $this->monitoringService->collectPerformanceMetrics();
            
            $status = [
                'application' => [
                    'name' => config('app.name'),
                    'version' => config('app.version', '1.0.0'),
                    'environment' => config('app.env'),
                    'debug' => config('app.debug'),
                ],
                'health' => $health['overall'] ?? ['status' => 'unknown'],
                'performance' => [
                    'memory_usage' => $metrics['memory']['formatted']['current'] ?? 'unknown',
                    'memory_peak' => $metrics['memory']['formatted']['peak'] ?? 'unknown',
                    'uptime' => $this->getUptime(),
                ],
                'services' => array_filter($health, function($key) {
                    return $key !== 'overall';
                }, ARRAY_FILTER_USE_KEY),
                'timestamp' => now()->toISOString()
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Status check failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Status check failed'
            ], 500);
        }
    }
    
    /**
     * 记录自定义指标
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function recordMetric(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|max:100',
            'value' => 'required|numeric',
            'context' => 'sometimes|array'
        ]);
        
        try {
            $this->monitoringService->recordPerformanceMetric(
                $request->input('type'),
                $request->input('value'),
                $request->input('context', [])
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Metric recorded successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to record metric', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record metric'
            ], 500);
        }
    }
    
    /**
     * 获取系统日志
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logs(Request $request): JsonResponse
    {
        $request->validate([
            'level' => 'sometimes|string|in:debug,info,warning,error,critical',
            'limit' => 'sometimes|integer|min:1|max:1000',
            'channel' => 'sometimes|string|max:50'
        ]);
        
        try {
            $level = $request->input('level', 'info');
            $limit = $request->input('limit', 100);
            $channel = $request->input('channel', 'daily');
            
            // 这里需要实现日志读取逻辑
            // 由于Laravel没有内置的日志读取API，这里提供一个简单的实现
            $logs = $this->getRecentLogs($channel, $level, $limit);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'logs' => $logs,
                    'total' => count($logs),
                    'level' => $level,
                    'channel' => $channel
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve logs', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve logs'
            ], 500);
        }
    }
    
    /**
     * 格式化Prometheus指标
     */
    private function formatPrometheusMetrics(array $metrics): string
    {
        $output = [];
        $namespace = config('monitoring.integrations.prometheus.namespace', 'laravel_links');
        
        // 内存指标
        if (isset($metrics['memory'])) {
            $output[] = "# HELP {$namespace}_memory_usage_bytes Current memory usage in bytes";
            $output[] = "# TYPE {$namespace}_memory_usage_bytes gauge";
            $output[] = "{$namespace}_memory_usage_bytes {$metrics['memory']['current_usage']}";
            
            $output[] = "# HELP {$namespace}_memory_peak_bytes Peak memory usage in bytes";
            $output[] = "# TYPE {$namespace}_memory_peak_bytes gauge";
            $output[] = "{$namespace}_memory_peak_bytes {$metrics['memory']['peak_usage']}";
        }
        
        // CPU负载指标
        if (isset($metrics['cpu']) && is_array($metrics['cpu'])) {
            $output[] = "# HELP {$namespace}_cpu_load_1min CPU load average (1 minute)";
            $output[] = "# TYPE {$namespace}_cpu_load_1min gauge";
            $output[] = "{$namespace}_cpu_load_1min {$metrics['cpu']['1min']}";
        }
        
        // 数据库指标
        if (isset($metrics['database']['query_time'])) {
            $output[] = "# HELP {$namespace}_database_query_time_ms Database query response time in milliseconds";
            $output[] = "# TYPE {$namespace}_database_query_time_ms gauge";
            $output[] = "{$namespace}_database_query_time_ms {$metrics['database']['query_time']}";
        }
        
        // 缓存指标
        if (isset($metrics['cache']['response_time'])) {
            $output[] = "# HELP {$namespace}_cache_response_time_ms Cache response time in milliseconds";
            $output[] = "# TYPE {$namespace}_cache_response_time_ms gauge";
            $output[] = "{$namespace}_cache_response_time_ms {$metrics['cache']['response_time']}";
        }
        
        return implode("\n", $output) . "\n";
    }
    
    /**
     * 获取系统运行时间
     */
    private function getUptime(): string
    {
        $uptimeFile = '/proc/uptime';
        
        if (file_exists($uptimeFile)) {
            $uptime = (float) file_get_contents($uptimeFile);
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        }
        
        return 'unknown';
    }
    
    /**
     * 获取最近的日志记录
     */
    private function getRecentLogs(string $channel, string $level, int $limit): array
    {
        // 这是一个简化的实现，实际项目中可能需要更复杂的日志解析
        $logFile = storage_path("logs/{$channel}.log");
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];
        
        // 从文件末尾开始读取
        $lines = array_reverse(array_slice($lines, -$limit * 2));
        
        foreach ($lines as $line) {
            if (count($logs) >= $limit) {
                break;
            }
            
            // 简单的日志解析（实际项目中可能需要更复杂的解析）
            if (strpos($line, strtoupper($level)) !== false) {
                $logs[] = [
                    'message' => $line,
                    'timestamp' => $this->extractTimestamp($line)
                ];
            }
        }
        
        return array_reverse($logs);
    }
    
    /**
     * 从日志行中提取时间戳
     */
    private function extractTimestamp(string $line): ?string
    {
        // 匹配Laravel日志格式中的时间戳
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}