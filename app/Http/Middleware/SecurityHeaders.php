<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 安全头部中间件
 * 
 * 为所有响应添加安全相关的 HTTP 头部
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 获取安全配置
        $securityConfig = config('security.headers', []);
        
        // 内容安全策略 (CSP)
        if (isset($securityConfig['content_security_policy'])) {
            $csp = $this->buildContentSecurityPolicy($securityConfig['content_security_policy']);
            $response->headers->set('Content-Security-Policy', $csp);
        }
        
        // HTTP 严格传输安全 (HSTS)
        if (isset($securityConfig['strict_transport_security']) && $request->isSecure()) {
            $hsts = $this->buildStrictTransportSecurity($securityConfig['strict_transport_security']);
            $response->headers->set('Strict-Transport-Security', $hsts);
        }
        
        // X-Frame-Options
        if (isset($securityConfig['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', $securityConfig['x_frame_options']);
        }
        
        // X-Content-Type-Options
        if (isset($securityConfig['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $securityConfig['x_content_type_options']);
        }
        
        // X-XSS-Protection
        if (isset($securityConfig['x_xss_protection'])) {
            $response->headers->set('X-XSS-Protection', $securityConfig['x_xss_protection']);
        }
        
        // Referrer-Policy
        if (isset($securityConfig['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $securityConfig['referrer_policy']);
        }
        
        // Permissions-Policy
        if (isset($securityConfig['permissions_policy'])) {
            $permissionsPolicy = $this->buildPermissionsPolicy($securityConfig['permissions_policy']);
            $response->headers->set('Permissions-Policy', $permissionsPolicy);
        }
        
        // 移除可能泄露服务器信息的头部
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        
        return $response;
    }
    
    /**
     * 构建内容安全策略字符串
     */
    private function buildContentSecurityPolicy(array $csp): string
    {
        $policies = [];
        
        foreach ($csp as $directive => $sources) {
            if (is_array($sources)) {
                $policies[] = str_replace('_', '-', $directive) . ' ' . implode(' ', $sources);
            } else {
                $policies[] = str_replace('_', '-', $directive) . ' ' . $sources;
            }
        }
        
        return implode('; ', $policies);
    }
    
    /**
     * 构建严格传输安全字符串
     */
    private function buildStrictTransportSecurity(array $hsts): string
    {
        $policy = 'max-age=' . ($hsts['max_age'] ?? 31536000);
        
        if ($hsts['include_subdomains'] ?? false) {
            $policy .= '; includeSubDomains';
        }
        
        if ($hsts['preload'] ?? false) {
            $policy .= '; preload';
        }
        
        return $policy;
    }
    
    /**
     * 构建权限策略字符串
     */
    private function buildPermissionsPolicy(array $permissions): string
    {
        $policies = [];
        
        foreach ($permissions as $feature => $allowlist) {
            if (is_array($allowlist)) {
                $policies[] = $feature . '=(' . implode(' ', $allowlist) . ')';
            } else {
                $policies[] = $feature . '=' . $allowlist;
            }
        }
        
        return implode(', ', $policies);
    }
}