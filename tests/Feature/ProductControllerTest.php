<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $admin;
    private $user;
    private $adminToken;
    private $userToken;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->userToken = JWTAuth::fromUser($this->user);
        $this->category = Category::factory()->create();
    }

    public function test_can_get_all_products_with_pagination()
    {
        Product::factory()->count(25)->create(['category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/products?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock_quantity',
                            'category_id',
                            'created_at',
                            'updated_at',
                            'category'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(10, $response->json('data.products'));
        $this->assertEquals(25, $response->json('data.pagination.total'));
    }

    public function test_can_filter_products_by_category()
    {
        $category2 = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson("/api/products?category_id={$this->category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(3, $response->json('data.products'));
    }

    public function test_can_filter_products_by_price_range()
    {
        Product::factory()->create(['price' => 50, 'category_id' => $this->category->id]);
        Product::factory()->create(['price' => 100, 'category_id' => $this->category->id]);
        Product::factory()->create(['price' => 150, 'category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/products?min_price=75&max_price=125');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(1, $response->json('data.products'));
    }

    public function test_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'iPhone 15', 'category_id' => $this->category->id]);
        Product::factory()->create(['name' => 'Samsung Galaxy', 'category_id' => $this->category->id]);
        Product::factory()->create(['name' => 'MacBook Pro', 'category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/products?search=iPhone');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(1, $response->json('data.products'));
    }

    public function test_cannot_get_products_without_token()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ]
            ])
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_cannot_get_nonexistent_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
    }

    public function test_admin_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 50,
            'category_id' => $this->category->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ürün başarıyla oluşturuldu'
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 50,
            'category_id' => $this->category->id
        ]);
    }

    public function test_user_cannot_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 50,
            'category_id' => $this->category->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_create_product_with_invalid_data()
    {
        $productData = [
            'name' => 'T', // Too short
            'price' => -10, // Negative price
            'stock_quantity' => -5, // Negative stock
            'category_id' => 999 // Non-existent category
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Doğrulama hatası'
            ]);
    }

    public function test_admin_can_update_product()
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $updateData = [
            'name' => 'Updated Product',
            'price' => 149.99,
            'stock_quantity' => 75
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ürün başarıyla güncellendi'
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'stock_quantity' => 75
        ]);
    }

    public function test_user_cannot_update_product()
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $updateData = [
            'name' => 'Updated Product',
            'price' => 149.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_update_nonexistent_product()
    {
        $updateData = [
            'name' => 'Updated Product',
            'price' => 149.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/products/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ürün başarıyla silindi'
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_user_cannot_delete_product()
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_delete_nonexistent_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
    }
}
