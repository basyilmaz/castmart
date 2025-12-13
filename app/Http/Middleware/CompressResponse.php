<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Response compression middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sadece HTML/JSON response'ları sıkıştır
        $contentType = $response->headers->get('Content-Type', '');
        
        if (!str_contains($contentType, 'text/html') && 
            !str_contains($contentType, 'application/json')) {
            return $response;
        }

        // Zaten sıkıştırılmışsa atla
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Accept-Encoding kontrolü
        $acceptEncoding = $request->header('Accept-Encoding', '');

        if (str_contains($acceptEncoding, 'gzip') && function_exists('gzencode')) {
            $content = $response->getContent();
            
            // Küçük response'ları sıkıştırma (100 byte altı)
            if (strlen($content) < 100) {
                return $response;
            }

            $compressed = gzencode($content, 6);
            
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
        }

        return $response;
    }
}
