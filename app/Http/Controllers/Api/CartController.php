<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Kullanıcı sepetini getir",
     *     description="Giriş yapmış kullanıcının sepetini ve içindeki ürünleri getirir",
     *     tags={"Sepet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sepet başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cart", type="object"),
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="total", type="number")
     *             )
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
        
        // Get or create cart for user
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        $cartItems = $cart->cartItems()->with('product')->get();
        
        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'items' => $cartItems,
                'total' => $total
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Sepete ürün ekle",
     *     description="Belirtilen ürünü belirtilen miktarda sepete ekler",
     *     tags={"Sepet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="Eklenecek ürünün ID'si"),
     *             @OA\Property(property="quantity", type="integer", example=2, description="Eklenecek ürün miktarı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürün sepete başarıyla eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün sepete başarıyla eklendi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ürün bulunamadı"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Doğrulama hatası"
     *     )
     * )
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz stok'
            ], 422);
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Check if product already exists in cart
        $cartItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity
            ]);
        } else {
            $cart->cartItems()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ürün sepete başarıyla eklendi'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/update",
     *     summary="Sepet ürün miktarını güncelle",
     *     description="Sepetteki belirli bir ürünün miktarını günceller",
     *     tags={"Sepet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="Güncellenecek ürünün ID'si"),
     *             @OA\Property(property="quantity", type="integer", example=3, description="Yeni ürün miktarı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sepet başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sepet başarıyla güncellendi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ürün sepette bulunamadı"
     *     )
     * )
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet bulunamadı'
            ], 404);
        }

        $cartItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün sepette bulunamadı'
            ], 404);
        }

        $product = Product::find($request->product_id);
        
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz stok'
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Sepet başarıyla güncellendi'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/remove/{product_id}",
     *     summary="Sepetten ürün çıkar",
     *     description="Sepetten belirli bir ürünü çıkarır",
     *     tags={"Sepet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="Çıkarılacak ürünün ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürün sepetten başarıyla çıkarıldı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün sepetten başarıyla çıkarıldı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ürün sepette bulunamadı"
     *     )
     * )
     */
    public function remove($product_id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet bulunamadı'
            ], 404);
        }

        $cartItem = $cart->cartItems()->where('product_id', $product_id)->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün sepette bulunamadı'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ürün sepetten başarıyla çıkarıldı'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     summary="Sepeti temizle",
     *     description="Sepetteki tüm ürünleri çıkarır",
     *     tags={"Sepet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sepet başarıyla temizlendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sepet başarıyla temizlendi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     )
     * )
     */
    public function clear()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->cartItems()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sepet başarıyla temizlendi'
        ]);
    }
}
