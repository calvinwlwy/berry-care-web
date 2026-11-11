<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordHelpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagementController;
use App\Http\Middleware\PreventAuthenticatedPageCaching;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:web-login-ip')
        ->name('login.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
        ->middleware('throttle:10,1')
        ->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->middleware('throttle:10,1')
        ->name('auth.google.callback');
    Route::get('/forgot-password', PasswordHelpController::class)->name('password.help');
});

Route::middleware(['auth', PreventAuthenticatedPageCaching::class])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/polybags', [ManagementController::class, 'polybags'])->name('polybags.index');
    Route::get('/diagnosis', [ManagementController::class, 'diagnosis'])->name('diagnosis.index');
    Route::post('/diagnosis/upload', [ManagementController::class, 'uploadScan'])->name('diagnosis.upload');
    Route::get('/alerts', [ManagementController::class, 'alerts'])->name('alerts.index');
    Route::get('/thresholds', [ManagementController::class, 'thresholds'])->name('thresholds.index');
    Route::put('/thresholds', [ManagementController::class, 'saveThresholds'])->name('thresholds.update');
    Route::get('/users', [ManagementController::class, 'users'])->name('users.index');
    Route::post('/users', [ManagementController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [ManagementController::class, 'updateUser'])->name('users.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
