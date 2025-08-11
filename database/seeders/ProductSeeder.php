<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Hosting Hizmetleri
            [
                'name' => 'Başlangıç Hosting Paketi',
                'description' => 'Küçük web siteleri için uygun başlangıç hosting paketi',
                'price' => 29.99,
                'stock_quantity' => 100,
                'category_id' => 1,
            ],
            [
                'name' => 'Kurumsal Hosting Paketi',
                'description' => 'Büyük işletmeler için gelişmiş hosting çözümü',
                'price' => 89.99,
                'stock_quantity' => 50,
                'category_id' => 1,
            ],
            [
                'name' => 'VPS Sunucu',
                'description' => 'Sanal özel sunucu hizmeti',
                'price' => 149.99,
                'stock_quantity' => 30,
                'category_id' => 1,
            ],
            [
                'name' => 'Dedicated Sunucu',
                'description' => 'Özel sunucu hizmeti yüksek performans için',
                'price' => 299.99,
                'stock_quantity' => 20,
                'category_id' => 1,
            ],
            [
                'name' => 'Cloud Hosting',
                'description' => 'Bulut tabanlı hosting çözümü',
                'price' => 199.99,
                'stock_quantity' => 40,
                'category_id' => 1,
            ],
            // Domain Hizmetleri
            [
                'name' => '.com Alan Adı',
                'description' => 'Yıllık .com alan adı kayıt hizmeti',
                'price' => 14.99,
                'stock_quantity' => 500,
                'category_id' => 2,
            ],
            [
                'name' => '.com.tr Alan Adı',
                'description' => 'Türkiye için .com.tr alan adı kayıt hizmeti',
                'price' => 19.99,
                'stock_quantity' => 300,
                'category_id' => 2,
            ],
            [
                'name' => '.net Alan Adı',
                'description' => 'Yıllık .net alan adı kayıt hizmeti',
                'price' => 16.99,
                'stock_quantity' => 400,
                'category_id' => 2,
            ],
            [
                'name' => '.org Alan Adı',
                'description' => 'Organizasyonlar için .org alan adı',
                'price' => 18.99,
                'stock_quantity' => 250,
                'category_id' => 2,
            ],
            [
                'name' => 'Domain Transfer Hizmeti',
                'description' => 'Mevcut alan adınızı bize transfer edin',
                'price' => 9.99,
                'stock_quantity' => 1000,
                'category_id' => 2,
            ],
            // Yazılım Ürünleri
            [
                'name' => 'E-Ticaret Yazılımı',
                'description' => 'Online mağaza kurulumu için tam kapsamlı yazılım',
                'price' => 599.99,
                'stock_quantity' => 25,
                'category_id' => 3,
            ],
            [
                'name' => 'CRM Yazılımı',
                'description' => 'Müşteri ilişkileri yönetimi yazılımı',
                'price' => 399.99,
                'stock_quantity' => 35,
                'category_id' => 3,
            ],
            [
                'name' => 'Muhasebe Yazılımı',
                'description' => 'İşletme muhasebe işlemleri için yazılım',
                'price' => 299.99,
                'stock_quantity' => 45,
                'category_id' => 3,
            ],
            [
                'name' => 'Web Tasarım Yazılımı',
                'description' => 'Profesyonel web sitesi tasarım yazılımı',
                'price' => 199.99,
                'stock_quantity' => 60,
                'category_id' => 3,
            ],
            [
                'name' => 'Güvenlik Yazılımı',
                'description' => 'Siber güvenlik ve koruma yazılımı',
                'price' => 149.99,
                'stock_quantity' => 80,
                'category_id' => 3,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
