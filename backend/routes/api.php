<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.cookie');
});

Route::middleware('jwt.cookie')->group(function (): void {
    Route::get('/profile', function () {
        return response()->json([
            'message' => 'Protected API',
            'user' => request()->user(),
        ]);
    });
});
