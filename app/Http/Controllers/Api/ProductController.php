<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Filtreleme ve sayfalama ile tüm ürünleri getir",
     *     description="Ürünleri kategori, fiyat aralığı ve arama ile filtreleyebilir, sayfalama yapabilirsiniz",
     *     tags={"Ürünler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Sayfa numarası",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Sayfa başına ürün sayısı",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Kategori ID'sine göre filtrele",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum fiyat filtresi",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maksimum fiyat filtresi",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Ürün adında arama yap",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürünler başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="products", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search filter
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $limit = $request->get('limit', 20);
        $products = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Belirli bir ürünü getir",
     *     description="ID'si verilen ürünün detaylarını getirir",
     *     tags={"Ürünler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ürün ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürün başarıyla getirildi",
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
     *         description="Ürün bulunamadı"
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Yeni ürün oluştur",
     *     description="Yeni bir ürün oluşturur (Sadece Admin)",
     *     tags={"Ürünler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock_quantity","category_id"},
     *             @OA\Property(property="name", type="string", example="iPhone 15", description="Ürün adı"),
     *             @OA\Property(property="description", type="string", example="En son iPhone modeli", description="Ürün açıklaması"),
     *             @OA\Property(property="price", type="number", example=999.99, description="Ürün fiyatı"),
     *             @OA\Property(property="stock_quantity", type="integer", example=50, description="Stok miktarı"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Kategori ID'si")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ürün başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün başarıyla oluşturuldu"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yasak - Admin erişimi gerekli"
     *     )
     * )
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ürün başarıyla oluşturuldu',
            'data' => $product->load('category')
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Ürün güncelle",
     *     description="Belirli bir ürünü günceller (Sadece Admin)",
     *     tags={"Ürünler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Güncellenecek ürünün ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro", description="Ürün adı"),
     *             @OA\Property(property="description", type="string", example="Güncellenmiş iPhone modeli", description="Ürün açıklaması"),
     *             @OA\Property(property="price", type="number", example=1099.99, description="Ürün fiyatı"),
     *             @OA\Property(property="stock_quantity", type="integer", example=75, description="Stok miktarı"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Kategori ID'si")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürün başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün başarıyla güncellendi"),
     *             @OA\Property(property="data", type="object")
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
     *         response=403,
     *         description="Yasak - Admin erişimi gerekli"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:3',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ürün başarıyla güncellendi',
            'data' => $product->load('category')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Ürün sil",
     *     description="Belirli bir ürünü siler (Sadece Admin)",
     *     tags={"Ürünler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Silinecek ürünün ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ürün başarıyla silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün başarıyla silindi")
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
     *         response=403,
     *         description="Yasak - Admin erişimi gerekli"
     *     )
     * )
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ürün başarıyla silindi'
        ]);
    }
}
