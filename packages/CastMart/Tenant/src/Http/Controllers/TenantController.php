<?php

namespace CastMart\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantSubscription;
use CastMart\Tenant\Facades\Tenant as TenantFacade;

class TenantController extends Controller
{
    /**
     * Tenant listesi (Admin)
     */
    public function index(Request $request)
    {
        $tenants = Tenant::with('owner')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->plan, fn($q, $p) => $q->where('plan', $p))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('subdomain', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::active()->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'trial' => Tenant::where('trial_ends_at', '>', now())->count(),
        ];

        return view('castmart-tenant::admin.index', compact('tenants', 'stats'));
    }

    /**
     * Tenant oluşturma formu (Admin)
     */
    public function create()
    {
        $plans = config('castmart-tenant.plans');
        return view('castmart-tenant::admin.create', compact('plans'));
    }

    /**
     * Tenant oluştur (Admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:tenants,subdomain|alpha_dash',
            'plan' => 'required|string|in:starter,professional,enterprise',
            'owner_id' => 'nullable|exists:admins,id',
        ]);

        $tenant = TenantFacade::create($request->all());

        return redirect()
            ->route('admin.tenants.show', $tenant->id)
            ->with('success', 'Tenant başarıyla oluşturuldu.');
    }

    /**
     * Tenant detayı (Admin)
     */
    public function show($id)
    {
        $tenant = Tenant::with(['owner', 'users', 'subscriptions'])->findOrFail($id);
        $stats = TenantFacade::getUsageStats($tenant);
        
        return view('castmart-tenant::admin.show', compact('tenant', 'stats'));
    }

    /**
     * Tenant düzenleme formu (Admin)
     */
    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        $plans = config('castmart-tenant.plans');
        
        return view('castmart-tenant::admin.edit', compact('tenant', 'plans'));
    }

    /**
     * Tenant güncelle (Admin)
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|alpha_dash|unique:tenants,subdomain,' . $id,
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $id,
            'plan' => 'required|string|in:starter,professional,enterprise',
        ]);

        $tenant->update($request->only(['name', 'subdomain', 'domain', 'plan', 'settings']));

        TenantFacade::clearTenantCache($tenant);

        return redirect()
            ->route('admin.tenants.show', $id)
            ->with('success', 'Tenant başarıyla güncellendi.');
    }

    /**
     * Tenant sil (Admin)
     */
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        TenantFacade::clearTenantCache($tenant);
        
        $tenant->delete();

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant başarıyla silindi.');
    }

    /**
     * Tenant'ı askıya al (Admin)
     */
    public function suspend(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        TenantFacade::suspend($tenant, $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Tenant askıya alındı.',
        ]);
    }

    /**
     * Tenant'ı aktif et (Admin)
     */
    public function activate($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        TenantFacade::activate($tenant);

        return response()->json([
            'success' => true,
            'message' => 'Tenant aktif edildi.',
        ]);
    }

    /**
     * Tenant olarak giriş yap (Admin - Impersonate)
     */
    public function impersonate($id, Request $request)
    {
        $tenant = Tenant::findOrFail($id);
        
        // Session'a impersonate bilgisini kaydet
        session(['impersonating_tenant' => $tenant->id]);
        session(['original_admin_id' => auth('admin')->id()]);

        return redirect($tenant->url . '/admin')
            ->with('success', $tenant->name . ' olarak giriş yapıldı.');
    }

    /**
     * Kayıt formu göster (Public)
     */
    public function showRegistrationForm()
    {
        $plans = config('castmart-tenant.plans');
        return view('castmart-tenant::register', compact('plans'));
    }

    /**
     * Yeni tenant kaydı (Public)
     */
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:tenants,subdomain|alpha_dash',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'plan' => 'required|string|in:starter,professional,enterprise',
        ]);

        // Admin kullanıcı oluştur
        $admin = \Webkul\User\Models\Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => 1,
            'role_id' => 1, // Default role
        ]);

        // Tenant oluştur
        $tenant = TenantFacade::create([
            'name' => $request->company_name,
            'subdomain' => $request->subdomain,
            'plan' => $request->plan,
            'owner_id' => $admin->id,
        ]);

        // Abonelik oluştur (trial)
        TenantFacade::createSubscription($tenant, $request->plan);

        return redirect($tenant->url . '/admin/login')
            ->with('success', 'Hesabınız oluşturuldu! Lütfen giriş yapın.');
    }
}
