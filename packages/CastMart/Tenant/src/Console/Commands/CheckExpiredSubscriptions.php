<?php

namespace CastMart\Tenant\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantSubscription;
use CastMart\Tenant\Facades\Tenant as TenantFacade;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'tenant:check-subscriptions';
    protected $description = 'Süresi dolan abonelikleri kontrol et ve bildirim gönder';

    public function handle(): int
    {
        $this->info('Abonelik kontrolü başlıyor...');

        // Süresi dolanları askıya al
        $expired = Tenant::where('status', 'active')
            ->where('trial_ends_at', '<', now())
            ->where('subscription_ends_at', '<', now())
            ->get();

        foreach ($expired as $tenant) {
            $this->warn("Askıya alınıyor: {$tenant->name}");
            
            TenantFacade::suspend($tenant, 'Abonelik süresi doldu');
            
            // TODO: Email bildirim gönder
            Log::info('Tenant askıya alındı - abonelik süresi doldu', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ]);
        }

        $this->info("Askıya alınan: {$expired->count()} tenant");

        // Süresi yaklaşanları bildir (7 gün)
        $expiringSoon = TenantSubscription::expiringSoon(7)
            ->with('tenant')
            ->get();

        foreach ($expiringSoon as $subscription) {
            $this->line("Süre yaklaşıyor: {$subscription->tenant->name} - {$subscription->remaining_days} gün");
            
            // TODO: Email bildirim gönder
        }

        $this->info("Süre yaklaşan: {$expiringSoon->count()} abonelik");
        $this->info('Abonelik kontrolü tamamlandı.');

        return Command::SUCCESS;
    }
}
