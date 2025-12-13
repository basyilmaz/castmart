<?php

namespace CastMart\Iyzico\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use CastMart\Iyzico\Payment\Iyzico;
use Illuminate\Support\Facades\Log;

class IyzicoController extends Controller
{
    /**
     * Order repository instance
     */
    protected OrderRepository $orderRepository;

    /**
     * iyzico payment instance
     */
    protected Iyzico $iyzico;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->iyzico = new Iyzico();
    }

    /**
     * Redirect to iyzico payment page
     */
    public function redirect()
    {
        $cart = Cart::getCart();

        if (!$cart) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Sepetiniz boş.');
        }

        $paymentForm = $this->iyzico->getPaymentForm();

        if (!$paymentForm) {
            return redirect()->route('shop.checkout.onepage.index')
                ->with('error', 'Ödeme formu oluşturulamadı. Lütfen tekrar deneyin.');
        }

        return view('iyzico::redirect', [
            'checkoutFormContent' => $paymentForm['checkoutFormContent'],
            'paymentPageUrl' => $paymentForm['paymentPageUrl'] ?? null,
        ]);
    }

    /**
     * Handle iyzico callback (3D Secure return)
     */
    public function callback(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            Log::error('iyzico callback: Token not found');
            return redirect()->route('shop.checkout.onepage.index')
                ->with('error', 'Ödeme doğrulanamadı. Token bulunamadı.');
        }

        $result = $this->iyzico->verifyCallback($token);

        if ($result['success']) {
            // Başarılı ödeme - siparişi oluştur
            try {
                $cart = Cart::getCart();
                
                if (!$cart) {
                    // Sepet yok, basket_id'den bul
                    Log::warning('iyzico callback: Cart not found, using basket_id', [
                        'basket_id' => $result['basket_id']
                    ]);
                }

                // Sipariş oluştur
                $order = $this->createOrder($cart, $result);

                if ($order) {
                    // Sepeti temizle
                    Cart::deactivateCart();

                    // Başarı sayfasına yönlendir
                    session()->flash('order', $order);
                    
                    return redirect()->route('shop.checkout.onepage.success')
                        ->with('success', 'Ödemeniz başarıyla tamamlandı.');
                }
            } catch (\Exception $e) {
                Log::error('iyzico order creation error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Başarısız ödeme
        Log::error('iyzico payment failed', [
            'error_code' => $result['error_code'] ?? 'unknown',
            'error_message' => $result['error_message'] ?? 'Bilinmeyen hata',
        ]);

        return redirect()->route('shop.checkout.onepage.index')
            ->with('error', 'Ödeme başarısız: ' . ($result['error_message'] ?? 'Bilinmeyen hata'));
    }

    /**
     * Create order from successful payment
     */
    protected function createOrder($cart, array $paymentResult)
    {
        if (!$cart) {
            return null;
        }

        // Ödeme detaylarını kaydet
        $cart->payment->additional = [
            'iyzico_payment_id' => $paymentResult['payment_id'],
            'iyzico_conversation_id' => $paymentResult['conversation_id'],
            'installment' => $paymentResult['installment'],
            'card_type' => $paymentResult['card_type'],
            'card_association' => $paymentResult['card_association'],
            'card_family' => $paymentResult['card_family'],
            'bin_number' => $paymentResult['bin_number'],
            'last_four_digits' => $paymentResult['last_four_digits'],
        ];
        $cart->payment->save();

        // Siparişi oluştur
        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        // Ödeme durumunu güncelle
        if ($order) {
            $order->payment->update([
                'additional' => $cart->payment->additional,
            ]);
            
            // Invoice oluştur (ödeme tamamlandı)
            $this->createInvoice($order);
        }

        return $order;
    }

    /**
     * Create invoice for paid order
     */
    protected function createInvoice($order)
    {
        $invoiceRepository = app(\Webkul\Sales\Repositories\InvoiceRepository::class);

        $invoiceData = [
            'order_id' => $order->id,
            'items' => [],
        ];

        foreach ($order->items as $item) {
            $invoiceData['items'][$item->id] = ['qty' => $item->qty_ordered];
        }

        try {
            $invoiceRepository->create($invoiceData);
        } catch (\Exception $e) {
            Log::warning('Could not create invoice', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Get installment options (AJAX endpoint)
     */
    public function getInstallments(Request $request)
    {
        $request->validate([
            'bin_number' => 'required|string|size:6',
        ]);

        $cart = Cart::getCart();
        $price = $cart ? $cart->grand_total : 0;

        $result = $this->iyzico->getInstallmentOptions($request->bin_number, $price);

        return response()->json($result);
    }

    /**
     * Process refund (Admin action)
     */
    public function refund(Request $request)
    {
        $request->validate([
            'payment_transaction_id' => 'required|string',
            'price' => 'required|numeric|min:0.01',
        ]);

        $result = $this->iyzico->refund(
            $request->payment_transaction_id,
            $request->price
        );

        return response()->json($result);
    }

    /**
     * Cancel payment (Admin action)
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $result = $this->iyzico->cancel($request->payment_id);

        return response()->json($result);
    }
}
