<?php

namespace CastMart\Param\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Param\Payment\Param;

class ParamController extends Controller
{
    protected Param $param;

    public function __construct()
    {
        $this->param = new Param();
    }

    /**
     * Ödeme sayfasına yönlendir
     */
    public function redirect()
    {
        $cart = cart()->getCart();
        
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Sepetiniz boş.');
        }

        return view('param::redirect', [
            'cart' => $cart,
            'installments' => config('param.installments.enabled'),
            'maxInstallment' => config('param.installments.max_installment'),
        ]);
    }

    /**
     * 3D Ödeme başlat
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'card_holder' => 'required|string|max:100',
            'card_number' => 'required|string|min:16|max:19',
            'expiry_month' => 'required|string|size:2',
            'expiry_year' => 'required|string|size:2',
            'cvv' => 'required|string|min:3|max:4',
            'installment' => 'nullable|integer|min:1|max:12',
        ]);

        $result = $this->param->initiate3DPayment([
            'card_holder' => $request->card_holder,
            'card_number' => $request->card_number,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'cvv' => $request->cvv,
            'installment' => $request->installment ?? 1,
        ]);

        if ($result['success']) {
            if (isset($result['redirect_url'])) {
                return redirect()->away($result['redirect_url']);
            }
            
            if (isset($result['html'])) {
                return response($result['html']);
            }
        }

        return back()->with('error', $result['message'] ?? 'Ödeme başlatılamadı.');
    }

    /**
     * Başarılı callback
     */
    public function callbackSuccess(Request $request)
    {
        $result = $this->param->verifyCallback($request->all());

        if ($result['success']) {
            // Siparişi onayla
            $order = $this->processOrder($result);
            
            if ($order) {
                session()->flash('success', 'Siparişiniz başarıyla oluşturuldu!');
                return redirect()->route('shop.checkout.success');
            }
        }

        session()->flash('error', $result['message'] ?? 'Ödeme doğrulanamadı.');
        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Başarısız callback
     */
    public function callbackFail(Request $request)
    {
        $result = $this->param->verifyCallback($request->all());
        
        $message = $result['message'] ?? 'Ödeme işlemi başarısız oldu.';
        
        session()->flash('error', $message);
        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Taksit seçenekleri API
     */
    public function getInstallments(Request $request)
    {
        $request->validate([
            'bin' => 'required|string|min:6|max:8',
        ]);

        $cart = cart()->getCart();
        $amount = $cart ? $cart->grand_total : 0;

        $installments = $this->param->getInstallmentOptions($request->bin, $amount);

        return response()->json([
            'success' => true,
            'installments' => $installments,
        ]);
    }

    /**
     * BIN sorgulama API
     */
    public function queryBin(Request $request)
    {
        $request->validate([
            'bin' => 'required|string|min:6|max:8',
        ]);

        $result = $this->param->queryBin($request->bin);

        return response()->json($result);
    }

    /**
     * Siparişi işle
     */
    protected function processOrder(array $paymentResult)
    {
        try {
            $cart = cart()->getCart();
            
            if (!$cart) {
                return null;
            }

            // Checkout order create
            $order = app(\Webkul\Checkout\Contracts\Order::class)->create([
                'cart' => $cart,
                'payment_method' => 'param',
                'payment_transaction_id' => $paymentResult['transaction_id'] ?? null,
            ]);

            // Cart temizle
            cart()->deActivateCart();

            return $order;

        } catch (\Exception $e) {
            \Log::error('Param order process error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
