<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pet')->group(function () {
        Route::get('/', [PetController::class, 'index']);
        Route::post('/', [PetController::class, 'store']);
        Route::get('/{id}', [PetController::class, 'show']);
        Route::put('/{id}', [PetController::class, 'update']);
        Route::delete('/{id}', [PetController::class, 'destroy']);
    });
});

Route::post('/pets/{petId}/locations', [LocationController::class, 'store']);
