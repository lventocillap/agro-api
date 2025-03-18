<?php

use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Testimonies\TestimoniesController;
use App\Models\Testimonies;
use Illuminate\Support\Facades\Route;

// Route::post('/product', [ProductController::class, 'storeProduct']);
// Route::put('/product/{nameProduct}', [ProductController::class, 'updateProduct']);
// Route::delete('/product/{nameProduct}', [ProductController::class, 'deleteProduct']);
Route::group([
    'prefix' => 'product',
    'controller' => ProductController::class,
], static function (){
    Route::post('/','storeProduct');
    Route::put('/{nameProduct}','updateProduct');
    Route::delete('/{nameProduct}','deleteProduct');
    Route::get('/all', 'getAllProducts');
    Route::get('/{namePordroct}','getProduct');
});

Route::group([
    'prefix' => 'testimonies',
    'controller' => TestimoniesController::class,
], static function (){
    Route::post('/', 'storeTestimonies');
    Route::put('/{testimonieId}', 'updateTestimonies');
    Route::delete('/{testimonieId}', 'deleteTestimonie');
    Route::get('/all', 'getAllTestimonies');
});