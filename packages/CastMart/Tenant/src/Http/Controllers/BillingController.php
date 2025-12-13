<?php

namespace CastMart\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantSubscription;
use CastMart\Tenant\Services\BillingService;
use CastMart\Tenant\Facades\Tenant as TenantFacade;

class BillingController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Billing dashboard
     */
    public function index()
    {
        $tenant = TenantFacade::current();
        
        if (!$tenant) {
            return redirect()->route('tenant.register');
        }

        $subscription = $tenant->activeSubscription();
        $invoices = TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $plans = config('castmart-tenant.plans');

        return view('castmart-tenant::billing.index', compact('tenant', 'subscription', 'invoices', 'plans'));
    }

    /**
     * Plan seçim sayfası
     */
    public function plans()
    {
        $tenant = TenantFacade::current();
        $currentPlan = $tenant?->plan ?? 'starter';
        $plans = config('castmart-tenant.plans');

        return view('castmart-tenant::billing.plans', compact('plans', 'currentPlan', 'tenant'));
    }

    /**
     * Checkout başlat
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:starter,professional,enterprise',
            'billing_cycle' => 'required|string|in:monthly,yearly',
        ]);

        $tenant = TenantFacade::current();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant bulunamadı',
            ], 404);
        }

        $result = $this->billingService->createCheckoutSession(
            $tenant,
            $request->plan,
            $request->billing_cycle
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * iyzico callback
     */
    public function callback(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return redirect()->route('tenant.billing.index')
                ->with('error', 'Geçersiz ödeme tokeni');
        }

        $result = $this->billingService->handleCallback($token);

        if ($result['success']) {
            return redirect()->route('tenant.billing.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('tenant.billing.index')
            ->with('error', $result['message']);
    }

    /**
     * Abonelik iptal
     */
    public function cancel(Request $request)
    {
        $tenant = TenantFacade::current();
        $subscription = $tenant?->activeSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif abonelik bulunamadı',
            ], 404);
        }

        $success = $this->billingService->cancelSubscription(
            $subscription,
            $request->input('reason')
        );

        return response()->json([
            'success' => $success,
            'message' => $success 
                ? 'Abonelik iptal edildi. Mevcut dönem sonuna kadar hizmet devam edecektir.' 
                : 'İptal işlemi başarısız',
        ]);
    }

    /**
     * Plan değiştir
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:starter,professional,enterprise',
        ]);

        $tenant = TenantFacade::current();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant bulunamadı',
            ], 404);
        }

        $result = $this->billingService->changePlan($tenant, $request->plan);

        return response()->json($result);
    }

    /**
     * Fatura görüntüle
     */
    public function invoice($id)
    {
        $subscription = TenantSubscription::findOrFail($id);
        
        // Yetki kontrolü
        $tenant = TenantFacade::current();
        if (!$tenant || $subscription->tenant_id !== $tenant->id) {
            abort(403);
        }

        $invoice = $this->billingService->generateInvoice($subscription);

        return view('castmart-tenant::billing.invoice', compact('invoice', 'subscription'));
    }

    /**
     * Fatura PDF indir
     */
    public function downloadInvoice($id)
    {
        $subscription = TenantSubscription::findOrFail($id);
        
        // Yetki kontrolü
        $tenant = TenantFacade::current();
        if (!$tenant || $subscription->tenant_id !== $tenant->id) {
            abort(403);
        }

        $invoice = $this->billingService->generateInvoice($subscription);

        // Basit PDF - gerçek uygulamada dompdf veya snappy kullanılabilir
        $html = view('castmart-tenant::billing.invoice-pdf', compact('invoice', 'subscription'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $invoice['invoice_number'] . '.html"');
    }
}
