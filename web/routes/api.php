<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware(['throttle:api-register']);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(['throttle:api-login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {
        Route::get('/getuser', [AuthController::class, 'getAllUser']);
        Route::post('/adduser', [AuthController::class, 'addUser']);
        Route::put('/updateuser/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/deleteuser/{id}', [AuthController::class, 'deleteUser']);
    });
});
