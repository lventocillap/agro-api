<?php

use App\Http\Controllers\AboutUsHomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/about-us-home', [AboutUsHomeController::class, 'getAboutUsHome']); // Ver AboutUs con imágenes
Route::put('/about-us-home/{id}', [AboutUsHomeController::class, 'updateAboutUsHome']); // Actualizar About Us

Route::post('/about-us-home/image', [AboutUsHomeController::class, 'updateImageToAboutUsHome']); // Agregar Imagen