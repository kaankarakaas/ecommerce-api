<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_correct_attributes()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isAdmin());
    }

    public function test_user_is_admin_method()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_carts_relationship()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->carts->contains($cart));
        $this->assertEquals($user->id, $cart->user_id);
    }

    public function test_user_has_orders_relationship()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->orders->contains($order));
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_category_model_has_correct_attributes()
    {
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories'
        ]);

        $this->assertEquals('Electronics', $category->name);
        $this->assertEquals('Electronic devices and accessories', $category->description);
    }

    public function test_category_has_products_relationship()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->products->contains($product));
        $this->assertEquals($category->id, $product->category_id);
    }

    public function test_product_model_has_correct_attributes()
    {
        $product = Product::factory()->create([
            'name' => 'iPhone 15',
            'description' => 'Latest iPhone model',
            'price' => 999.99,
            'stock_quantity' => 50
        ]);

        $this->assertEquals('iPhone 15', $product->name);
        $this->assertEquals('Latest iPhone model', $product->description);
        $this->assertEquals(999.99, $product->price);
        $this->assertEquals(50, $product->stock_quantity);
    }

    public function test_product_has_category_relationship()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $product->category->id);
        $this->assertEquals($category->name, $product->category->name);
    }

    public function test_product_has_cart_items_relationship()
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($product->cartItems->contains($cartItem));
        $this->assertEquals($product->id, $cartItem->product_id);
    }

    public function test_product_has_order_items_relationship()
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($product->orderItems->contains($orderItem));
        $this->assertEquals($product->id, $orderItem->product_id);
    }

    public function test_cart_model_has_correct_attributes()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $cart->user_id);
    }

    public function test_cart_has_user_relationship()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $cart->user->id);
        $this->assertEquals($user->name, $cart->user->name);
    }

    public function test_cart_has_cart_items_relationship()
    {
        $cart = Cart::factory()->create();
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->assertTrue($cart->cartItems->contains($cartItem));
        $this->assertEquals($cart->id, $cartItem->cart_id);
    }

    public function test_cart_item_model_has_correct_attributes()
    {
        $cartItem = CartItem::factory()->create([
            'quantity' => 3
        ]);

        $this->assertEquals(3, $cartItem->quantity);
    }

    public function test_cart_item_has_cart_relationship()
    {
        $cart = Cart::factory()->create();
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->assertEquals($cart->id, $cartItem->cart->id);
    }

    public function test_cart_item_has_product_relationship()
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertEquals($product->id, $cartItem->product->id);
        $this->assertEquals($product->name, $cartItem->product->name);
    }

    public function test_order_model_has_correct_attributes()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 299.99,
            'status' => 'pending'
        ]);

        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(299.99, $order->total_amount);
        $this->assertEquals('pending', $order->status);
    }

    public function test_order_has_user_relationship()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $order->user->id);
        $this->assertEquals($user->name, $order->user->name);
    }

    public function test_order_has_order_items_relationship()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertTrue($order->orderItems->contains($orderItem));
        $this->assertEquals($order->id, $orderItem->order_id);
    }

    public function test_order_item_model_has_correct_attributes()
    {
        $orderItem = OrderItem::factory()->create([
            'quantity' => 2,
            'price' => 149.99
        ]);

        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(149.99, $orderItem->price);
    }

    public function test_order_item_has_order_relationship()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertEquals($order->id, $orderItem->order->id);
    }

    public function test_order_item_has_product_relationship()
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertEquals($product->id, $orderItem->product->id);
        $this->assertEquals($product->name, $orderItem->product->name);
    }

    public function test_model_fillable_attributes()
    {
        // Test User fillable
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ];
        $user = User::create($userData);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        // Test Category fillable
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];
        $category = Category::create($categoryData);
        $this->assertDatabaseHas('categories', $categoryData);

        // Test Product fillable
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 50,
            'category_id' => $category->id
        ];
        $product = Product::create($productData);
        $this->assertDatabaseHas('products', $productData);
    }

    public function test_model_casts()
    {
        $product = Product::factory()->create([
            'price' => '99.99',
            'stock_quantity' => '50'
        ]);

        // Price is cast as decimal, so it should be a string
        $this->assertIsString($product->price);
        // Stock quantity is not cast, so it should be an int
        $this->assertIsInt($product->stock_quantity);

        $order = Order::factory()->create([
            'total_amount' => '299.99'
        ]);

        // Total amount is cast as decimal, so it should be a string
        $this->assertIsString($order->total_amount);

        $orderItem = OrderItem::factory()->create([
            'price' => '149.99'
        ]);

        // Price is cast as decimal, so it should be a string
        $this->assertIsString($orderItem->price);
    }
}
