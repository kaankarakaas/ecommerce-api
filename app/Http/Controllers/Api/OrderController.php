<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Yeni sipariş oluştur",
     *     description="Sepetteki ürünlerden yeni bir sipariş oluşturur ve sepeti temizler",
     *     tags={"Siparişler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Sipariş başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sipariş başarıyla oluşturuldu"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order", type="object"),
     *                 @OA\Property(property="order_items", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Sepet boş veya yetersiz stok"
     *     )
     * )
     */
    public function store()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->cartItems()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet boş'
            ], 422);
        }

        // Check stock availability
        $cartItems = $cart->cartItems()->with('product')->get();
        
        foreach ($cartItems as $cartItem) {
            if ($cartItem->product->stock_quantity < $cartItem->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Ürün için yetersiz stok: {$cartItem->product->name}"
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate total
            $total = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => 'pending'
            ]);

            // Create order items and update stock
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price
                ]);

                // Update product stock
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Clear cart
            $cart->cartItems()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sipariş başarıyla oluşturuldu',
                'data' => [
                    'order' => $order->load('orderItems.product'),
                    'order_items' => $order->orderItems
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Sipariş oluşturulamadı'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Kullanıcı siparişlerini getir",
     *     description="Giriş yapmış kullanıcının tüm siparişlerini listeler",
     *     tags={"Siparişler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Siparişler başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('orderItems.product')->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Belirli bir siparişi getir",
     *     description="ID'si verilen siparişin detaylarını getirir",
     *     tags={"Siparişler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sipariş ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sipariş başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sipariş bulunamadı"
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        $order = $user->orders()->with('orderItems.product')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Sipariş bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
