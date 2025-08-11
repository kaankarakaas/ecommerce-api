# E-Ticaret API

Laravel ile geliştirilmiş kapsamlı bir e-ticaret API'si. JWT kimlik doğrulama ve Swagger dokümantasyonu ile birlikte gelir.

## Özellikler

- 🔐 JWT Kimlik Doğrulama
- 📚 Swagger/OpenAPI Dokümantasyonu
- 👥 Kullanıcı Yönetimi (Admin/User rolleri)
- 📂 Kategori Yönetimi
- 🛍️ Ürün Yönetimi (filtreleme, sayfalama, arama)
- 🛒 Sepet Yönetimi
- 📦 Sipariş Yönetimi
- 💾 PostgreSQL Veritabanı Desteği
- 🔒 Güvenlik Önlemleri (doğrulama, yetkilendirme)
- 🐳 Docker Desteği

## Teknik Gereksinimler

- PHP 8.0+
- PostgreSQL 13+
- Composer
- Laravel 12.x

## Kurulum Seçenekleri

### 1. Docker ile Kurulum (Önerilen)

Docker kullanarak hızlı kurulum için:

```bash
# Projeyi klonlayın
git clone https://github.com/kaankarakaas/ecommerce-api.git
cd ecommerce-api

# Docker container'larını başlatın
docker-compose up

# API'ye erişim
# http://localhost:8000
# Swagger Dokümantasyonu: http://localhost:8000/api/documentation
```

### 2. Manuel Kurulum

#### Proje Kurulum Adımları

1. **Projeyi klonlayın:**
```bash
git clone https://github.com/kaankarakaas/ecommerce-api.git
cd ecommerce-api
```

2. **Bağımlılıkları yükleyin:**
```bash
composer install
```

3. **Environment dosyasını oluşturun:**
```bash
copy .env.example .env
```

4. **Environment değişkenlerini düzenleyin:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_api
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

5. **Application key oluşturun:**
```bash
php artisan key:generate
```

6. **JWT secret key oluşturun:**
```bash
php artisan jwt:secret
```

7. **Veritabanı migration'larını çalıştırın:**
```bash
php artisan migrate
```

8. **Örnek verileri yükleyin:**
```bash
php artisan db:seed
```

9. **Swagger dokümantasyonunu oluşturun:**
```bash
php artisan l5-swagger:generate
```

10. **Geliştirme sunucusunu başlatın:**
```bash
php artisan serve
```

## Veritabanı Kurulum Talimatları

### PostgreSQL Kurulumu

1. **PostgreSQL'i indirin ve kurun:**
   - Windows: https://www.postgresql.org/download/windows/
   - macOS: `brew install postgresql`
   - Ubuntu: `sudo apt-get install postgresql postgresql-contrib`

2. **Veritabanı oluşturun:**
```sql
CREATE DATABASE ecommerce_api;
```

3. **Kullanıcı oluşturun (opsiyonel):**
```sql
CREATE USER ecommerce_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE ecommerce_api TO ecommerce_user;
```

4. **Laravel migration'larını çalıştırın:**
```bash
php artisan migrate
```

5. **Örnek verileri yükleyin:**
```bash
php artisan db:seed
```

### SQL Dump Kullanımı

Veritabanını hızlıca kurmak için `database/ecommerce_api.sql` dosyasını kullanabilirsiniz:

```bash
# PostgreSQL'e bağlanın
psql -U postgres

# SQL dump dosyasını çalıştırın
\i database/ecommerce_api.sql
```

## API Endpoint Listesi ve Kullanımı

### 🔐 Kimlik Doğrulama Endpoint'leri

#### 1. Kullanıcı Kaydı
```http
POST /api/register
Content-Type: application/json

{
    "name": "Ahmet Yılmaz",
    "email": "ahmet@example.com",
    "password": "sifre123"
}
```

#### 2. Kullanıcı Girişi
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@test.com",
    "password": "user123"
}
```

#### 3. Kullanıcı Profili Görüntüleme
```http
GET /api/profile
Authorization: Bearer {JWT_TOKEN}
```

#### 4. Kullanıcı Profili Güncelleme
```http
PUT /api/profile
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "name": "Yeni İsim",
    "email": "yeni@email.com"
}
```

#### 5. Kullanıcı Çıkışı
```http
POST /api/logout
Authorization: Bearer {JWT_TOKEN}
```

### 📂 Kategori Endpoint'leri

#### 1. Tüm Kategorileri Listele
```http
GET /api/categories
Authorization: Bearer {JWT_TOKEN}
```

#### 2. Yeni Kategori Oluştur (Admin)
```http
POST /api/categories
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "name": "Yeni Kategori",
    "description": "Kategori açıklaması"
}
```

#### 3. Kategori Güncelle (Admin)
```http
PUT /api/categories/{id}
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "name": "Güncellenmiş Kategori",
    "description": "Güncellenmiş açıklama"
}
```

#### 4. Kategori Sil (Admin)
```http
DELETE /api/categories/{id}
Authorization: Bearer {JWT_TOKEN}
```

### 🛍️ Ürün Endpoint'leri

#### 1. Ürünleri Listele (Filtreleme ve Sayfalama)
```http
GET /api/products?page=1&limit=20&category_id=1&min_price=10&max_price=100&search=hosting
Authorization: Bearer {JWT_TOKEN}
```

**Filtreleme Parametreleri:**
- `page`: Sayfa numarası (varsayılan: 1)
- `limit`: Sayfa başına kayıt sayısı (varsayılan: 20)
- `category_id`: Kategori filtresi
- `min_price`: Minimum fiyat
- `max_price`: Maksimum fiyat
- `search`: Ürün adında arama

#### 2. Tek Ürün Detayı
```http
GET /api/products/{id}
Authorization: Bearer {JWT_TOKEN}
```

#### 3. Yeni Ürün Ekle (Admin)
```http
POST /api/products
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "name": "Yeni Ürün",
    "description": "Ürün açıklaması",
    "price": 99.99,
    "stock_quantity": 50,
    "category_id": 1
}
```

#### 4. Ürün Güncelle (Admin)
```http
PUT /api/products/{id}
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "name": "Güncellenmiş Ürün",
    "description": "Güncellenmiş açıklama",
    "price": 89.99,
    "stock_quantity": 40,
    "category_id": 1
}
```

#### 5. Ürün Sil (Admin)
```http
DELETE /api/products/{id}
Authorization: Bearer {JWT_TOKEN}
```

### 🛒 Sepet Endpoint'leri

#### 1. Sepeti Görüntüle
```http
GET /api/cart
Authorization: Bearer {JWT_TOKEN}
```

#### 2. Sepete Ürün Ekle
```http
POST /api/cart/add
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2
}
```

#### 3. Sepet Ürün Miktarı Güncelle
```http
PUT /api/cart/update
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 3
}
```

#### 4. Sepetten Ürün Çıkar
```http
DELETE /api/cart/remove/{product_id}
Authorization: Bearer {JWT_TOKEN}
```

#### 5. Sepeti Temizle
```http
DELETE /api/cart/clear
Authorization: Bearer {JWT_TOKEN}
```

### 📦 Sipariş Endpoint'leri

#### 1. Sipariş Oluştur
```http
POST /api/orders
Authorization: Bearer {JWT_TOKEN}
```

#### 2. Kullanıcının Siparişlerini Listele
```http
GET /api/orders
Authorization: Bearer {JWT_TOKEN}
```

#### 3. Sipariş Detayı
```http
GET /api/orders/{id}
Authorization: Bearer {JWT_TOKEN}
```

## Örnek Request/Response'lar

### 🔐 Kimlik Doğrulama Örnekleri

#### 1. Kullanıcı Kaydı

**Request:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mehmet Demir",
    "email": "mehmet@example.com",
    "password": "guvenli123"
  }'
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Kullanıcı başarıyla kaydedildi",
    "data": {
        "user": {
            "id": 3,
            "name": "Mehmet Demir",
            "email": "mehmet@example.com",
            "role": "user"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzM0MDQ4MDAwLCJleHAiOjE3MzQwNTE2MDAsIm5iZiI6MTczNDA0ODAwMCwianRpIjoiVGVzdFRva2VuIiwic3ViIjozLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0YWIwMTBiZDQiLCJyb2xlIjoidXNlciJ9.abc123..."
    }
}
```

#### 2. Kullanıcı Girişi

**Request:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@test.com",
    "password": "user123"
  }'
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Giriş başarılı",
    "data": {
        "user": {
            "id": 2,
            "name": "Test User",
            "email": "user@test.com",
            "role": "user"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzM0MDQ4MDAwLCJleHAiOjE3MzQwNTE2MDAsIm5iZiI6MTczNDA0ODAwMCwianRpIjoiVGVzdFRva2VuIiwic3ViIjoyLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0YWIwMTBiZDQiLCJyb2xlIjoidXNlciJ9.xyz789..."
    }
}
```

**Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Geçersiz kimlik bilgileri",
    "data": null
}
```

#### 3. Kullanıcı Profili Görüntüleme

**Request:**
```bash
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Profil başarıyla getirildi",
    "data": {
        "user": {
            "id": 2,
            "name": "Test User",
            "email": "user@test.com",
            "role": "user",
            "created_at": "2025-01-11T20:00:00.000000Z",
            "updated_at": "2025-01-11T20:00:00.000000Z"
        }
    }
}
```

### 📂 Kategori Örnekleri

#### 1. Kategorileri Listele

**Request:**
```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Kategoriler başarıyla listelendi",
    "data": [
        {
            "id": 1,
            "name": "Hosting Hizmetleri",
            "description": "Web hosting ve sunucu hizmetleri",
            "created_at": "2025-01-11T20:00:00.000000Z",
            "updated_at": "2025-01-11T20:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Domain Hizmetleri",
            "description": "Alan adı kayıt ve yönetim hizmetleri",
            "created_at": "2025-01-11T20:00:00.000000Z",
            "updated_at": "2025-01-11T20:00:00.000000Z"
        },
        {
            "id": 3,
            "name": "Yazılım Ürünleri",
            "description": "Yazılım şirketi ürünleri ve çözümleri",
            "created_at": "2025-01-11T20:00:00.000000Z",
            "updated_at": "2025-01-11T20:00:00.000000Z"
        }
    ]
}
```

#### 2. Yeni Kategori Oluştur (Admin)

**Request:**
```bash
curl -X POST http://localhost:8000/api/categories \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "SSL Sertifikaları",
    "description": "Güvenlik sertifikaları ve SSL hizmetleri"
  }'
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Kategori başarıyla oluşturuldu",
    "data": {
        "category": {
            "id": 4,
            "name": "SSL Sertifikaları",
            "description": "Güvenlik sertifikaları ve SSL hizmetleri",
            "created_at": "2025-01-11T20:30:00.000000Z",
            "updated_at": "2025-01-11T20:30:00.000000Z"
        }
    }
}
```

### 🛍️ Ürün Örnekleri

#### 1. Ürünleri Listele (Filtreleme ile)

**Request:**
```bash
curl -X GET "http://localhost:8000/api/products?category_id=1&min_price=50&max_price=200&search=hosting" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Ürünler başarıyla listelendi",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Başlangıç Hosting Paketi",
                "description": "Küçük web siteleri için uygun başlangıç hosting paketi",
                "price": "29.99",
                "stock_quantity": 100,
                "category_id": 1,
                "category": {
                    "id": 1,
                    "name": "Hosting Hizmetleri"
                },
                "created_at": "2025-01-11T20:00:00.000000Z",
                "updated_at": "2025-01-11T20:00:00.000000Z"
            },
            {
                "id": 2,
                "name": "Kurumsal Hosting Paketi",
                "description": "Büyük işletmeler için gelişmiş hosting çözümü",
                "price": "89.99",
                "stock_quantity": 50,
                "category_id": 1,
                "category": {
                    "id": 1,
                    "name": "Hosting Hizmetleri"
                },
                "created_at": "2025-01-11T20:00:00.000000Z",
                "updated_at": "2025-01-11T20:00:00.000000Z"
            }
        ],
        "first_page_url": "http://localhost:8000/api/products?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/api/products?page=1",
        "next_page_url": null,
        "path": "http://localhost:8000/api/products",
        "per_page": 20,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    }
}
```

#### 2. Tek Ürün Detayı

**Request:**
```bash
curl -X GET http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Ürün başarıyla getirildi",
    "data": {
        "product": {
            "id": 1,
            "name": "Başlangıç Hosting Paketi",
            "description": "Küçük web siteleri için uygun başlangıç hosting paketi",
            "price": "29.99",
            "stock_quantity": 100,
            "category_id": 1,
            "category": {
                "id": 1,
                "name": "Hosting Hizmetleri",
                "description": "Web hosting ve sunucu hizmetleri"
            },
            "created_at": "2025-01-11T20:00:00.000000Z",
            "updated_at": "2025-01-11T20:00:00.000000Z"
        }
    }
}
```

### 🛒 Sepet Örnekleri

#### 1. Sepete Ürün Ekle

**Request:**
```bash
curl -X POST http://localhost:8000/api/cart/add \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Ürün sepete başarıyla eklendi",
    "data": {
        "cart_item": {
            "id": 1,
            "cart_id": 1,
            "product_id": 1,
            "quantity": 2,
            "product": {
                "id": 1,
                "name": "Başlangıç Hosting Paketi",
                "price": "29.99"
            },
            "created_at": "2025-01-11T20:35:00.000000Z",
            "updated_at": "2025-01-11T20:35:00.000000Z"
        }
    }
}
```

#### 2. Sepeti Görüntüle

**Request:**
```bash
curl -X GET http://localhost:8000/api/cart \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Sepet başarıyla getirildi",
    "data": {
        "cart": {
            "id": 1,
            "user_id": 2,
            "items": [
                {
                    "id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "product": {
                        "id": 1,
                        "name": "Başlangıç Hosting Paketi",
                        "price": "29.99",
                        "description": "Küçük web siteleri için uygun başlangıç hosting paketi"
                    }
                }
            ],
            "total_items": 2,
            "total_amount": "59.98"
        }
    }
}
```

### 📦 Sipariş Örnekleri

#### 1. Sipariş Oluştur

**Request:**
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Sipariş başarıyla oluşturuldu",
    "data": {
        "order": {
            "id": 1,
            "user_id": 2,
            "total_amount": "59.98",
            "status": "pending",
            "order_items": [
                {
                    "id": 1,
                    "order_id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "price": "29.99",
                    "product": {
                        "id": 1,
                        "name": "Başlangıç Hosting Paketi"
                    }
                }
            ],
            "created_at": "2025-01-11T20:40:00.000000Z",
            "updated_at": "2025-01-11T20:40:00.000000Z"
        }
    }
}
```

#### 2. Siparişleri Listele

**Request:**
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Siparişler başarıyla listelendi",
    "data": [
        {
            "id": 1,
            "user_id": 2,
            "total_amount": "59.98",
            "status": "pending",
            "order_items": [
                {
                    "id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "price": "29.99",
                    "product": {
                        "id": 1,
                        "name": "Başlangıç Hosting Paketi"
                    }
                }
            ],
            "created_at": "2025-01-11T20:40:00.000000Z",
            "updated_at": "2025-01-11T20:40:00.000000Z"
        }
    ]
}
```

### ❌ Hata Örnekleri

#### 1. Yetkisiz Erişim (401)

**Response:**
```json
{
    "success": false,
    "message": "Yetkisiz erişim",
    "data": null
}
```

#### 2. Validasyon Hatası (422)

**Response:**
```json
{
    "success": false,
    "message": "Doğrulama hatası",
    "data": null,
    "errors": {
        "email": [
            "Email alanı zorunludur."
        ],
        "password": [
            "Şifre en az 8 karakter olmalıdır."
        ]
    }
}
```

#### 3. Bulunamadı (404)

**Response:**
```json
{
    "success": false,
    "message": "Ürün bulunamadı",
    "data": null
}
```

#### 4. Admin Erişimi Gerekli (403)

**Response:**
```json
{
    "success": false,
    "message": "Admin erişimi gerekli",
    "data": null
}
```

## JWT Kimlik Doğrulama

Bu API JWT (JSON Web Token) tabanlı kimlik doğrulama kullanır. İşte JWT token'ın nasıl kullanılacağı:

### 1. Token Alma
Giriş veya kayıt endpoint'lerinden JWT token alınır:

**Giriş:**
```bash
POST /api/login
{
    "email": "user@test.com",
    "password": "user123"
}
```

**Yanıt:**
```json
{
    "success": true,
    "message": "Giriş başarılı",
    "data": {
        "user": {
            "id": 1,
            "name": "Test User",
            "email": "user@test.com",
            "role": "user"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### 2. Token Kullanımı
Korumalı endpoint'lere erişim için token'ı Authorization header'ında gönderin:

```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### 3. Token Geçerlilik Süresi
- Token'lar 1 saat geçerlidir
- Süre dolduğunda 401 Yetkisiz erişim hatası alırsınız
- Yeni token almak için tekrar giriş yapın

### 4. Postman'de Kullanım
Postman Collection'ında `{{token}}` değişkeni kullanılır:
1. Giriş/Kayıt endpoint'ini çalıştırın
2. Yanıttan token'ı kopyalayın
3. Collection değişkenlerine `token` olarak ekleyin
4. Diğer endpoint'ler otomatik olarak token'ı kullanacaktır

## API Dokümantasyonu

Swagger dokümantasyonuna erişmek için:
```
http://localhost:8000/api/documentation
```

## Test Kullanıcı Bilgileri

### Admin Kullanıcı
- Email: `admin@test.com`
- Şifre: `admin123`

### Normal Kullanıcı
- Email: `user@test.com`
- Şifre: `user123`

## Filtreleme ve Sayfalama

Ürün listesi için desteklenen parametreler:
- `page` - Sayfa numarası (varsayılan: 1)
- `limit` - Sayfa başına kayıt sayısı (varsayılan: 20)
- `category_id` - Kategori filtresi
- `min_price` - Minimum fiyat
- `max_price` - Maksimum fiyat
- `search` - Ürün adında arama

## Güvenlik

- JWT token tabanlı kimlik doğrulama
- Rol tabanlı yetkilendirme (Admin/User)
- Girdi doğrulama ve sanitizasyon
- SQL injection koruması
- XSS koruması
- Şifre hash'leme (bcrypt)

## Veritabanı Yapısı

### Tablolar
- `users` - Kullanıcı bilgileri
- `categories` - Ürün kategorileri
- `products` - Ürün bilgileri
- `carts` - Kullanıcı sepetleri
- `cart_items` - Sepet ürünleri
- `orders` - Siparişler
- `order_items` - Sipariş ürünleri

## Test

```bash
php artisan test
```

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.


## Bonus

Tarayıcınızdan `http://localhost:8000` adresine giderek Konami Asteroids oyununu oynayabilirsiniz. :)
