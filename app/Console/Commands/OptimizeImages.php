<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize 
                            {--directory= : Optimize edilecek klasör}
                            {--disk=public : Storage disk}
                            {--all : Tüm görselleri optimize et}';
                            
    protected $description = 'Görselleri optimize et ve WebP versiyonlarını oluştur';

    public function handle(ImageOptimizationService $optimizer)
    {
        $disk = $this->option('disk');
        $directory = $this->option('directory');

        if ($this->option('all')) {
            $this->optimizeAll($optimizer, $disk);
        } elseif ($directory) {
            $this->optimizeDirectory($optimizer, $directory, $disk);
        } else {
            $this->info('Kullanım:');
            $this->line('  --directory=product/images  Belirli klasörü optimize et');
            $this->line('  --all                       Tüm görselleri optimize et');
            $this->line('  --disk=public               Storage disk seç');
            return 0;
        }

        return 0;
    }

    protected function optimizeDirectory(ImageOptimizationService $optimizer, string $directory, string $disk): void
    {
        $this->info("📁 Klasör optimize ediliyor: {$directory}");
        
        $files = Storage::disk($disk)->files($directory);
        $imageFiles = collect($files)->filter(function ($file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
        });

        $bar = $this->output->createProgressBar($imageFiles->count());
        $bar->start();

        $totalSaved = 0;
        $processed = 0;

        foreach ($imageFiles as $file) {
            $result = $optimizer->optimize($file, $disk);
            $totalSaved += $result['saved_bytes'];
            $processed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$processed} görsel optimize edildi");
        $this->info("💾 Toplam tasarruf: " . $this->formatBytes($totalSaved));
    }

    protected function optimizeAll(ImageOptimizationService $optimizer, string $disk): void
    {
        $this->info("🔄 Tüm görseller optimize ediliyor...");
        
        $excludeDirs = config('image-optimization.exclude_directories', []);
        $allFiles = Storage::disk($disk)->allFiles();
        
        $imageFiles = collect($allFiles)->filter(function ($file) use ($excludeDirs) {
            // Hariç tutulan klasörleri atla
            foreach ($excludeDirs as $excludeDir) {
                if (str_starts_with($file, $excludeDir)) {
                    return false;
                }
            }
            
            // Zaten optimized/thumbnail dosyaları atla
            if (preg_match('/_(small|medium|large|product)\./', $file)) {
                return false;
            }
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
        });

        $this->info("📊 {$imageFiles->count()} görsel bulundu");
        
        $bar = $this->output->createProgressBar($imageFiles->count());
        $bar->start();

        $totalSaved = 0;
        $processed = 0;
        $failed = 0;

        foreach ($imageFiles as $file) {
            try {
                $result = $optimizer->optimize($file, $disk);
                $totalSaved += $result['saved_bytes'];
                $processed++;
            } catch (\Exception $e) {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Başarılı: {$processed}");
        if ($failed > 0) {
            $this->warn("❌ Başarısız: {$failed}");
        }
        $this->info("💾 Toplam tasarruf: " . $this->formatBytes($totalSaved));
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
