<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageOptimizationService
{
    protected ImageManager $manager;
    protected array $config;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->config = config('image-optimization', [
            'quality' => 85,
            'webp_quality' => 80,
            'max_width' => 1920,
            'max_height' => 1920,
            'create_webp' => true,
            'create_thumbnails' => true,
            'thumbnail_sizes' => [
                'small' => ['width' => 150, 'height' => 150],
                'medium' => ['width' => 300, 'height' => 300],
                'large' => ['width' => 600, 'height' => 600],
            ],
        ]);
    }

    /**
     * Görseli optimize et ve WebP versiyonunu oluştur
     */
    public function optimize(string $path, ?string $disk = null): array
    {
        $disk = $disk ?? 'public';
        $results = [
            'original' => $path,
            'webp' => null,
            'thumbnails' => [],
            'saved_bytes' => 0,
        ];

        try {
            $fullPath = Storage::disk($disk)->path($path);
            
            if (!file_exists($fullPath)) {
                return $results;
            }

            $originalSize = filesize($fullPath);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            // Sadece resim dosyalarını işle
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return $results;
            }

            $image = $this->manager->read($fullPath);

            // Boyut sınırlaması
            $this->resizeIfNeeded($image);

            // Orijinali optimize et
            $image->save($fullPath, $this->config['quality']);
            $optimizedSize = filesize($fullPath);
            $results['saved_bytes'] = $originalSize - $optimizedSize;

            // WebP versiyonu oluştur
            if ($this->config['create_webp'] && $extension !== 'webp') {
                $webpPath = $this->createWebPVersion($image, $path, $disk);
                $results['webp'] = $webpPath;
            }

            // Thumbnail'lar oluştur
            if ($this->config['create_thumbnails']) {
                $results['thumbnails'] = $this->createThumbnails($fullPath, $path, $disk);
            }

            Log::info('Image optimized', [
                'path' => $path,
                'original_size' => $this->formatBytes($originalSize),
                'optimized_size' => $this->formatBytes($optimizedSize),
                'saved' => $this->formatBytes($results['saved_bytes']),
            ]);

        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * WebP versiyonu oluştur
     */
    protected function createWebPVersion($image, string $originalPath, string $disk): ?string
    {
        try {
            $pathInfo = pathinfo($originalPath);
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $fullWebpPath = Storage::disk($disk)->path($webpPath);

            // WebP olarak kaydet
            $image->toWebp($this->config['webp_quality'])->save($fullWebpPath);

            return $webpPath;
        } catch (\Exception $e) {
            Log::warning('WebP creation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Thumbnail'lar oluştur
     */
    protected function createThumbnails(string $fullPath, string $originalPath, string $disk): array
    {
        $thumbnails = [];
        $pathInfo = pathinfo($originalPath);

        foreach ($this->config['thumbnail_sizes'] as $sizeName => $dimensions) {
            try {
                $image = $this->manager->read($fullPath);
                
                // Cover fit (crop to fill)
                $image->cover($dimensions['width'], $dimensions['height']);

                $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$sizeName}." . $pathInfo['extension'];
                $fullThumbPath = Storage::disk($disk)->path($thumbPath);
                
                $image->save($fullThumbPath, $this->config['quality']);
                $thumbnails[$sizeName] = $thumbPath;

                // WebP thumbnail
                if ($this->config['create_webp']) {
                    $webpThumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$sizeName}.webp";
                    $image->toWebp($this->config['webp_quality'])
                          ->save(Storage::disk($disk)->path($webpThumbPath));
                    $thumbnails[$sizeName . '_webp'] = $webpThumbPath;
                }

            } catch (\Exception $e) {
                Log::warning("Thumbnail creation failed for {$sizeName}", ['error' => $e->getMessage()]);
            }
        }

        return $thumbnails;
    }

    /**
     * Gerekirse boyutlandır
     */
    protected function resizeIfNeeded($image): void
    {
        $width = $image->width();
        $height = $image->height();
        $maxWidth = $this->config['max_width'];
        $maxHeight = $this->config['max_height'];

        if ($width > $maxWidth || $height > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }
    }

    /**
     * Klasördeki tüm görselleri optimize et
     */
    public function optimizeDirectory(string $directory, ?string $disk = null): array
    {
        $disk = $disk ?? 'public';
        $files = Storage::disk($disk)->files($directory);
        $results = [
            'processed' => 0,
            'failed' => 0,
            'total_saved' => 0,
        ];

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $result = $this->optimize($file, $disk);
                
                if ($result['saved_bytes'] > 0) {
                    $results['processed']++;
                    $results['total_saved'] += $result['saved_bytes'];
                } else {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }

    /**
     * WebP destekli resim URL'i döndür
     */
    public function getOptimizedUrl(string $path, string $size = null): string
    {
        $pathInfo = pathinfo($path);
        
        // Boyut varsa thumbnail kullan
        if ($size && isset($this->config['thumbnail_sizes'][$size])) {
            $path = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$size}." . $pathInfo['extension'];
        }

        // Tarayıcı WebP destekliyorsa WebP döndür
        if ($this->browserSupportsWebP()) {
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . 
                       ($size ? "_{$size}" : '') . '.webp';
            
            if (Storage::disk('public')->exists($webpPath)) {
                return Storage::disk('public')->url($webpPath);
            }
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Tarayıcı WebP destekliyor mu?
     */
    protected function browserSupportsWebP(): bool
    {
        $accept = request()->header('Accept', '');
        return str_contains($accept, 'image/webp');
    }

    /**
     * Byte'ları formatla
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Lazy loading için placeholder oluştur
     */
    public function createPlaceholder(string $path, int $width = 20): string
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            $image = $this->manager->read($fullPath);
            
            // Küçük blur'lu placeholder
            $image->scaleDown($width);
            
            return 'data:image/jpeg;base64,' . base64_encode($image->toJpeg(50)->toString());
        } catch (\Exception $e) {
            return '';
        }
    }
}
