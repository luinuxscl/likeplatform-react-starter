<?php

use Illuminate\Support\Facades\Route;
use Like\Fcv\Http\Controllers\VerificationController;
use Like\Fcv\Http\Controllers\AccessController;
use Like\Fcv\Http\Controllers\Guard\DashboardController;
use Like\Fcv\Http\Controllers\SearchController;
use Like\Fcv\Http\Controllers\AccessQueryController;

Route::middleware(['web', 'auth'])
    ->prefix('fcv')
    ->name('fcv.')
    ->group(function () {
        Route::get('/guard', [DashboardController::class, 'index'])->name('guard');
        Route::post('/verify', [VerificationController::class, 'verify'])->name('verify');
        Route::post('/search', [SearchController::class, 'search'])->name('search');
        Route::post('/access', [AccessController::class, 'store'])->name('access.store');
        Route::get('/access/recent', [AccessQueryController::class, 'recent'])->name('access.recent');
        Route::get('/health', function () {
            return response()->json([
                'ok' => true,
                'package' => 'like/fcv-access',
                'version' => config('fcv.version'),
            ]);
        })->name('health');
    });
