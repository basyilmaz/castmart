<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

/*
 * Image Cache Route - Railway için dinamik görsel cache
 * Bu route, cache edilmiş görseller bulunamadığında anlık oluşturur
 */
Route::get('cache/{template}/{path}', function ($template, $path) {
    // Template boyutları
    $sizes = [
        'small' => ['width' => 100, 'height' => 100],
        'medium' => ['width' => 280, 'height' => 280],
        'large' => ['width' => 480, 'height' => 480],
    ];
    
    if (!isset($sizes[$template])) {
        abort(404);
    }
    
    // Orijinal görsel yolu
    $originalPath = storage_path('app/public/' . $path);
    
    // Eğer public'te varsa
    if (!File::exists($originalPath)) {
        $originalPath = public_path('storage/' . $path);
    }
    
    // Hala yoksa, public'te ara
    if (!File::exists($originalPath)) {
        $originalPath = public_path($path);
    }
    
    if (!File::exists($originalPath)) {
        // Placeholder görsel döndür
        $placeholder = public_path('images/placeholder.png');
        if (File::exists($placeholder)) {
            return response()->file($placeholder, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        abort(404);
    }
    
    // Cache klasörü
    $cacheDir = storage_path('app/public/cache/' . $template);
    $cachePath = $cacheDir . '/' . str_replace('/', '-', $path);
    
    // Cache varsa döndür
    if (File::exists($cachePath)) {
        return response()->file($cachePath, [
            'Content-Type' => mime_content_type($cachePath),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
    
    // Cache yoksa oluştur
    try {
        if (!File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }
        
        $size = $sizes[$template];
        $image = Image::make($originalPath);
        $image->fit($size['width'], $size['height']);
        $image->save($cachePath);
        
        return response()->file($cachePath, [
            'Content-Type' => $image->mime(),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    } catch (\Exception $e) {
        // Hata durumunda orijinal görseli döndür
        return response()->file($originalPath, [
            'Content-Type' => mime_content_type($originalPath),
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
})->where('path', '.*')->name('image.cache');
