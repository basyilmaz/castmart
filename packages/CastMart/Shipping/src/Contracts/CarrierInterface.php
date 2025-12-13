<?php

namespace CastMart\Shipping\Contracts;

interface CarrierInterface
{
    /**
     * Kargo firması kodunu getir
     */
    public function getCode(): string;

    /**
     * Kargo firması adını getir
     */
    public function getName(): string;

    /**
     * Kargo firması aktif mi?
     */
    public function isEnabled(): bool;

    /**
     * Gönderi oluştur
     */
    public function createShipment(array $data): array;

    /**
     * Takip numarası sorgula
     */
    public function trackShipment(string $trackingNumber): array;

    /**
     * Kargo etiketi oluştur
     */
    public function getLabel(string $trackingNumber): ?string;

    /**
     * Gönderiyi iptal et
     */
    public function cancelShipment(string $trackingNumber): bool;

    /**
     * Fiyat hesapla
     */
    public function calculateRate(array $data): float;
}
