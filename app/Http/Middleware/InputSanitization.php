<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 输入验证和清理中间件
 * 
 * 防止XSS和SQL注入攻击
 */
class InputSanitization
{
    /**
     * 危险的SQL关键词
     */
    private array $sqlKeywords = [
        'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter',
        'exec', 'execute', 'script', 'javascript', 'vbscript', 'onload', 'onerror'
    ];
    
    /**
     * XSS攻击模式
     */
    private array $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>.*?<\/embed>/is',
        '/javascript:/i',
        '/vbscript:/i',
        '/on\w+\s*=/i'
    ];
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inputConfig = config('security.input_validation', []);
        
        if (!($inputConfig['enabled'] ?? true)) {
            return $next($request);
        }
        
        // 检查和清理输入数据
        $this->sanitizeInput($request);
        
        return $next($request);
    }
    
    /**
     * 清理输入数据
     */
    private function sanitizeInput(Request $request): void
    {
        $inputConfig = config('security.input_validation', []);
        
        // 清理GET参数
        if ($inputConfig['sanitize_get'] ?? true) {
            $this->sanitizeArray($request->query->all(), $request->query);
        }
        
        // 清理POST数据
        if ($inputConfig['sanitize_post'] ?? true) {
            $this->sanitizeArray($request->request->all(), $request->request);
        }
        
        // 清理文件上传
        if ($inputConfig['validate_files'] ?? true) {
            $this->validateFiles($request);
        }
    }
    
    /**
     * 清理数组数据
     */
    private function sanitizeArray(array $data, $parameterBag): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->sanitizeArray($value, $parameterBag);
            } else {
                $sanitized = $this->sanitizeValue($value);
                if ($sanitized !== $value) {
                    $parameterBag->set($key, $sanitized);
                    
                    // 记录可疑输入
                    Log::warning('Suspicious input detected and sanitized', [
                        'key' => $key,
                        'original' => $value,
                        'sanitized' => $sanitized,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }
            }
        }
    }
    
    /**
     * 清理单个值
     */
    private function sanitizeValue(string $value): string
    {
        // 检测SQL注入
        if ($this->detectSqlInjection($value)) {
            return '';
        }
        
        // 清理XSS
        $value = $this->cleanXss($value);
        
        // 移除null字节
        $value = str_replace(chr(0), '', $value);
        
        // 清理控制字符
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return $value;
    }
    
    /**
     * 检测SQL注入
     */
    private function detectSqlInjection(string $value): bool
    {
        $value = strtolower($value);
        
        foreach ($this->sqlKeywords as $keyword) {
            if (strpos($value, $keyword) !== false) {
                // 进一步检查是否为恶意SQL
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $value)) {
                    return true;
                }
            }
        }
        
        // 检查SQL注入模式
        $patterns = [
            '/\bunion\s+select/i',
            '/\bor\s+1\s*=\s*1/i',
            '/\band\s+1\s*=\s*1/i',
            '/\'\s*or\s*\'.*\'\s*=\s*\'/i',
            '/\bdrop\s+table/i',
            '/\binsert\s+into/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 清理XSS攻击
     */
    private function cleanXss(string $value): string
    {
        // 移除XSS模式
        foreach ($this->xssPatterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }
        
        // HTML实体编码
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $value;
    }
    
    /**
     * 验证文件上传
     */
    private function validateFiles(Request $request): void
    {
        $fileConfig = config('security.file_upload', []);
        
        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile, $fileConfig);
                }
            } else {
                $this->validateSingleFile($file, $fileConfig);
            }
        }
    }
    
    /**
     * 验证单个文件
     */
    private function validateSingleFile($file, array $config): void
    {
        if (!$file->isValid()) {
            return;
        }
        
        $allowedTypes = $config['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $maxSize = $config['max_size'] ?? 10240; // KB
        
        // 检查文件类型
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            Log::warning('Invalid file type uploaded', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'ip' => request()->ip()
            ]);
        }
        
        // 检查文件大小
        if ($file->getSize() > $maxSize * 1024) {
            Log::warning('File size exceeded', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'max_size' => $maxSize * 1024,
                'ip' => request()->ip()
            ]);
        }
        
        // 检查文件内容
        $this->scanFileContent($file);
    }
    
    /**
     * 扫描文件内容
     */
    private function scanFileContent($file): void
    {
        $content = file_get_contents($file->getPathname());
        
        // 检查恶意脚本
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::alert('Malicious file content detected', [
                    'filename' => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                    'ip' => request()->ip()
                ]);
                break;
            }
        }
    }
}