<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('seller');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('seller')
            && $user->sellerProfile?->id === $product->seller_profile_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole('seller')
            && $user->sellerProfile?->id === $product->seller_profile_id;
    }
}