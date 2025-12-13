<?php

namespace CastMart\Shipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Shipping\Services\ShippingService;
use CastMart\Shipping\Models\Shipment;
use Webkul\Sales\Models\Order;

class ShippingController extends Controller
{
    protected ShippingService $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Kargo yönetimi ana sayfası
     */
    public function index(Request $request)
    {
        $shipments = Shipment::with('order')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->carrier, fn($q, $carrier) => $q->where('carrier_code', $carrier))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('receiver_name', 'like', "%{$search}%")
                        ->orWhere('receiver_phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => Shipment::count(),
            'pending' => Shipment::pending()->count(),
            'in_transit' => Shipment::inTransit()->count(),
            'delivered' => Shipment::delivered()->count(),
        ];

        $carriers = $this->shippingService->getEnabledCarriers();

        return view('castmart-shipping::admin.index', compact('shipments', 'stats', 'carriers'));
    }

    /**
     * Sipariş için gönderi oluştur
     */
    public function createFromOrder(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Zaten gönderi oluşturulmuş mu kontrol et
        $existing = Shipment::where('order_id', $orderId)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Bu sipariş için zaten kargo oluşturulmuş.',
                'tracking_number' => $existing->tracking_number,
            ]);
        }

        $carrierCode = $request->input('carrier', config('castmart-shipping.default_carrier'));
        $result = $this->shippingService->createShipmentFromOrder($order, $carrierCode);

        return response()->json($result);
    }

    /**
     * Manuel gönderi oluştur
     */
    public function create(Request $request)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_address' => 'required|string',
            'receiver_city' => 'required|string|max:50',
            'carrier' => 'nullable|string',
        ]);

        $result = $this->shippingService->createShipment(
            $request->all(),
            $request->input('carrier')
        );

        return response()->json($result);
    }

    /**
     * Kargo takip
     */
    public function track(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'carrier' => 'nullable|string',
        ]);

        $result = $this->shippingService->trackShipment(
            $request->tracking_number,
            $request->carrier
        );

        return response()->json($result);
    }

    /**
     * Kargo detay sayfası
     */
    public function show($id)
    {
        $shipment = Shipment::with('order')->findOrFail($id);
        
        // Güncel takip bilgisini al
        $tracking = $this->shippingService->trackShipment($shipment->tracking_number, $shipment->carrier_code);

        return view('castmart-shipping::admin.show', compact('shipment', 'tracking'));
    }

    /**
     * Kargo etiketi indir (PDF)
     */
    public function downloadLabel($id)
    {
        $shipment = Shipment::findOrFail($id);
        
        $label = $this->shippingService->getLabel($shipment->tracking_number, $shipment->carrier_code);

        if (!$label) {
            return back()->with('error', 'Kargo etiketi oluşturulamadı.');
        }

        return response($label)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="kargo_etiket_' . $shipment->tracking_number . '.pdf"');
    }

    /**
     * Kargo etiketi görüntüle
     */
    public function viewLabel($id)
    {
        $shipment = Shipment::findOrFail($id);
        
        $label = $this->shippingService->getLabel($shipment->tracking_number, $shipment->carrier_code);

        if (!$label) {
            return back()->with('error', 'Kargo etiketi oluşturulamadı.');
        }

        return response($label)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="kargo_etiket_' . $shipment->tracking_number . '.pdf"');
    }

    /**
     * Gönderiyi iptal et
     */
    public function cancel($id)
    {
        $shipment = Shipment::findOrFail($id);

        if ($shipment->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Teslim edilmiş gönderi iptal edilemez.',
            ]);
        }

        $success = $this->shippingService->cancelShipment($shipment->tracking_number, $shipment->carrier_code);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Gönderi iptal edildi.' : 'Gönderi iptal edilemedi.',
        ]);
    }

    /**
     * Toplu takip güncelleme
     */
    public function bulkUpdateTracking(Request $request)
    {
        $shipments = Shipment::inTransit()->get();
        $updated = 0;

        foreach ($shipments as $shipment) {
            $result = $this->shippingService->trackShipment($shipment->tracking_number, $shipment->carrier_code);
            if ($result['success']) {
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} gönderi güncellendi.",
        ]);
    }

    /**
     * Fiyat karşılaştırması
     */
    public function compareRates(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'weight' => 'nullable|numeric',
            'desi' => 'nullable|numeric',
        ]);

        $rates = $this->shippingService->compareRates($request->all());

        return response()->json([
            'success' => true,
            'rates' => $rates,
        ]);
    }

    /**
     * Aktif kargo firmalarını getir (API)
     */
    public function getCarriers()
    {
        $carriers = [];
        
        foreach ($this->shippingService->getEnabledCarriers() as $carrier) {
            $carriers[] = [
                'code' => $carrier->getCode(),
                'name' => $carrier->getName(),
            ];
        }

        return response()->json([
            'success' => true,
            'carriers' => $carriers,
        ]);
    }
}
