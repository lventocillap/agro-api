<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;

// Incluir varios archivos de rutas API
require __DIR__ . '/apiService.php';
require __DIR__.'/apiProduct.php';
require __DIR__.'/apiPolicy.php';
Route::get('/api/documentation', [SwaggerController::class, 'docs']);
Route::get('/api/doc-assets/{asset}', [SwaggerController::class, 'assets']);