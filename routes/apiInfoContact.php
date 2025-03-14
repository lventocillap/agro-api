<?php

use App\Http\Controllers\InfoContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/info-contact', [InfoContactController::class, 'getInfoContact']); // Ver Info Contact
Route::put('/info-contact/{id}', [InfoContactController::class, 'updateInfoContact']); // Actualizar Info Contact
