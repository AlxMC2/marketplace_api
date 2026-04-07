<?php

use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\Category;

test('producto tiene los campos fillable correctos', function () {
    $fillable = (new Product())->getFillable();

    expect($fillable)->toContain('name')
                     ->toContain('price')
                     ->toContain('stock')
                     ->toContain('status')
                     ->toContain('seller_profile_id')
                     ->toContain('category_id');
});

test('producto castea price como decimal', function () {
    $casts = (new Product())->getCasts();

    expect($casts)->toHaveKey('price');
    expect($casts['price'])->toBe('decimal:2');
});

test('producto castea stock como integer', function () {
    $casts = (new Product())->getCasts();

    expect($casts)->toHaveKey('stock');
    expect($casts['stock'])->toBe('integer');
});

test('producto tiene relacion con sellerProfile', function () {
    $product = new Product();

    expect($product->sellerProfile())->toBeInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class
    );
});

test('producto tiene relacion con category', function () {
    $product = new Product();

    expect($product->category())->toBeInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class
    );
});