<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'status'      => $this->status,
            'category'    => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ],
            'seller' => [
                'id'         => $this->sellerProfile->id,
                'store_name' => $this->sellerProfile->store_name,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}