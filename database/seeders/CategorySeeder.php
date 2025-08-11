<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hosting Hizmetleri',
                'description' => 'Web hosting ve sunucu hizmetleri',
            ],
            [
                'name' => 'Domain Hizmetleri',
                'description' => 'Alan adı kayıt ve yönetim hizmetleri',
            ],
            [
                'name' => 'Yazılım Ürünleri',
                'description' => 'Yazılım şirketi ürünleri ve çözümleri',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
