<?php

namespace CastMart\Marketing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Marketing\Models\LoyaltyAccount;
use CastMart\Marketing\Models\LoyaltyTransaction;
use Webkul\Customer\Repositories\CustomerRepository;

class LoyaltyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Loyalty dashboard - Admin Panel
     */
    public function index()
    {
        $stats = [
            'total_members' => LoyaltyAccount::count(),
            'total_points_issued' => LoyaltyAccount::sum('lifetime_points'),
            'total_points_available' => LoyaltyAccount::sum('available_points'),
            'total_points_redeemed' => LoyaltyTransaction::where('type', 'redeem')->sum('points') * -1,
            'bronze_members' => LoyaltyAccount::where('tier', 'bronze')->count(),
            'silver_members' => LoyaltyAccount::where('tier', 'silver')->count(),
            'gold_members' => LoyaltyAccount::where('tier', 'gold')->count(),
            'platinum_members' => LoyaltyAccount::where('tier', 'platinum')->count(),
        ];

        $recentTransactions = LoyaltyTransaction::with('loyaltyAccount.customer')
            ->latest()
            ->take(10)
            ->get();

        return view('castmart-marketing::loyalty.index', compact('stats', 'recentTransactions'));
    }

    /**
     * Üye listesi - Admin Panel
     */
    public function members(Request $request)
    {
        $query = LoyaltyAccount::with('customer');

        // Arama
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tier filtresi
        if ($request->has('tier') && $request->tier) {
            $query->where('tier', $request->tier);
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'lifetime_points');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $members = $query->paginate(20);

        return view('castmart-marketing::loyalty.members', compact('members'));
    }

    /**
     * Üye detayı - Admin Panel
     */
    public function memberDetail($id)
    {
        $account = LoyaltyAccount::with(['customer', 'transactions' => function ($q) {
            $q->latest()->take(50);
        }])->findOrFail($id);

        return view('castmart-marketing::loyalty.member-detail', compact('account'));
    }

    /**
     * Puan ayarlama - Admin Panel
     */
    public function adjustPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $account = LoyaltyAccount::findOrFail($id);
        $points = (int) $request->points;

        if ($points > 0) {
            $account->addPoints($points, 'admin_adjustment', $request->reason);
            $message = "{$points} puan başarıyla eklendi.";
        } else {
            $pointsToRedeem = abs($points);
            if ($pointsToRedeem > $account->available_points) {
                return back()->withErrors(['points' => 'Yeterli puan bulunmuyor.']);
            }
            $account->redeemPoints($pointsToRedeem, 'Admin düzeltmesi: ' . $request->reason);
            $message = "{$pointsToRedeem} puan başarıyla düşüldü.";
        }

        return back()->with('success', $message);
    }

    /**
     * Tüm işlemler - Admin Panel
     */
    public function transactions(Request $request)
    {
        $query = LoyaltyTransaction::with('loyaltyAccount.customer');

        // Tip filtresi
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Tarih filtresi
        if ($request->has('from') && $request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to') && $request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transactions = $query->latest()->paginate(50);

        return view('castmart-marketing::loyalty.transactions', compact('transactions'));
    }

    // ========================================
    // API Methods (Storefront)
    // ========================================

    /**
     * Müşteri hesabı - API
     */
    public function account(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Yetkisiz erişim'], 401);
        }

        $account = LoyaltyAccount::where('customer_id', $customer->id)->first();

        if (!$account) {
            // Hesap yoksa oluştur
            $account = LoyaltyAccount::create([
                'customer_id' => $customer->id,
                'total_points' => 0,
                'available_points' => 0,
                'lifetime_points' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'available_points' => $account->available_points,
                'lifetime_points' => $account->lifetime_points,
                'tier' => $account->tier,
                'tier_name' => $account->tier_name,
                'next_tier' => $account->next_tier,
                'points_to_next_tier' => $account->points_to_next_tier,
                'points_value' => $account->points_value,
                'referral_code' => $account->referral_code,
            ],
        ]);
    }

    /**
     * Müşteri işlemleri - API
     */
    public function myTransactions(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Yetkisiz erişim'], 401);
        }

        $account = LoyaltyAccount::where('customer_id', $customer->id)->first();

        if (!$account) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $transactions = $account->transactions()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'type' => $t->type,
                    'points' => $t->points,
                    'balance_after' => $t->balance_after,
                    'description' => $t->description,
                    'created_at' => $t->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Puan kullan - API
     */
    public function redeem(Request $request)
    {
        $customer = $request->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Yetkisiz erişim'], 401);
        }

        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $account = LoyaltyAccount::where('customer_id', $customer->id)->first();

        if (!$account) {
            return response()->json(['error' => 'Sadakat hesabı bulunamadı'], 404);
        }

        $points = (int) $request->points;

        if ($points > $account->available_points) {
            return response()->json(['error' => 'Yeterli puan bulunmuyor'], 400);
        }

        $minRedeem = config('castmart-marketing.loyalty.min_redeem_points', 100);
        if ($points < $minRedeem) {
            return response()->json(['error' => "Minimum {$minRedeem} puan kullanabilirsiniz"], 400);
        }

        $transaction = $account->redeemPoints($points, 'Alışverişte puan kullanımı');

        $rate = config('castmart-marketing.loyalty.points_to_currency_rate', 100);
        $discount = $points / $rate;

        return response()->json([
            'success' => true,
            'message' => "{$points} puan kullanıldı. ₺{$discount} indirim uygulandı.",
            'data' => [
                'points_used' => $points,
                'discount_amount' => $discount,
                'remaining_points' => $account->available_points,
            ],
        ]);
    }
}
