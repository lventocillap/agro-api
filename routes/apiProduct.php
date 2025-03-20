<?php

use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/product', [ProductController::class, 'storeProduct']);
Route::put('/product/{nameProduct}', [ProductController::class, 'updateProduct']);
Route::delete('/product/{nameProduct}', [ProductController::class, 'deleteProduct']);
