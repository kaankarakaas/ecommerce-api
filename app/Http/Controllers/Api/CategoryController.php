<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Tüm kategorileri getir",
     *     description="Sistemdeki tüm kategorileri listeler",
     *     tags={"Kategoriler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Kategoriler başarıyla getirildi",
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
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Yeni kategori oluştur",
     *     description="Yeni bir kategori oluşturur (Sadece Admin)",
     *     tags={"Kategoriler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Elektronik", description="Kategori adı"),
     *             @OA\Property(property="description", type="string", example="Elektronik cihazlar ve aksesuarlar", description="Kategori açıklaması")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla oluşturuldu"),
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
            'name' => 'required|string|min:2',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori başarıyla oluşturuldu',
            'data' => $category
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Kategori güncelle",
     *     description="Belirli bir kategoriyi günceller (Sadece Admin)",
     *     tags={"Kategoriler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Güncellenecek kategorinin ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Elektronik", description="Kategori adı"),
     *             @OA\Property(property="description", type="string", example="Elektronik cihazlar ve aksesuarlar", description="Kategori açıklaması")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla güncellendi"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori bulunamadı"
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

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:2',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori başarıyla güncellendi',
            'data' => $category
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Kategori sil",
     *     description="Belirli bir kategoriyi siler (Sadece Admin)",
     *     tags={"Kategoriler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Silinecek kategorinin ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori başarıyla silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kategori başarıyla silindi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim - Geçersiz veya eksik JWT token"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori bulunamadı"
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

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori başarıyla silindi'
        ]);
    }
}
