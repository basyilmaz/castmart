<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

class ServeThemeAssets
{
    /**
     * Theme asset'lerini storage yerine doğrudan sunmak için middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        
        // storage/themes ile başlıyorsa
        if (str_starts_with($path, 'storage/themes')) {
            $actualPath = str_replace('storage/', storage_path('app/public/'), $path);
            
            if (File::exists($actualPath)) {
                $mimeType = $this->getMimeType($actualPath);
                
                return response()->file($actualPath, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Dosya uzantısına göre MIME type belirle
     */
    protected function getMimeType(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            default => 'application/octet-stream',
        };
    }
}
