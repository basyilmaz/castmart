<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwoFactorAuthService;

class TwoFactorController extends Controller
{
    protected TwoFactorAuthService $twoFactor;

    public function __construct(TwoFactorAuthService $twoFactor)
    {
        $this->twoFactor = $twoFactor;
    }

    // =====================
    // CUSTOMER METHODS
    // =====================

    /**
     * 2FA ayarları sayfası (müşteri)
     */
    public function settings()
    {
        $customer = auth()->guard('customer')->user();
        
        return view('shop::customers.account.two-factor.settings', [
            'enabled' => $customer->two_factor_enabled ?? false,
        ]);
    }

    /**
     * 2FA etkinleştirme başlat (müşteri)
     */
    public function enable()
    {
        $customer = auth()->guard('customer')->user();
        
        if ($customer->two_factor_enabled) {
            return redirect()->route('customer.two-factor.settings')
                ->with('info', '2FA zaten etkinleştirilmiş.');
        }

        $secretKey = $this->twoFactor->generateSecretKey();
        session()->put('2fa_setup_secret', $secretKey);

        $qrCode = $this->twoFactor->getQRCodeSvg($customer->email, $secretKey);

        return view('shop::customers.account.two-factor.enable', [
            'qrCode' => $qrCode,
            'secretKey' => $secretKey,
        ]);
    }

    /**
     * 2FA etkinleştirmeyi onayla (müşteri)
     */
    public function confirmEnable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $customer = auth()->guard('customer')->user();
        $secretKey = session()->get('2fa_setup_secret');

        if (!$secretKey) {
            return back()->with('error', 'Oturum süresi doldu. Lütfen tekrar deneyin.');
        }

        if ($this->twoFactor->enableForCustomer($customer, $secretKey, $request->code)) {
            session()->forget('2fa_setup_secret');
            
            $recoveryCodes = json_decode(decrypt($customer->fresh()->two_factor_recovery_codes), true);
            
            return view('shop::customers.account.two-factor.recovery-codes', [
                'recoveryCodes' => $recoveryCodes,
            ]);
        }

        return back()->with('error', 'Geçersiz doğrulama kodu.');
    }

    /**
     * 2FA devre dışı bırak (müşteri)
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $customer = auth()->guard('customer')->user();

        if (!password_verify($request->password, $customer->password)) {
            return back()->with('error', 'Geçersiz şifre.');
        }

        $this->twoFactor->disable($customer);
        $this->twoFactor->clearTwoFactorSession('customer', $customer->id);

        return redirect()->route('customer.two-factor.settings')
            ->with('success', '2FA başarıyla devre dışı bırakıldı.');
    }

    /**
     * 2FA doğrulama sayfası (login sonrası)
     */
    public function challenge()
    {
        $customer = auth()->guard('customer')->user();

        if (!$customer || !$customer->two_factor_enabled) {
            return redirect()->route('shop.customer.account.profile.index');
        }

        if ($this->twoFactor->isTwoFactorConfirmed('customer', $customer->id)) {
            return redirect()->route('shop.customer.account.profile.index');
        }

        return view('shop::customers.account.two-factor.challenge');
    }

    /**
     * 2FA doğrulama (login sonrası)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $customer = auth()->guard('customer')->user();
        $secretKey = decrypt($customer->two_factor_secret);

        // Normal OTP kontrolü
        if ($this->twoFactor->verifyCode($secretKey, $request->code)) {
            $this->twoFactor->confirmTwoFactor('customer', $customer->id);
            return redirect()->intended(route('shop.customer.account.profile.index'));
        }

        // Recovery kod kontrolü
        if ($this->twoFactor->verifyRecoveryCode($customer, $request->code)) {
            $this->twoFactor->confirmTwoFactor('customer', $customer->id);
            return redirect()->intended(route('shop.customer.account.profile.index'))
                ->with('warning', 'Kurtarma kodu kullanıldı. Lütfen yeni kodlar oluşturun.');
        }

        return back()->with('error', 'Geçersiz doğrulama kodu.');
    }

    /**
     * Yeni recovery kodları oluştur
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $customer = auth()->guard('customer')->user();

        if (!password_verify($request->password, $customer->password)) {
            return back()->with('error', 'Geçersiz şifre.');
        }

        $codes = $this->twoFactor->generateRecoveryCodes($customer, 'customer');

        return view('shop::customers.account.two-factor.recovery-codes', [
            'recoveryCodes' => $codes,
        ]);
    }

    // =====================
    // ADMIN METHODS
    // =====================

    /**
     * 2FA ayarları (admin)
     */
    public function adminSettings()
    {
        $admin = auth()->guard('admin')->user();
        
        return view('admin::settings.two-factor.settings', [
            'enabled' => $admin->two_factor_enabled ?? false,
        ]);
    }

    /**
     * 2FA etkinleştir (admin)
     */
    public function adminEnable()
    {
        $admin = auth()->guard('admin')->user();
        
        if ($admin->two_factor_enabled) {
            return redirect()->route('admin.two-factor.settings')
                ->with('info', '2FA zaten etkinleştirilmiş.');
        }

        $secretKey = $this->twoFactor->generateSecretKey();
        session()->put('2fa_admin_setup_secret', $secretKey);

        $qrCode = $this->twoFactor->getQRCodeSvg($admin->email, $secretKey);

        return view('admin::settings.two-factor.enable', [
            'qrCode' => $qrCode,
            'secretKey' => $secretKey,
        ]);
    }

    /**
     * 2FA etkinleştirmeyi onayla (admin)
     */
    public function adminConfirmEnable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $admin = auth()->guard('admin')->user();
        $secretKey = session()->get('2fa_admin_setup_secret');

        if (!$secretKey) {
            return back()->with('error', 'Oturum süresi doldu.');
        }

        if ($this->twoFactor->enableForAdmin($admin, $secretKey, $request->code)) {
            session()->forget('2fa_admin_setup_secret');
            
            $recoveryCodes = json_decode(decrypt($admin->fresh()->two_factor_recovery_codes), true);
            
            return view('admin::settings.two-factor.recovery-codes', [
                'recoveryCodes' => $recoveryCodes,
            ]);
        }

        return back()->with('error', 'Geçersiz doğrulama kodu.');
    }

    /**
     * 2FA devre dışı bırak (admin)
     */
    public function adminDisable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = auth()->guard('admin')->user();

        if (!password_verify($request->password, $admin->password)) {
            return back()->with('error', 'Geçersiz şifre.');
        }

        $this->twoFactor->disable($admin);
        $this->twoFactor->clearTwoFactorSession('admin', $admin->id);

        return redirect()->route('admin.two-factor.settings')
            ->with('success', '2FA devre dışı bırakıldı.');
    }

    /**
     * 2FA doğrulama sayfası (admin login sonrası)
     */
    public function adminChallenge()
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin || !$admin->two_factor_enabled) {
            return redirect()->route('admin.dashboard.index');
        }

        if ($this->twoFactor->isTwoFactorConfirmed('admin', $admin->id)) {
            return redirect()->route('admin.dashboard.index');
        }

        return view('admin::settings.two-factor.challenge');
    }

    /**
     * 2FA doğrula (admin)
     */
    public function adminVerify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $admin = auth()->guard('admin')->user();
        $secretKey = decrypt($admin->two_factor_secret);

        if ($this->twoFactor->verifyCode($secretKey, $request->code)) {
            $this->twoFactor->confirmTwoFactor('admin', $admin->id);
            return redirect()->intended(route('admin.dashboard.index'));
        }

        if ($this->twoFactor->verifyRecoveryCode($admin, $request->code)) {
            $this->twoFactor->confirmTwoFactor('admin', $admin->id);
            return redirect()->intended(route('admin.dashboard.index'))
                ->with('warning', 'Kurtarma kodu kullanıldı.');
        }

        return back()->with('error', 'Geçersiz doğrulama kodu.');
    }
}
