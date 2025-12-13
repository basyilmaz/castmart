<?php

namespace CastMart\Shipping\Services;

use CastMart\Shipping\Contracts\CarrierInterface;
use CastMart\Shipping\Carriers\ArasKargo;
use CastMart\Shipping\Carriers\MngKargo;
use CastMart\Shipping\Carriers\YurticiKargo;
use CastMart\Shipping\Models\Shipment;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected array $carriers = [];

    public function __construct()
    {
        $this->registerCarriers();
    }

    /**
     * Kargo firmalarını kaydet
     */
    protected function registerCarriers(): void
    {
        $this->carriers = [
            'aras' => new ArasKargo(),
            'mng' => new MngKargo(),
            'yurtici' => new YurticiKargo(),
        ];
    }

    /**
     * Belirtilen kargo firmasını getir
     */
    public function getCarrier(string $code): ?CarrierInterface
    {
        return $this->carriers[$code] ?? null;
    }

    /**
     * Tüm aktif kargo firmalarını getir
     */
    public function getEnabledCarriers(): array
    {
        return array_filter($this->carriers, fn($carrier) => $carrier->isEnabled());
    }

    /**
     * Varsayılan kargo firmasını getir
     */
    public function getDefaultCarrier(): ?CarrierInterface
    {
        $defaultCode = config('castmart-shipping.default_carrier', 'aras');
        return $this->getCarrier($defaultCode);
    }

    /**
     * Gönderi oluştur
     */
    public function createShipment(array $orderData, ?string $carrierCode = null): array
    {
        $carrier = $carrierCode 
            ? $this->getCarrier($carrierCode) 
            : $this->getDefaultCarrier();

        if (!$carrier) {
            return [
                'success' => false,
                'message' => 'Kargo firması bulunamadı',
            ];
        }

        if (!$carrier->isEnabled()) {
            return [
                'success' => false,
                'message' => $carrier->getName() . ' şu anda aktif değil',
            ];
        }

        // Gönderi oluştur
        $result = $carrier->createShipment($orderData);

        if ($result['success']) {
            // Veritabanına kaydet
            $shipment = Shipment::create([
                'order_id' => $orderData['order_id'] ?? null,
                'carrier_code' => $carrier->getCode(),
                'tracking_number' => $result['tracking_number'],
                'cargo_key' => $result['cargo_key'] ?? null,
                'status' => 'created',
                'receiver_name' => $orderData['receiver_name'],
                'receiver_phone' => $orderData['receiver_phone'],
                'receiver_address' => $orderData['receiver_address'],
                'receiver_city' => $orderData['receiver_city'],
                'receiver_district' => $orderData['receiver_district'] ?? null,
                'piece_count' => $orderData['piece_count'] ?? 1,
                'weight' => $orderData['weight'] ?? null,
                'desi' => $orderData['desi'] ?? null,
                'is_cod' => $orderData['is_cod'] ?? false,
                'cod_amount' => $orderData['cod_amount'] ?? null,
                'metadata' => $orderData,
            ]);

            $result['shipment_id'] = $shipment->id;
        }

        return $result;
    }

    /**
     * Sipariş için gönderi oluştur
     */
    public function createShipmentFromOrder($order, ?string $carrierCode = null): array
    {
        $shippingAddress = $order->shipping_address;
        
        $orderData = [
            'order_id' => $order->id,
            'order_number' => $order->increment_id,
            'invoice_number' => $order->invoices->first()?->increment_id ?? $order->increment_id,
            
            // Alıcı bilgileri
            'receiver_name' => $shippingAddress->first_name . ' ' . $shippingAddress->last_name,
            'receiver_phone' => $shippingAddress->phone,
            'receiver_address' => $shippingAddress->address1,
            'receiver_city' => $shippingAddress->city,
            'receiver_district' => $shippingAddress->address2 ?? '',
            
            // Paket bilgileri
            'piece_count' => 1,
            'weight' => $this->calculateOrderWeight($order),
            'description' => 'Sipariş #' . $order->increment_id,
            
            // Kapıda ödeme
            'is_cod' => $order->payment->method === 'cashondelivery',
            'cod_amount' => $order->payment->method === 'cashondelivery' ? $order->grand_total : 0,
        ];

        return $this->createShipment($orderData, $carrierCode);
    }

    /**
     * Kargo takip
     */
    public function trackShipment(string $trackingNumber, ?string $carrierCode = null): array
    {
        // Önce veritabanından bul
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        $carrier = $carrierCode 
            ? $this->getCarrier($carrierCode) 
            : ($shipment ? $this->getCarrier($shipment->carrier_code) : $this->getDefaultCarrier());

        if (!$carrier) {
            return [
                'success' => false,
                'message' => 'Kargo firması bulunamadı',
            ];
        }

        $result = $carrier->trackShipment($trackingNumber);

        // Durumu güncelle
        if ($result['success'] && $shipment) {
            $shipment->update([
                'status' => $result['status'],
                'last_location' => $result['last_location'] ?? null,
                'delivered_at' => $result['status'] === 'delivered' ? now() : null,
            ]);
        }

        return $result;
    }

    /**
     * Kargo etiketi al
     */
    public function getLabel(string $trackingNumber, ?string $carrierCode = null): ?string
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        $carrier = $carrierCode 
            ? $this->getCarrier($carrierCode) 
            : ($shipment ? $this->getCarrier($shipment->carrier_code) : $this->getDefaultCarrier());

        if (!$carrier) {
            return null;
        }

        return $carrier->getLabel($trackingNumber);
    }

    /**
     * Gönderiyi iptal et
     */
    public function cancelShipment(string $trackingNumber, ?string $carrierCode = null): bool
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        $carrier = $carrierCode 
            ? $this->getCarrier($carrierCode) 
            : ($shipment ? $this->getCarrier($shipment->carrier_code) : $this->getDefaultCarrier());

        if (!$carrier) {
            return false;
        }

        $success = $carrier->cancelShipment($trackingNumber);

        if ($success && $shipment) {
            $shipment->update(['status' => 'cancelled']);
        }

        return $success;
    }

    /**
     * Fiyat karşılaştırması yap
     */
    public function compareRates(array $data): array
    {
        $rates = [];

        foreach ($this->getEnabledCarriers() as $carrier) {
            try {
                $rate = $carrier->calculateRate($data);
                $rates[$carrier->getCode()] = [
                    'carrier' => $carrier->getName(),
                    'code' => $carrier->getCode(),
                    'rate' => $rate,
                    'formatted' => number_format($rate, 2) . ' ₺',
                ];
            } catch (\Exception $e) {
                Log::warning("Rate calculation failed for {$carrier->getCode()}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // En ucuzdan pahalıya sırala
        uasort($rates, fn($a, $b) => $a['rate'] <=> $b['rate']);

        return $rates;
    }

    /**
     * Sipariş ağırlığını hesapla
     */
    protected function calculateOrderWeight($order): float
    {
        $totalWeight = 0;

        foreach ($order->items as $item) {
            $weight = $item->product->weight ?? 0.5; // kg
            $totalWeight += $weight * $item->qty_ordered;
        }

        return max(0.5, $totalWeight); // Minimum 0.5 kg
    }
}
