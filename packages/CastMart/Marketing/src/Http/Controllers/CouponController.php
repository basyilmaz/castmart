<?php

namespace CastMart\Marketing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Marketing\Models\Coupon;
use CastMart\Marketing\Models\CouponUsage;
use CastMart\Marketing\Services\MarketingService;

class CouponController extends Controller
{
    protected MarketingService $marketingService;

    public function __construct(MarketingService $marketingService)
    {
        $this->marketingService = $marketingService;
    }

    /**
     * Marketing dashboard
     */
    public function dashboard()
    {
        $couponStats = $this->marketingService->getCouponStats();
        $loyaltyStats = $this->marketingService->getLoyaltyStats();
        
        $recentCoupons = Coupon::latest()->take(5)->get();
        $topCoupons = Coupon::orderByDesc('used_count')->take(5)->get();

        return view('castmart-marketing::admin.dashboard', compact(
            'couponStats', 'loyaltyStats', 'recentCoupons', 'topCoupons'
        ));
    }

    /**
     * Kupon listesi
     */
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->search, fn($q, $s) => $q->where('code', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status === 'active', fn($q) => $q->active())
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('castmart-marketing::admin.coupons.index', compact('coupons'));
    }

    /**
     * Yeni kupon formu
     */
    public function create()
    {
        $types = config('castmart-marketing.coupons.types');
        return view('castmart-marketing::admin.coupons.create', compact('types'));
    }

    /**
     * Kupon kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:coupons,code',
            'type' => 'required|string|in:percentage,fixed,free_shipping,buy_x_get_y',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $coupon = $this->marketingService->createCoupon($request->all());

        return redirect()
            ->route('admin.marketing.coupons.index')
            ->with('success', "Kupon oluşturuldu: {$coupon->code}");
    }

    /**
     * Kupon detay
     */
    public function show($id)
    {
        $coupon = Coupon::with(['usages.customer', 'usages.order'])->findOrFail($id);
        
        $stats = [
            'total_usage' => $coupon->usages->count(),
            'total_discount' => $coupon->usages->sum('discount_amount'),
            'unique_customers' => $coupon->usages->unique('customer_id')->count(),
        ];

        return view('castmart-marketing::admin.coupons.show', compact('coupon', 'stats'));
    }

    /**
     * Kupon düzenle
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $types = config('castmart-marketing.coupons.types');
        
        return view('castmart-marketing::admin.coupons.edit', compact('coupon', 'types'));
    }

    /**
     * Kupon güncelle
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:percentage,fixed,free_shipping,buy_x_get_y',
            'value' => 'required|numeric|min:0',
        ]);

        $coupon->update($request->only([
            'name', 'description', 'type', 'value',
            'min_order_amount', 'max_discount_amount',
            'usage_limit', 'usage_per_customer',
            'starts_at', 'expires_at', 'is_active',
            'first_order_only', 'free_shipping',
        ]));

        return redirect()
            ->route('admin.marketing.coupons.show', $id)
            ->with('success', 'Kupon güncellendi');
    }

    /**
     * Kupon sil
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()
            ->route('admin.marketing.coupons.index')
            ->with('success', 'Kupon silindi');
    }

    /**
     * Kupon aktif/pasif toggle
     */
    public function toggle($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => $coupon->is_active ? 'Kupon aktif edildi' : 'Kupon pasif edildi',
        ]);
    }

    /**
     * Kupon kodu oluştur (AJAX)
     */
    public function generateCode()
    {
        return response()->json([
            'code' => Coupon::generateCode(),
        ]);
    }

    /**
     * API: Kupon doğrula
     */
    public function validateApi(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $customer = auth('customer')->user();
        
        $result = $this->marketingService->validateCoupon(
            $request->code,
            $customer,
            $request->subtotal
        );

        return response()->json($result);
    }
}
