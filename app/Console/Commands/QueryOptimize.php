<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueryOptimize extends Command
{
    protected $signature = 'db:optimize {--analyze : Run ANALYZE TABLE}';
    protected $description = 'Veritabanı optimizasyonu - index analizi ve öneriler';

    public function handle(): int
    {
        $this->info('Veritabanı optimizasyonu başlıyor...');

        // Tablo istatistikleri
        $tables = DB::select('SHOW TABLE STATUS');

        $this->table(
            ['Tablo', 'Satır', 'Boyut (MB)', 'Index (MB)', 'Engine'],
            collect($tables)->map(fn($t) => [
                $t->Name,
                number_format($t->Rows ?? 0),
                round(($t->Data_length ?? 0) / 1024 / 1024, 2),
                round(($t->Index_length ?? 0) / 1024 / 1024, 2),
                $t->Engine ?? 'Unknown',
            ])->toArray()
        );

        // Analyze tables
        if ($this->option('analyze')) {
            $this->info('Tablo analizi yapılıyor...');
            
            foreach ($tables as $table) {
                $this->line("Analyzing: {$table->Name}");
                DB::statement("ANALYZE TABLE {$table->Name}");
            }
            
            $this->info('Tablo analizi tamamlandı.');
        }

        // Slow query önerileri
        $this->warn('⚠️ Performans Önerileri:');
        $this->line('1. products tablosunda sku, name alanlarına index ekleyin');
        $this->line('2. orders tablosunda created_at, status alanlarına composite index');
        $this->line('3. Büyük tablolarda pagination kullanın (limit 50-100)');
        $this->line('4. N+1 sorgu problemini with() eager loading ile çözün');

        return Command::SUCCESS;
    }
}
