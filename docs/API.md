# CastMart API Documentation

## Overview

CastMart API, e-ticaret platformu için RESTful API servisleri sunar.

**Base URL:** `https://castmart.castintech.com/api`

**Authentication:** Bearer Token (JWT)

---

## Authentication

### Login

```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

### Register

```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

---

## Products

### List Products

```http
GET /api/products
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | Sayfa numarası (default: 1) |
| limit | int | Sayfa başına ürün (default: 20, max: 100) |
| category_id | int | Kategori filtresi |
| search | string | Arama terimi |
| sort | string | Sıralama (price_asc, price_desc, newest, popular) |
| min_price | float | Minimum fiyat |
| max_price | float | Maksimum fiyat |

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "sku": "PROD-001",
            "name": "Ürün Adı",
            "url_key": "urun-adi",
            "price": 199.99,
            "special_price": 149.99,
            "in_stock": true,
            "images": [
                {"url": "https://..."}
            ]
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 20,
        "total": 200
    }
}
```

### Get Product Detail

```http
GET /api/products/{id}
```

---

## Cart

### Get Cart

```http
GET /api/cart
Authorization: Bearer {token}
```

### Add to Cart

```http
POST /api/cart/add
Authorization: Bearer {token}
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2,
    "selected_configurable_option": null
}
```

### Update Cart Item

```http
PUT /api/cart/update/{item_id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "quantity": 3
}
```

### Remove from Cart

```http
DELETE /api/cart/remove/{item_id}
Authorization: Bearer {token}
```

---

## Checkout

### Get Checkout

```http
GET /api/checkout
Authorization: Bearer {token}
```

### Apply Coupon

```http
POST /api/checkout/coupon
Authorization: Bearer {token}
Content-Type: application/json

{
    "code": "INDIRIM20"
}
```

### Place Order

```http
POST /api/checkout/save-order
Authorization: Bearer {token}
Content-Type: application/json

{
    "billing": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "5551234567",
        "address1": "Adres satırı 1",
        "city": "İstanbul",
        "postcode": "34000",
        "country": "TR"
    },
    "shipping": {
        "method": "flatrate_flatrate"
    },
    "payment": {
        "method": "iyzico"
    }
}
```

---

## Orders

### List Orders

```http
GET /api/customer/orders
Authorization: Bearer {token}
```

### Get Order Detail

```http
GET /api/customer/orders/{id}
Authorization: Bearer {token}
```

---

## Marketing API

### Validate Coupon

```http
POST /api/marketing/coupons/validate
Content-Type: application/json

{
    "code": "INDIRIM20",
    "subtotal": 500.00
}
```

**Response:**
```json
{
    "valid": true,
    "coupon": {
        "code": "INDIRIM20",
        "type": "percentage",
        "value": 20
    },
    "discount": 100.00,
    "message": "Kupon uygulandı: %20 indirim"
}
```

### Loyalty Account

```http
GET /api/marketing/loyalty/account
Authorization: Bearer {token}
```

**Response:**
```json
{
    "total_points": 1500,
    "available_points": 1200,
    "tier": "silver",
    "tier_name": "Gümüş",
    "points_value": 12.00,
    "next_tier": {
        "name": "Altın",
        "points_needed": 3500
    }
}
```

### Redeem Points

```http
POST /api/marketing/loyalty/redeem
Authorization: Bearer {token}
Content-Type: application/json

{
    "points": 500
}
```

---

## Chatbot

### Send Message

```http
POST /api/marketing/chatbot
Content-Type: application/json

{
    "message": "Siparişimi takip etmek istiyorum",
    "session_id": "unique_session_id"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Sipariş takibi için lütfen sipariş numaranızı giriniz.",
    "intent": {
        "type": "order_inquiry"
    },
    "suggestions": [
        "Siparişlerimi göster",
        "Son siparişim nerede?"
    ]
}
```

---

## Trendyol Integration

### Sync Products

```http
POST /api/admin/trendyol/sync/products
Authorization: Bearer {admin_token}
```

### Get Trendyol Orders

```http
GET /api/admin/trendyol/orders
Authorization: Bearer {admin_token}
```

### Answer Question

```http
POST /api/admin/trendyol/questions/{id}/answer
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "answer": "Ürün satışta değildir."
}
```

---

## Health Check

```http
GET /api/health
```

**Response:**
```json
{
    "status": "healthy",
    "timestamp": "2025-12-13T15:30:00Z",
    "app": "CastMart",
    "version": "1.0.0",
    "environment": "production",
    "database": "connected",
    "cache": "working"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
    "error": "Bad Request",
    "message": "Geçersiz istek"
}
```

### 401 Unauthorized
```json
{
    "error": "Unauthorized",
    "message": "Yetkilendirme gerekli"
}
```

### 403 Forbidden
```json
{
    "error": "Forbidden",
    "message": "Bu işlem için yetkiniz yok"
}
```

### 404 Not Found
```json
{
    "error": "Not Found",
    "message": "Kaynak bulunamadı"
}
```

### 422 Validation Error
```json
{
    "error": "Validation Error",
    "message": "Doğrulama hatası",
    "errors": {
        "email": ["Email alanı gereklidir"],
        "password": ["Şifre en az 8 karakter olmalıdır"]
    }
}
```

### 429 Too Many Requests
```json
{
    "error": "Too Many Requests",
    "message": "Çok fazla istek gönderdiniz. Lütfen bekleyin.",
    "retry_after": 60
}
```

### 500 Internal Server Error
```json
{
    "error": "Internal Server Error",
    "message": "Sunucu hatası oluştu"
}
```

---

## Rate Limiting

| Endpoint | Limit |
|----------|-------|
| API (genel) | 60 istek/dakika |
| Auth (login) | 5 deneme/dakika |
| Search | 30 arama/dakika |
| Checkout | 10 işlem/dakika |

Rate limit bilgisi response header'larında döner:
- `X-RateLimit-Limit`: Maksimum istek
- `X-RateLimit-Remaining`: Kalan istek

---

## Webhooks

### İyzico Payment Callback

```http
POST /api/iyzico/callback
```

### Trendyol Order Webhook

```http
POST /api/webhooks/trendyol/orders
```

---

*API Version: 1.0.0*
*Last Updated: 2025-12-13*
