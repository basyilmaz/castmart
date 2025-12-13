<?php

namespace CastMart\Trendyol\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class TrendyolScraperService
{
    private int $timeout = 15; // 30'dan 15'e düşürüldü
    private int $rateLimitDelay = 1; // 3'ten 1'e düşürüldü

    /**
     * Trendyol URL'den ürün bilgilerini çek
     */
    public function extractProductFromUrl(string $url): ?array
    {
        try {
            $html = $this->fetchPage($url);
            if (!$html) {
                return null;
            }

            $crawler = new Crawler($html);

            // JSON-LD structured data'yı çek
            $jsonLdScript = $crawler->filter('script[type="application/ld+json"]')->first();
            $productData = [];

            if ($jsonLdScript->count() > 0) {
                $jsonLd = json_decode($jsonLdScript->text(), true);
                if (isset($jsonLd['@type']) && $jsonLd['@type'] === 'Product') {
                    $productData = $this->parseJsonLd($jsonLd);
                }
            }

            // HTML'den ek bilgiler çek
            $productData = array_merge($productData, $this->parseHtml($crawler));

            return $productData;

        } catch (\Exception $e) {
            Log::error('Trendyol scraping failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Ürün yorumlarını çek
     */
    public function scrapeReviews(string $productUrl, int $page = 1): array
    {
        try {
            $productId = $this->extractProductId($productUrl);
            if (!$productId) {
                return [];
            }

            // Trendyol review API (public)
            $reviewUrl = "https://public-mdc.trendyol.com/discovery-web-websfxsocialreviewrating-santral/reviews/{$productId}";
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->get($reviewUrl, [
                'page' => $page,
                'size' => 20,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $reviews = [];

            foreach ($data['productReviews']['content'] ?? [] as $review) {
                $reviews[] = [
                    'rating' => $review['rate'] ?? 0,
                    'comment' => $review['comment'] ?? '',
                    'reviewer_name' => $review['userFullName'] ?? 'Anonim',
                    'has_purchase' => $review['isLegalTextAccepted'] ?? false,
                    'review_date' => isset($review['createdDate']) 
                        ? date('Y-m-d', $review['createdDate'] / 1000) 
                        : null,
                    'helpful_count' => $review['likeCount'] ?? 0,
                ];
            }

            sleep($this->rateLimitDelay);

            return [
                'reviews' => $reviews,
                'total' => $data['productReviews']['totalElements'] ?? 0,
                'average_rating' => $data['ratingScore']['averageRating'] ?? 0,
                'total_count' => $data['ratingScore']['totalCount'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Trendyol review scraping failed', [
                'url' => $productUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Sayfayı HTTP ile çek
     */
    private function fetchPage(string $url): ?string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
        ])
        ->timeout($this->timeout)
        ->get($url);

        sleep($this->rateLimitDelay);

        if ($response->successful()) {
            return $response->body();
        }

        return null;
    }

    /**
     * JSON-LD verisini parse et
     */
    private function parseJsonLd(array $jsonLd): array
    {
        return [
            'name' => $jsonLd['name'] ?? null,
            'description' => $jsonLd['description'] ?? null,
            'sku' => $jsonLd['sku'] ?? null,
            'brand' => $jsonLd['brand']['name'] ?? null,
            'price' => isset($jsonLd['offers']['price']) ? (float)$jsonLd['offers']['price'] : null,
            'currency' => $jsonLd['offers']['priceCurrency'] ?? 'TRY',
            'availability' => $jsonLd['offers']['availability'] ?? null,
            'image_url' => $jsonLd['image'] ?? null,
            'rating' => $jsonLd['aggregateRating']['ratingValue'] ?? null,
            'review_count' => $jsonLd['aggregateRating']['reviewCount'] ?? null,
        ];
    }

    /**
     * HTML'den ek bilgileri parse et
     */
    private function parseHtml(Crawler $crawler): array
    {
        $data = [];

        // Ürün görselleri
        try {
            $images = [];
            $crawler->filter('.gallery-modal-content img, .product-slide img')->each(function ($img) use (&$images) {
                $src = $img->attr('src') ?? $img->attr('data-src');
                if ($src && !in_array($src, $images)) {
                    $images[] = $src;
                }
            });
            $data['images'] = $images;
        } catch (\Exception $e) {
            $data['images'] = [];
        }

        // Kategori
        try {
            $breadcrumbs = [];
            $crawler->filter('.breadcrumb-wrapper a, .product-detail-breadcrumb a')->each(function ($a) use (&$breadcrumbs) {
                $text = trim($a->text());
                if ($text && $text !== 'Trendyol') {
                    $breadcrumbs[] = $text;
                }
            });
            $data['category_path'] = $breadcrumbs;
        } catch (\Exception $e) {
            $data['category_path'] = [];
        }

        // Ürün özellikleri
        try {
            $attributes = [];
            $crawler->filter('.detail-attr-container .detail-attr-item, .product-feature-list li')->each(function ($item) use (&$attributes) {
                $text = trim($item->text());
                if (str_contains($text, ':')) {
                    [$key, $value] = explode(':', $text, 2);
                    $attributes[trim($key)] = trim($value);
                }
            });
            $data['attributes'] = $attributes;
        } catch (\Exception $e) {
            $data['attributes'] = [];
        }

        return $data;
    }

    /**
     * URL'den product ID çıkart
     */
    private function extractProductId(string $url): ?string
    {
        // https://www.trendyol.com/brand/product-name-p-123456789
        if (preg_match('/-p-(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
