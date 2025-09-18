<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 速率限制中间件
 * 
 * 防止API滥用和DDoS攻击
 */
class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $rateLimitConfig = config('security.rate_limiting', []);
        $config = $rateLimitConfig[$type] ?? $rateLimitConfig['default'] ?? [];
        
        if (empty($config)) {
            return $next($request);
        }
        
        $key = $this->generateKey($request, $type);
        $maxAttempts = $config['max_attempts'] ?? 60;
        $decayMinutes = $config['decay_minutes'] ?? 1;
        
        // 检查当前请求数
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            // 记录超限日志
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'type' => $type,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts
            ]);
            
            return response()->json([
                'error' => '请求过于频繁，请稍后再试',
                'retry_after' => $decayMinutes * 60
            ], 429);
        }
        
        // 增加请求计数
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        $response = $next($request);
        
        // 添加速率限制头部
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    /**
     * 生成速率限制键
     */
    private function generateKey(Request $request, string $type): string
    {
        $identifier = $request->user() ? $request->user()->id : $request->ip();
        return "rate_limit:{$type}:{$identifier}";
    }
}