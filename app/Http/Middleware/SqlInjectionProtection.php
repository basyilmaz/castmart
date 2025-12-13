<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SqlInjectionProtection
{
    /**
     * SQL Injection koruması
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        
        if ($this->hasSqlInjection($input)) {
            Log::warning('SQL Injection attempt detected', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'input' => $this->sanitizeForLog($input),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Invalid Request',
                'message' => 'İstek güvenlik kontrolünden geçemedi.',
            ], 400);
        }

        return $next($request);
    }

    /**
     * SQL injection pattern kontrolü
     */
    protected function hasSqlInjection(array $input): bool
    {
        $patterns = [
            // Union-based injection
            '/\bunion\b.*\bselect\b/i',
            // Boolean-based injection
            '/\bor\b.*[\'\"]\s*=\s*[\'\"]?/i',
            '/\band\b.*[\'\"]\s*=\s*[\'\"]?/i',
            // Comment injection
            '/--\s*$/m',
            '/\/\*.*\*\//s',
            // Stacked queries
            '/;\s*(drop|delete|truncate|update|insert|alter)\b/i',
            // Time-based injection
            '/\bsleep\s*\(/i',
            '/\bbenchmark\s*\(/i',
            '/\bwaitfor\s+delay\b/i',
            // Information gathering
            '/\binformation_schema\b/i',
            '/\bsysobjects\b/i',
            '/\bsyscolumns\b/i',
            // Hex encoding
            '/0x[0-9a-f]{8,}/i',
        ];

        return $this->checkPatterns($input, $patterns);
    }

    /**
     * Pattern kontrolü (nested arrays dahil)
     */
    protected function checkPatterns(array $input, array $patterns): bool
    {
        foreach ($input as $key => $value) {
            // Key kontrolü
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $key)) {
                    return true;
                }
            }

            // Value kontrolü
            if (is_array($value)) {
                if ($this->checkPatterns($value, $patterns)) {
                    return true;
                }
            } elseif (is_string($value)) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Log için input temizle (hassas verileri gizle)
     */
    protected function sanitizeForLog(array $input): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'credit_card', 'cvv', 'pin'];
        
        foreach ($input as $key => &$value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '***HIDDEN***';
            } elseif (is_array($value)) {
                $value = $this->sanitizeForLog($value);
            }
        }

        return $input;
    }
}
