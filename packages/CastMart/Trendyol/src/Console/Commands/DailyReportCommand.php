<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Trendyol\Models\IntelligenceAlert;
use CastMart\Trendyol\Services\IntelligenceService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DailyReportCommand extends Command
{
    protected $signature = 'trendyol:daily-report 
                            {--account= : Belirli hesap ID\'si}
                            {--email= : Raporu gönderilecek e-posta adresi}';

    protected $description = 'Trendyol günlük performans raporu oluşturur';

    public function handle(): int
    {
        $this->info('📊 Trendyol Günlük Rapor Oluşturuluyor...');
        $this->newLine();

        $accountId = $this->option('account');
        $email = $this->option('email');

        $accounts = $accountId
            ? MarketplaceAccount::where('id', $accountId)->get()
            : MarketplaceAccount::where('marketplace', 'trendyol')
                ->where('is_active', true)
                ->get();

        if ($accounts->isEmpty()) {
            $this->warn('Aktif Trendyol hesabı bulunamadı.');
            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->generateReport($account, $email);
        }

        $this->info('✅ Günlük rapor tamamlandı!');
        return self::SUCCESS;
    }

    private function generateReport(MarketplaceAccount $account, ?string $email): void
    {
        $this->info("📈 {$account->name} hesabı için rapor oluşturuluyor...");

        $intelligenceService = new IntelligenceService($account);
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Bugünün verileri
        $todayOrders = MarketplaceOrder::where('account_id', $account->id)
            ->where('created_at', '>=', $today)
            ->get();

        // Dünün verileri
        $yesterdayOrders = MarketplaceOrder::where('account_id', $account->id)
            ->where('created_at', '>=', $yesterday)
            ->where('created_at', '<', $today)
            ->get();

        // Haftalık veriler
        $weekAgo = now()->subDays(7);
        $weeklyOrders = MarketplaceOrder::where('account_id', $account->id)
            ->where('created_at', '>=', $weekAgo)
            ->get();

        // BuyBox durumu
        $buyboxStats = [
            'won' => BuyboxTracking::where('marketplace_account_id', $account->id)
                ->where('status', 'won')->count(),
            'lost' => BuyboxTracking::where('marketplace_account_id', $account->id)
                ->where('status', 'lost')->count(),
            'risk' => BuyboxTracking::where('marketplace_account_id', $account->id)
                ->where('status', 'risk')->count(),
        ];
        $buyboxTotal = array_sum($buyboxStats);

        // Aktif uyarılar
        $alerts = IntelligenceAlert::where('marketplace_account_id', $account->id)
            ->active()
            ->get();

        // Sağlık skoru
        $healthScore = $intelligenceService->calculateHealthScore();

        // Rapor özeti
        $report = [
            'account_name' => $account->name,
            'date' => now()->format('d.m.Y'),
            'health_score' => $healthScore['total_score'],
            'health_label' => $healthScore['label'],
            'orders' => [
                'today' => $todayOrders->count(),
                'yesterday' => $yesterdayOrders->count(),
                'weekly' => $weeklyOrders->count(),
                'change' => $yesterdayOrders->count() > 0 
                    ? round((($todayOrders->count() - $yesterdayOrders->count()) / $yesterdayOrders->count()) * 100, 1)
                    : 0,
            ],
            'revenue' => [
                'today' => $this->calculateRevenue($todayOrders),
                'yesterday' => $this->calculateRevenue($yesterdayOrders),
                'weekly' => $this->calculateRevenue($weeklyOrders),
            ],
            'buybox' => [
                'won' => $buyboxStats['won'],
                'lost' => $buyboxStats['lost'],
                'risk' => $buyboxStats['risk'],
                'rate' => $buyboxTotal > 0 ? round(($buyboxStats['won'] / $buyboxTotal) * 100, 1) : 0,
            ],
            'alerts' => [
                'critical' => $alerts->where('type', 'critical')->count(),
                'warning' => $alerts->where('type', 'warning')->count(),
                'opportunity' => $alerts->where('type', 'opportunity')->count(),
            ],
        ];

        // Konsola yazdır
        $this->displayReport($report);

        // Log'a yaz
        Log::channel('daily')->info('Trendyol Daily Report', $report);

        // E-posta gönder (eğer belirtildiyse)
        if ($email) {
            $this->sendReportEmail($email, $report);
        }
    }

    private function calculateRevenue($orders): float
    {
        $total = 0;
        foreach ($orders as $order) {
            $items = $order->items_data ?? [];
            foreach ($items as $item) {
                $price = $item['price'] ?? $item['salePrice'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                $total += $price * $quantity;
            }
        }
        return round($total, 2);
    }

    private function displayReport(array $report): void
    {
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Hesap', $report['account_name']],
                ['Tarih', $report['date']],
                ['Sağlık Skoru', $report['health_score'] . '/100 (' . $report['health_label'] . ')'],
                ['Bugünkü Sipariş', $report['orders']['today']],
                ['Dünkü Sipariş', $report['orders']['yesterday']],
                ['Haftalık Sipariş', $report['orders']['weekly']],
                ['Sipariş Değişim', ($report['orders']['change'] >= 0 ? '+' : '') . $report['orders']['change'] . '%'],
                ['Bugünkü Ciro', number_format($report['revenue']['today'], 2) . ' ₺'],
                ['Haftalık Ciro', number_format($report['revenue']['weekly'], 2) . ' ₺'],
                ['BuyBox Kazanım', $report['buybox']['rate'] . '%'],
                ['BuyBox Kayıp', $report['buybox']['lost']],
                ['Kritik Uyarı', $report['alerts']['critical']],
                ['Uyarı', $report['alerts']['warning']],
                ['Fırsat', $report['alerts']['opportunity']],
            ]
        );
    }

    private function sendReportEmail(string $email, array $report): void
    {
        try {
            // Basit metin e-posta gönder
            Mail::raw($this->formatEmailBody($report), function ($message) use ($email, $report) {
                $message->to($email)
                    ->subject("📊 Trendyol Günlük Rapor - {$report['account_name']} - {$report['date']}");
            });

            $this->info("📧 Rapor {$email} adresine gönderildi.");
        } catch (\Exception $e) {
            $this->error("❌ E-posta gönderilemedi: " . $e->getMessage());
        }
    }

    private function formatEmailBody(array $report): string
    {
        return <<<EOT
🚀 TRENDYOL GÜNLÜK PERFORMANS RAPORU
=====================================

📅 Tarih: {$report['date']}
🏪 Hesap: {$report['account_name']}

📊 SAĞLIK SKORU
Puan: {$report['health_score']}/100 ({$report['health_label']})

📦 SİPARİŞLER
• Bugün: {$report['orders']['today']} sipariş
• Dün: {$report['orders']['yesterday']} sipariş
• Haftalık: {$report['orders']['weekly']} sipariş
• Değişim: {$report['orders']['change']}%

💰 CİRO
• Bugün: {$report['revenue']['today']} ₺
• Haftalık: {$report['revenue']['weekly']} ₺

🎯 BUYBOX DURUMU
• Kazanım Oranı: {$report['buybox']['rate']}%
• Kazanılan: {$report['buybox']['won']}
• Kaybedilen: {$report['buybox']['lost']}
• Riskli: {$report['buybox']['risk']}

⚠️ UYARILAR
• Kritik: {$report['alerts']['critical']}
• Uyarı: {$report['alerts']['warning']}
• Fırsat: {$report['alerts']['opportunity']}

---
Bu rapor CastMart Trendyol Intelligence System tarafından otomatik oluşturulmuştur.
EOT;
    }
}
