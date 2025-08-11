<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $userToken;
    private $category;
    private $product1;
    private $product2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->userToken = JWTAuth::fromUser($this->user);
        $this->category = Category::factory()->create();
        $this->product1 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 50
        ]);
        $this->product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 75.00,
            'stock_quantity' => 30
        ]);
    }

    public function test_can_create_order_from_cart()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product2->id,
            'quantity' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'order' => [
                        'id',
                        'user_id',
                        'total_amount',
                        'status',
                        'created_at',
                        'updated_at',
                        'order_items'
                    ],
                    'order_items'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Sipariş başarıyla oluşturuldu'
            ]);

        $expectedTotal = ($this->product1->price * 2) + ($this->product2->price * 1);
        $this->assertEquals($expectedTotal, $response->json('data.order.total_amount'));

        // Check that order items were created
        $this->assertDatabaseCount('order_items', 2);

        // Check that cart was cleared
        $this->assertDatabaseCount('cart_items', 0);

        // Check that stock was decremented
        $this->assertEquals(48, $this->product1->fresh()->stock_quantity);
        $this->assertEquals(29, $this->product2->fresh()->stock_quantity);
    }

    public function test_cannot_create_order_with_empty_cart()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Sepet boş'
            ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 60 // More than available stock (50)
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => "Ürün için yetersiz stok: {$this->product1->name}"
            ]);
    }

    public function test_cannot_create_order_without_token()
    {
        $response = $this->postJson('/api/orders');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_can_get_user_orders()
    {
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 200.00
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 150.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'price' => 100.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'total_amount',
                        'status',
                        'created_at',
                        'updated_at',
                        'order_items'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_cannot_get_orders_without_token()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_can_get_single_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 200.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'price' => 100.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'total_amount',
                    'status',
                    'created_at',
                    'updated_at',
                    'order_items' => [
                        '*' => [
                            'id',
                            'order_id',
                            'product_id',
                            'quantity',
                            'price',
                            'created_at',
                            'updated_at',
                            'product'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_cannot_get_nonexistent_order()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Sipariş bulunamadı'
            ]);
    }

    public function test_cannot_get_other_user_order()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 200.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Sipariş bulunamadı'
            ]);
    }

    public function test_order_creation_uses_database_transaction()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(201);
        
        // Verify that order was created successfully
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id
        ]);
    }

    public function test_order_status_is_set_to_pending()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(201);

        $orderId = $response->json('data.order.id');
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'pending'
        ]);
    }

    public function test_order_items_contain_correct_data()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'quantity' => 3
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/orders');

        $response->assertStatus(201);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product1->id,
            'quantity' => 3,
            'price' => $this->product1->price
        ]);
    }

    public function test_user_can_only_see_their_own_orders()
    {
        $otherUser = User::factory()->create();
        
        Order::factory()->create(['user_id' => $this->user->id]);
        Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/orders');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
