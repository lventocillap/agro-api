<?php

use App\Http\Controllers\AboutUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/about-us', [AboutUsController::class, 'getAboutUs']); // Ver About Us
Route::put('/about-us/{id}', [AboutUsController::class, 'updateAboutUs']); // Actualizar About Us

Route::post('/about-us/add-value', [AboutUsController::class, 'addValueAboutUs']); // Agregar un valor
Route::put('/about-us/update-value/{id}', [AboutUsController::class, 'updateValueAboutUs']); // Actualizar un valor
Route::delete('/about-us/delete-value/{id}', [AboutUsController::class, 'deleteValueAboutUs']); // Eliminar un valor