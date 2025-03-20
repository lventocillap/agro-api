<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Http\Request;

trait ValidateProductStore
{
    public function validateProductStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:256',
            'characteristics' => 'required|string|max:10000',
            'benefits' => 'required|string|:max:10000',
            'compatibility' => 'required|string|max:10000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:1|max:10000',
            'subcategory_id' => 'required|array',
            'subcategory_id.*' => 'integer|exists:subcategories,id',
            'pdf' => 'string|nullable',
            'image' => 'string|nullable'
        ]);
    }
}