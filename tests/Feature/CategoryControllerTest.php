<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $admin;
    private $user;
    private $adminToken;
    private $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->userToken = JWTAuth::fromUser($this->user);
    }

    public function test_can_get_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_cannot_get_categories_without_token()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_admin_can_create_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Kategori başarıyla oluşturuldu'
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
    }

    public function test_user_cannot_create_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->postJson('/api/categories', $categoryData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_create_category_without_token()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ]);
    }

    public function test_cannot_create_category_with_invalid_data()
    {
        $categoryData = [
            'name' => 'T', // Too short
            'description' => 'Test Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Doğrulama hatası'
            ]);
    }

    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Kategori başarıyla güncellendi'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ]);
    }

    public function test_user_cannot_update_category()
    {
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_update_nonexistent_category()
    {
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/categories/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ]);
    }

    public function test_admin_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kategori başarıyla silindi'
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function test_user_cannot_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin erişimi gerekli'
            ]);
    }

    public function test_cannot_delete_nonexistent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/categories/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ]);
    }
}
