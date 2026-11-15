<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClimateReadingController;
use App\Http\Controllers\Api\DashboardSummaryController;
use App\Http\Controllers\Api\DiagnosisController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PolybagController;
use App\Http\Controllers\Api\SensorReadingController;
use App\Http\Controllers\Api\ThresholdController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/google', [GoogleAuthController::class, 'login'])
    ->middleware('throttle:google-mobile-login');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware(['throttle:api-register']);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(['throttle:api-login']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {
        Route::get('/getuser', [AuthController::class, 'getAllUser']);
        Route::post('/adduser', [AuthController::class, 'addUser']);
        Route::put('/updateuser/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/deleteuser/{id}', [AuthController::class, 'deleteUser']);
    });

    Route::apiResource('polybags', PolybagController::class)
        ->names('api.polybags');
    Route::get('/polybags/{polybag}/readings', [SensorReadingController::class, 'index']);
    Route::post('/polybags/{polybag}/readings', [SensorReadingController::class, 'store']);

    Route::get('/climate/latest', [ClimateReadingController::class, 'latest']);
    Route::get('/climate', [ClimateReadingController::class, 'index']);
    Route::post('/climate', [ClimateReadingController::class, 'store']);

    Route::apiResource('diagnoses', DiagnosisController::class)
        ->names('api.diagnoses');
    Route::apiResource('kategori', KategoriController::class)
        ->names('api.kategori');

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::get('/alerts/{alert}', [AlertController::class, 'show']);
    Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy']);

    Route::get('/thresholds', [ThresholdController::class, 'index']);
    Route::put('/thresholds', [ThresholdController::class, 'update']);

    Route::get('/dashboard/summary', DashboardSummaryController::class);
});
