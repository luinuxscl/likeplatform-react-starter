<?php

use Illuminate\Support\Facades\Route;
use Like\Fcv\Http\Controllers\VerificationController;

Route::middleware(['web', 'auth'])
    ->prefix('fcv')
    ->name('fcv.')
    ->group(function () {
        Route::post('/verify', [VerificationController::class, 'verify'])->name('verify');
        Route::get('/health', function () {
            return response()->json([
                'ok' => true,
                'package' => 'like/fcv-access',
                'version' => config('fcv.version'),
            ]);
        })->name('health');
    });
