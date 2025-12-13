<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class XssProtection
{
    /**
     * XSS koruması
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        
        if ($this->hasXssAttack($input)) {
            Log::warning('XSS attack attempt detected', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Invalid Request',
                'message' => 'İstek güvenlik kontrolünden geçemedi.',
            ], 400);
        }

        // Input'u temizle
        $sanitized = $this->sanitizeInput($input);
        $request->merge($sanitized);

        return $next($request);
    }

    /**
     * XSS pattern kontrolü
     */
    protected function hasXssAttack(array $input): bool
    {
        $patterns = [
            // Script tags
            '/<script\b[^>]*>.*?<\/script>/is',
            // Event handlers
            '/\bon\w+\s*=/i',
            // JavaScript protocol
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            // Expression
            '/expression\s*\(/i',
            // Style injection
            '/<style\b[^>]*>.*?<\/style>/is',
            // Object/embed/iframe
            '/<(object|embed|iframe|frame|frameset|applet|meta|link)\b/i',
            // SVG attacks
            '/<svg\b.*?onload\s*=/is',
        ];

        return $this->checkPatterns($input, $patterns);
    }

    /**
     * Pattern kontrolü
     */
    protected function checkPatterns(array $input, array $patterns): bool
    {
        foreach ($input as $value) {
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
     * Input temizleme
     */
    protected function sanitizeInput(array $input): array
    {
        // Temizlenmeyecek alanlar (HTML editör içeriği vb.)
        $allowedHtmlFields = ['content', 'description', 'body', 'html_content'];

        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                $value = $this->sanitizeInput($value);
            } elseif (is_string($value)) {
                // HTML'e izin verilen alanlar hariç temizle
                if (!in_array($key, $allowedHtmlFields)) {
                    $value = $this->cleanString($value);
                }
            }
        }

        return $input;
    }

    /**
     * String temizle
     */
    protected function cleanString(string $value): string
    {
        // HTML entities encode
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
        
        // Null bytes temizle
        $value = str_replace("\0", '', $value);
        
        return $value;
    }
}
