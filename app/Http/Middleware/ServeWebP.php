<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeWebP
{
    /**
     * Handle an incoming request.
     * 
     * Eğer tarayıcı WebP destekliyorsa ve dosya mevcutsa,
     * otomatik olarak WebP versiyonunu sun.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sadece GET isteklerinde ve HTML response'larda çalış
        if ($request->method() !== 'GET' || !$this->isHtmlResponse($response)) {
            return $response;
        }

        // Tarayıcı WebP desteklemiyor
        if (!$this->browserSupportsWebP($request)) {
            return $response;
        }

        // HTML içeriğindeki resim URL'lerini WebP'ye dönüştür
        $content = $response->getContent();
        
        if ($content) {
            $modifiedContent = $this->replaceImageUrls($content);
            $response->setContent($modifiedContent);
        }

        return $response;
    }

    /**
     * Tarayıcı WebP destekliyor mu?
     */
    protected function browserSupportsWebP(Request $request): bool
    {
        $accept = $request->header('Accept', '');
        return str_contains($accept, 'image/webp');
    }

    /**
     * Response HTML mi?
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }

    /**
     * HTML içindeki resim URL'lerini WebP'ye dönüştür
     */
    protected function replaceImageUrls(string $content): string
    {
        // <img> taglarındaki src'leri değiştir
        $content = preg_replace_callback(
            '/<img([^>]*)\ssrc=["\']([^"\']+\.(jpg|jpeg|png))["\']([^>]*)>/i',
            function ($matches) {
                $before = $matches[1];
                $src = $matches[2];
                $after = $matches[4];
                
                $webpSrc = $this->getWebPUrl($src);
                
                if ($webpSrc) {
                    // Picture tag ile fallback
                    return '<picture>' .
                           '<source srcset="' . $webpSrc . '" type="image/webp">' .
                           '<img' . $before . ' src="' . $src . '"' . $after . '>' .
                           '</picture>';
                }
                
                return $matches[0];
            },
            $content
        );

        // Background-image'lardaki URL'leri değiştir
        $content = preg_replace_callback(
            '/background-image:\s*url\(["\']?([^"\')\s]+\.(jpg|jpeg|png))["\']?\)/i',
            function ($matches) {
                $src = $matches[1];
                $webpSrc = $this->getWebPUrl($src);
                
                if ($webpSrc) {
                    return 'background-image: url("' . $webpSrc . '")';
                }
                
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Resim URL'inden WebP URL'i oluştur
     */
    protected function getWebPUrl(string $url): ?string
    {
        // Sadece yerel dosyalar için
        if (str_starts_with($url, 'http') && !str_contains($url, request()->getHost())) {
            return null;
        }

        $pathInfo = pathinfo($url);
        $webpUrl = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

        // WebP dosyası var mı kontrol et
        $webpPath = public_path(parse_url($webpUrl, PHP_URL_PATH) ?? '');
        
        if (file_exists($webpPath)) {
            return $webpUrl;
        }

        // Storage'da kontrol et
        $storagePath = str_replace('/storage/', '', parse_url($webpUrl, PHP_URL_PATH) ?? '');
        if (\Storage::disk('public')->exists($storagePath)) {
            return $webpUrl;
        }

        return null;
    }
}
