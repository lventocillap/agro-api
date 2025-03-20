<?php

namespace App\Http\Controllers\Subcategory;

use App\Exceptions\Category\NotFoundCategory;
use App\Exceptions\Subcategory\NotFoundSubcategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subcategory\ValidateSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ValidateSubcategoryRequest;

    public function storeSubcategory(string $nameCategory, Request $request): JsonResponse
    {
        $category = Category::where('name', $nameCategory)->first();
        if (!$category) {
            throw new NotFoundCategory;
        }
        $this->validateSubcategoryRequest($request);
        $category->subcategories()->create([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Subcategotria registrada']);
    }
    public function deleteSubcategory(string $nameSubcategory): JsonResponse
    {
        $nameSubcategory = Subcategory::where('name', $nameSubcategory)->first();
        if (!$nameSubcategory) {
            throw new NotFoundSubcategory;
        }
        return new JsonResponse(['data' => 'Subcategoria eliminada']);
    }
    public function getAllSubcategories(string $nameCategory): JsonResponse
    {
        $category = Category::where('name', $nameCategory)->first();

        if (!$category) {
            throw new NotFoundCategory;
        }
        $subcategories = Subcategory::select('id', 'name')
            ->where('category_id', $category->id)
            ->get();
        return new JsonResponse(['data' => $subcategories]);
    }
}
