<?php

use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Subcategory\SubcategoryController;
use App\Http\Controllers\Testimonies\TestimoniesController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'products',
    'controller' => ProductController::class,
], static function (){
    Route::post('/','storeProduct');
    Route::put('/{nameProduct}','updateProduct');
    Route::delete('/{nameProduct}','deleteProduct');
    Route::get('/', 'getAllProducts');
    Route::get('/{namePordroct}','getProduct');
});

Route::group([
    'prefix' => 'testimonies',
    'controller' => TestimoniesController::class,
], static function (){
    Route::post('/', 'storeTestimonies');
    Route::put('/{testimonieId}', 'updateTestimonies');
    Route::delete('/{testimonieId}', 'deleteTestimonie');
    Route::get('/', 'getAllTestimonies');
});

Route::group([
    'prefix' => 'categories'
], function () {
    Route::post('/', [CategoryController::class, 'storeCategory']);
    Route::put('/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/{id}', [CategoryController::class, 'deleteCategory']);
    Route::get('/', [CategoryController::class, 'getAllCategories']);
    
    Route::post('/{idCategory}/subcategories', [SubcategoryController::class, 'storeSubcategory']);
    Route::get('/{nameCategory}/subcategories', [SubcategoryController::class, 'getAllSubcategories']);
    Route::delete('/subcategory/{nameSubcategories}', [SubcategoryController::class, 'deleteSubcategory']);
});

Route::group([
    'prefix' => 'blogs',
    'controller' => BlogController::class
],static function () {
    Route::post('/', 'storeBlog');
    Route::put('/{idBlog}', 'updateBlog');
    Route::delete('/{idBlog}', 'deleteBlog');
    Route::get('/', 'getAllBlogs');
});