<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $userToken;
    private $category;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->userToken = JWTAuth::fromUser($this->user);
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 100
        ]);
    }

    public function test_can_get_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'cart' => [
                        'id',
                        'user_id',
                        'created_at',
                        'updated_at'
                    ],
                    'items' => [
                        '*' => [
                            'id',
                            'cart_id',
                            'product_id',
                            'quantity',
                            'created_at',
                            'updated_at',
                            'product'
                        ]
                    ],
                    'total'
                ]
            ])
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_cannot_get_cart_without_token()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_can_add_product_to_cart()
    {
        $cartData = [
            'product_id' => $this->product->id,
            'quantity' => 3
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/cart/add', $cartData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ürün sepete başarıyla eklendi'
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);
    }

    public function test_cannot_add_product_with_invalid_data()
    {
        $cartData = [
            'product_id' => 999, // Non-existent product
            'quantity' => 0 // Invalid quantity
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/cart/add', $cartData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Doğrulama hatası'
            ]);
    }

    public function test_cannot_add_product_with_insufficient_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5
        ]);

        $cartData = [
            'product_id' => $product->id,
            'quantity' => 10
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/cart/add', $cartData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Yetersiz stok'
            ]);
    }

    public function test_can_update_cart_item_quantity()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $updateData = [
            'product_id' => $this->product->id,
            'quantity' => 5
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->putJson('/api/cart/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sepet başarıyla güncellendi'
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    public function test_cannot_update_cart_item_with_insufficient_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $updateData = [
            'product_id' => $product->id,
            'quantity' => 10
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->putJson('/api/cart/update', $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Yetersiz stok'
            ]);
    }

    public function test_cannot_update_nonexistent_cart_item()
    {
        $updateData = [
            'product_id' => 999,
            'quantity' => 5
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->putJson('/api/cart/update', $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Doğrulama hatası'
            ]);
    }

    public function test_can_remove_product_from_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson("/api/cart/remove/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ürün sepetten başarıyla çıkarıldı'
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    public function test_cannot_remove_nonexistent_product_from_cart()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson('/api/cart/remove/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Sepet bulunamadı'
            ]);
    }

    public function test_can_clear_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson('/api/cart/clear');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sepet başarıyla temizlendi'
            ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_can_clear_empty_cart()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson('/api/cart/clear');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sepet başarıyla temizlendi'
            ]);
    }

    public function test_cannot_access_cart_endpoints_without_token()
    {
        // Test add endpoint
        $response = $this->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
        $response->assertStatus(401);

        // Test update endpoint
        $response = $this->putJson('/api/cart/update', [
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
        $response->assertStatus(401);

        // Test remove endpoint
        $response = $this->deleteJson("/api/cart/remove/{$this->product->id}");
        $response->assertStatus(401);

        // Test clear endpoint
        $response = $this->deleteJson('/api/cart/clear');
        $response->assertStatus(401);
    }

    public function test_cart_automatically_created_for_user()
    {
        $cartData = [
            'product_id' => $this->product->id,
            'quantity' => 1
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/cart/add', $cartData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id
        ]);
    }

    public function test_cart_total_calculation()
    {
        $product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 50.00,
            'stock_quantity' => 100
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/cart');

        $response->assertStatus(200);

        $expectedTotal = ($this->product->price * 2) + ($product2->price * 1);
        $this->assertEquals($expectedTotal, $response->json('data.total'));
    }
}
