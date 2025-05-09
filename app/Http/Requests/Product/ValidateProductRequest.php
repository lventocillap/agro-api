<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Http\Request;

trait ValidateProductRequest
{
    public function validateProducRequest(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|unique:products,name|max:256',
            'benefits' => 'required|array',
            'compatibility' => 'required|string|max:10000',
            'price' => 'required|numeric|min:1|max:999999.99',
            'stock' => 'required|integer|min:0|max:10000',
            'subcategory_id' => 'integer|required',
            'delete_images' => 'sometimes|array',
            'delete_images.*' => 'integer',
            'images'      => 'sometimes|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,gif,webp|max:5048',
            'discount' => 'integer|nullable'
        ],);
    }
}