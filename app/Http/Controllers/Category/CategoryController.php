<?php

namespace App\Http\Controllers\Category;

use App\Exceptions\Category\NotFoundCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ValidateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Laravel 11 API Documentation",
 *     version="1.0.0",
 *     description="Documentación de la API de autenticación con Swagger en Laravel 11",
 *     @OA\Contact(
 *         email="soporte@tuempresa.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="API Server"
 * )
 */
class CategoryController extends Controller
{
    use ValidateCategoryRequest;
    /**
 * @OA\Get(
 *     path="/api/usuarios",
 *     summary="Obtener la lista de usuarios",
 *     tags={"Usuarios"},
 *     @OA\Response(
 *         response=200,
 *         description="Lista de usuarios"
 *     )
 * )
 */
    public function storeCatetgory(Request $request): JsonResponse
    {
        $this->validateCategoryRequest($request);
        Category::create([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Categoria registrada']);
    }
    public function updateCategory(int $id, Request $request): JsonResponse
    {
        $this->validateCategoryRequest($request);
        $category = Category::find($id);
        if (!$category) {
            throw new NotFoundCategory;
        }
        $category->update([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Categoria actualizado']);
    }
    public function deleteCategory(int $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            throw new NotFoundCategory;
        }
        $category->delete();
        return new JsonResponse(['date' => 'Categoria eliminada']);
    }
    public function getAllCategories(Request $request): JsonResponse
    {
        $nameSubcategory = $request->query('subcategory');

        $query = Category::select('id', 'name');

        if ($nameSubcategory) {
            $query->whereHas('subcategories', function ($query) use ($nameSubcategory) {
                $query->where('name', 'like', "%{$nameSubcategory}%");
            });
        }
        $categories = $query->with(['subcategories' => function ($query) use ($nameSubcategory) {
            if ($nameSubcategory) {
                $query->where('name', 'like', "%{$nameSubcategory}%");
            }
            $query->select('id', 'name', 'category_id');
        }])->get();
        return new JsonResponse(['data' => $categories]);
    }
}
