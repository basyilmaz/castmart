<?php

namespace CastMart\PayTR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use CastMart\PayTR\Payment\PayTR;
use Illuminate\Support\Facades\Log;

class PayTRController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Redirect to PayTR payment page
     */
    public function redirect()
    {
        $cart = Cart::getCart();

        if (!$cart) {
            session()->flash('error', 'Sepet bulunamadı.');
            return redirect()->route('shop.checkout.cart.index');
        }

        $paytr = new PayTR();
        $paymentForm = $paytr->getPaymentForm();

        if (!$paymentForm) {
            session()->flash('error', 'Ödeme formu oluşturulamadı. Lütfen tekrar deneyin.');
            return redirect()->route('shop.checkout.onepage.index');
        }

        return view('paytr::redirect', [
            'iframe_url' => $paymentForm['iframe_url'],
            'token' => $paymentForm['token'],
        ]);
    }

    /**
     * PayTR callback (webhook)
     */
    public function callback(Request $request)
    {
        Log::info('PayTR callback received', $request->all());

        $paytr = new PayTR();
        $result = $paytr->verifyCallback($request->all());

        if (!$result['success']) {
            Log::error('PayTR callback failed', $result);
            return response('FAILED', 400);
        }

        // Siparişi bul veya oluştur
        $cartId = $result['cart_id'];
        
        if ($cartId) {
            try {
                // Cart'ı bul
                $cart = app(\Webkul\Checkout\Repositories\CartRepository::class)->find($cartId);
                
                if ($cart) {
                    // Sepeti aktif yap
                    Cart::setCart($cart);
                    
                    // Siparişi oluştur
                    $order = $this->orderRepository->create(Cart::prepareDataForOrder());
                    
                    // Ödeme detaylarını kaydet
                    $order->update([
                        'payment_method' => 'paytr',
                        'payment_title' => 'PayTR ile Ödeme',
                    ]);

                    // Transaction kaydı
                    if (isset($order->payment)) {
                        $order->payment->update([
                            'additional' => [
                                'merchant_oid' => $result['merchant_oid'],
                                'payment_type' => $result['payment_type'],
                                'installment_count' => $result['installment_count'],
                            ],
                        ]);
                    }

                    // Sepeti deaktive et
                    Cart::deActivateCart();

                    Log::info('PayTR order created', ['order_id' => $order->id]);
                }
            } catch (\Exception $e) {
                Log::error('PayTR order creation error', ['message' => $e->getMessage()]);
            }
        }

        // PayTR'a OK döndür
        return response('OK', 200);
    }

    /**
     * Success page
     */
    public function success(Request $request)
    {
        // Son siparişi bul
        $order = $this->orderRepository
            ->where('customer_id', auth()->guard('customer')->id())
            ->latest()
            ->first();

        if ($order) {
            session()->flash('order', $order);
            return redirect()->route('shop.checkout.onepage.success');
        }

        session()->flash('success', 'Ödemeniz başarıyla tamamlandı.');
        return redirect()->route('shop.checkout.onepage.success');
    }

    /**
     * Fail page
     */
    public function fail(Request $request)
    {
        session()->flash('error', 'Ödeme işlemi başarısız oldu. Lütfen tekrar deneyin.');
        return redirect()->route('shop.checkout.onepage.index');
    }

    /**
     * Get installment options via AJAX
     */
    public function installments(Request $request)
    {
        $request->validate([
            'bin' => 'required|string|min:6|max:8',
            'amount' => 'required|numeric|min:0',
        ]);

        $paytr = new PayTR();
        $result = $paytr->getInstallmentOptions($request->bin, $request->amount);

        return response()->json($result);
    }
}
