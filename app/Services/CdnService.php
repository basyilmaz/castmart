<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;

class CdnService
{
    protected string $driver;
    protected array $config;

    public function __construct()
    {
        $this->driver = config('cdn.driver', 'local');
        $this->config = config('cdn.drivers.' . $this->driver, []);
    }

    /**
     * Dosyayı CDN'e yükle
     */
    public function upload(UploadedFile|string $file, string $path, array $options = []): ?string
    {
        $disk = $this->getDisk();
        
        try {
            if ($file instanceof UploadedFile) {
                $filename = $options['filename'] ?? $file->hashName();
                $fullPath = rtrim($path, '/') . '/' . $filename;
                
                $disk->putFileAs($path, $file, $filename, $options['visibility'] ?? 'public');
            } else {
                // String content olarak dosya
                $fullPath = $path;
                $disk->put($path, $file, $options['visibility'] ?? 'public');
            }

            // Cache invalidate
            $this->invalidateCache($fullPath);

            return $this->url($fullPath);
        } catch (\Exception $e) {
            \Log::error('CDN upload error', ['error' => $e->getMessage(), 'path' => $path]);
            return null;
        }
    }

    /**
     * CDN URL'i döndür
     */
    public function url(string $path): string
    {
        if ($this->driver === 'cloudflare') {
            return rtrim($this->config['url'], '/') . '/' . ltrim($path, '/');
        }

        if ($this->driver === 'bunny') {
            return rtrim($this->config['pull_zone_url'], '/') . '/' . ltrim($path, '/');
        }

        if ($this->driver === 's3' || $this->driver === 'spaces') {
            return $this->getDisk()->url($path);
        }

        // Local veya diğer
        return asset('storage/' . $path);
    }

    /**
     * Dosyayı sil
     */
    public function delete(string $path): bool
    {
        try {
            $this->getDisk()->delete($path);
            $this->invalidateCache($path);
            return true;
        } catch (\Exception $e) {
            \Log::error('CDN delete error', ['error' => $e->getMessage(), 'path' => $path]);
            return false;
        }
    }

    /**
     * Dosya var mı?
     */
    public function exists(string $path): bool
    {
        return $this->getDisk()->exists($path);
    }

    /**
     * Dosyayı getir
     */
    public function get(string $path): ?string
    {
        try {
            return $this->getDisk()->get($path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cache invalidate (Cloudflare, Bunny vb.)
     */
    public function invalidateCache(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : [$paths];

        if ($this->driver === 'cloudflare') {
            return $this->purgeCloudflare($paths);
        }

        if ($this->driver === 'bunny') {
            return $this->purgeBunny($paths);
        }

        return true;
    }

    /**
     * Cloudflare cache temizle
     */
    protected function purgeCloudflare(array $paths): bool
    {
        if (empty($this->config['zone_id']) || empty($this->config['api_token'])) {
            return false;
        }

        try {
            $urls = array_map(fn($path) => $this->url($path), $paths);

            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_token'],
                'Content-Type' => 'application/json',
            ])->post("https://api.cloudflare.com/client/v4/zones/{$this->config['zone_id']}/purge_cache", [
                'files' => $urls,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Cloudflare purge error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Bunny CDN cache temizle
     */
    protected function purgeBunny(array $paths): bool
    {
        if (empty($this->config['api_key']) || empty($this->config['pull_zone_id'])) {
            return false;
        }

        try {
            foreach ($paths as $path) {
                $url = $this->url($path);
                
                \Http::withHeaders([
                    'AccessKey' => $this->config['api_key'],
                ])->post("https://api.bunny.net/purge?url=" . urlencode($url));
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Bunny purge error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Tüm cache'i temizle
     */
    public function purgeAll(): bool
    {
        if ($this->driver === 'cloudflare') {
            try {
                $response = \Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['api_token'],
                    'Content-Type' => 'application/json',
                ])->post("https://api.cloudflare.com/client/v4/zones/{$this->config['zone_id']}/purge_cache", [
                    'purge_everything' => true,
                ]);

                return $response->successful();
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($this->driver === 'bunny') {
            try {
                \Http::withHeaders([
                    'AccessKey' => $this->config['api_key'],
                ])->post("https://api.bunny.net/pullzone/{$this->config['pull_zone_id']}/purgeCache");

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Storage disk al
     */
    protected function getDisk()
    {
        $diskName = $this->config['disk'] ?? 'public';
        return Storage::disk($diskName);
    }

    /**
     * Asset URL'lerini CDN ile değiştir
     */
    public function assetUrl(string $path): string
    {
        if (!config('cdn.enabled', false)) {
            return asset($path);
        }

        $cdnUrl = config('cdn.asset_url');
        
        if ($cdnUrl) {
            return rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');
        }

        return asset($path);
    }

    /**
     * Resim URL'ini CDN versiyonuyla değiştir
     */
    public function imageUrl(string $path, array $transforms = []): string
    {
        $url = $this->url($path);

        // Cloudflare Image Resizing
        if ($this->driver === 'cloudflare' && !empty($transforms)) {
            $params = [];
            
            if (isset($transforms['width'])) {
                $params[] = 'width=' . $transforms['width'];
            }
            if (isset($transforms['height'])) {
                $params[] = 'height=' . $transforms['height'];
            }
            if (isset($transforms['quality'])) {
                $params[] = 'quality=' . $transforms['quality'];
            }
            if (isset($transforms['format'])) {
                $params[] = 'format=' . $transforms['format'];
            }
            if (isset($transforms['fit'])) {
                $params[] = 'fit=' . $transforms['fit'];
            }

            if (!empty($params)) {
                $url = str_replace('/cdn-cgi/image/', '/cdn-cgi/image/' . implode(',', $params) . '/', $url);
            }
        }

        // Bunny Optimizer
        if ($this->driver === 'bunny' && !empty($transforms)) {
            $params = [];
            
            if (isset($transforms['width'])) {
                $params['width'] = $transforms['width'];
            }
            if (isset($transforms['height'])) {
                $params['height'] = $transforms['height'];
            }
            if (isset($transforms['quality'])) {
                $params['quality'] = $transforms['quality'];
            }

            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }

        return $url;
    }

    /**
     * Mevcut driver'ı döndür
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * CDN aktif mi?
     */
    public function isEnabled(): bool
    {
        return config('cdn.enabled', false) && $this->driver !== 'local';
    }
}
