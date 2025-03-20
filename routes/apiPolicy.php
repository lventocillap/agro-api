<?php

use App\Http\Controllers\Policy\PolicyController;
use Illuminate\Support\Facades\Route;

Route::get('/policies', [PolicyController::class, 'getPolicy']);
Route::put('/policies/{id}', [PolicyController::class, 'updatePolicy']);