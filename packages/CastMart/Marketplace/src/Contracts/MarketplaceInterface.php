<?php

namespace CastMart\Marketplace\Contracts;

interface MarketplaceInterface
{
    /**
     * Pazaryeri bağlantısını test et
     * @return bool|array true başarılı ise, hata detayları array olarak döndürülür
     */
    public function testConnection(): bool|array;

    /**
     * Ürünü pazaryerine gönder
     */
    public function createProduct(array $productData): array;

    /**
     * Ürün bilgilerini güncelle
     */
    public function updateProduct(string $externalId, array $productData): array;

    /**
     * Stok ve fiyat güncelle
     * @param array|string $items Toplu güncelleme için array, tekli için barcode string
     * @param int $stock Tekli güncelleme için stok miktarı
     * @param float $price Tekli güncelleme için fiyat
     */
    public function updateInventory(array|string $items, int $stock = 0, float $price = 0): array|bool;

    /**
     * Siparişleri çek
     */
    public function getOrders(array $filters = []): array;

    /**
     * Sipariş durumunu güncelle
     */
    public function updateOrderStatus(string $orderId, string $status, array $data = []): bool;

    /**
     * Kargo takip numarası gönder
     */
    public function sendTrackingNumber(string $orderId, string $trackingNumber, string $cargoProvider): bool;

    /**
     * Müşteri sorularını çek
     */
    public function getQuestions(array $filters = []): array;

    /**
     * Soruyu cevapla
     */
    public function answerQuestion(string $questionId, string $answer): bool;

    /**
     * Kategorileri çek
     */
    public function getCategories(): array;

    /**
     * Markaları çek
     */
    public function getBrands(): array;
}
