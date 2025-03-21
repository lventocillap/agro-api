<?php

use App\Http\Controllers\AboutUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Route::get('/about-us', [AboutUsController::class, 'getAboutUs']); // Ver AboutUs con imágenes
//Route::put('/about-us/{id}', [AboutUsController::class, 'updateAboutUs']); // Actualizar About Us

Route::post('/about-us/image', [AboutUsController::class, 'updateImageToAboutUs']); // Agregar Imagen

Route::post('/about-us/add-value/{id}', [AboutUsController::class, 'addValueAboutUs']); // Agregar un valor
Route::put('/about-us/update-value/{id}', [AboutUsController::class, 'updateValueAboutUs']); // Actualizar un valor
Route::delete('/about-us/delete-value/{id}', [AboutUsController::class, 'deleteValueAboutUs']); // Eliminar un valor