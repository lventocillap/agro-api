<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Public Routes
Route::post('register', [AuthController::class, 'registerUser']);
Route::post('login', [AuthController::class, 'loginUser']);

Route::get('about-us', [AboutUsController::class, 'getAboutUs']); // Ver AboutUs con imágenes

Route::middleware(IsUserAuth::class)->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });


    Route::middleware(IsAdmin::class)->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::put('/about-us/{id}', [AboutUsController::class, 'updateAboutUs']); // Actualizar About Us
        });
    });
});

