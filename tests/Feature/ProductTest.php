<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'web']);
});

test('cualquiera puede listar productos activos', function () {
    Product::factory()->count(3)->create(['status' => 'active']);

    $response = getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'price', 'stock', 'status', 'category', 'seller']],
        ]);
});

test('cualquiera puede ver el detalle de un producto', function () {
    $product = Product::factory()->create();

    $response = getJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $product->id);
});

test('vendedor autenticado puede crear un producto', function () {
    $category = Category::factory()->create();
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    SellerProfile::factory()->create(['user_id' => $seller->id]);

    $response = actingAs($seller)->postJson('/api/products', [
        'name' => 'Laptop Gamer',
        'description' => 'Una laptop muy potente',
        'price' => 999.99,
        'stock' => 5,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'Laptop Gamer')
        ->assertJsonPath('status', 'active');
});

test('vendedor puede editar su propio producto', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $profile = SellerProfile::factory()->create(['user_id' => $seller->id]);
    $product = Product::factory()->create(['seller_profile_id' => $profile->id]);

    $response = actingAs($seller)->putJson("/api/products/{$product->id}", [
        'name' => 'Nombre Actualizado',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Nombre Actualizado');
});

test('vendedor puede eliminar su propio producto', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $profile = SellerProfile::factory()->create(['user_id' => $seller->id]);
    $product = Product::factory()->create(['seller_profile_id' => $profile->id]);

    $response = actingAs($seller)->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Producto eliminado correctamente.');

    assertDatabaseMissing('products', ['id' => $product->id]);
});

test('vendedor ve solo sus propios productos', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $profile = SellerProfile::factory()->create(['user_id' => $seller->id]);

    Product::factory()->count(2)->create(['seller_profile_id' => $profile->id]);
    Product::factory()->count(3)->create();

    $response = actingAs($seller)->getJson('/api/seller/products');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('comprador no puede crear un producto', function () {
    $category = Category::factory()->create();
    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $response = actingAs($buyer)->postJson('/api/products', [
        'name' => 'Intento fallido',
        'price' => 10.00,
        'stock' => 1,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(403);
});

test('vendedor no puede editar producto de otro vendedor', function () {
    $seller1 = User::factory()->create();
    $seller1->assignRole('seller');
    $profile1 = SellerProfile::factory()->create(['user_id' => $seller1->id]);
    $product = Product::factory()->create(['seller_profile_id' => $profile1->id]);

    $seller2 = User::factory()->create();
    $seller2->assignRole('seller');
    SellerProfile::factory()->create(['user_id' => $seller2->id]);

    $response = actingAs($seller2)->putJson("/api/products/{$product->id}", [
        'name' => 'Intento de hackeo',
    ]);

    $response->assertStatus(403);
});

test('vendedor no puede eliminar producto de otro vendedor', function () {
    $seller1 = User::factory()->create();
    $seller1->assignRole('seller');
    $profile1 = SellerProfile::factory()->create(['user_id' => $seller1->id]);
    $product = Product::factory()->create(['seller_profile_id' => $profile1->id]);

    $seller2 = User::factory()->create();
    $seller2->assignRole('seller');
    SellerProfile::factory()->create(['user_id' => $seller2->id]);

    $response = actingAs($seller2)->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(403);
});

test('crear producto sin datos requeridos retorna 422', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    SellerProfile::factory()->create(['user_id' => $seller->id]);

    $response = actingAs($seller)->postJson('/api/products', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
});

test('usuario no autenticado no puede crear producto', function () {
    $category = Category::factory()->create();

    $response = postJson('/api/products', [
        'name' => 'Sin token',
        'price' => 10,
        'stock' => 1,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(401);
});