<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas para el controlador de servicios
Route::get('/services', [ServiceController::class, 'getServices']);
Route::post('/services', [ServiceController::class, 'createService']);
Route::get('/services/{id}', [ServiceController::class, 'getServiceById']);
Route::put('/services/{id}', [ServiceController::class, 'updateServiceById']);
Route::delete('/services/{id}', [ServiceController::class, 'deleteService']);

Route::post('/services/{id}/features', [ServiceController::class, 'addFeature']);
Route::put('/services/features/{id}/{index}', [ServiceController::class, 'updateFeature']);
Route::delete('/services/features/{id}/{index}', [ServiceController::class, 'deleteFeature']);
