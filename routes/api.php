<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pet')->group(function () {
        Route::get('/', [PetController::class, 'index']);
        Route::post('/', [PetController::class, 'store']);
        Route::get('/qr-code/{id}',  [PetController::class,'getQrCode'])->name('pets.qrcode')->middleware('auth');
        Route::get('/{id}/qrcode', [PetController::class, 'getQrCode']);
        Route::get('/{id}/locations', [PetController::class, 'getLocations']);
        Route::get('/{id}', [PetController::class, 'show']);
        Route::put('/{id}', [PetController::class, 'update']);
        Route::delete('/{id}', [PetController::class, 'destroy']);
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'get']);
        Route::put('/', [UserController::class, 'update']);
    });
});

Route::post('/pet-location/{token}', [LocationController::class, 'store']);
Route::get('/pet-details/{token}', [PetController::class, 'getPetDetails']);

