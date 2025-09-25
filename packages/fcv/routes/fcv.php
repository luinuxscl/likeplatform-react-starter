<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('fcv')
    ->name('fcv.')
    ->group(function () {
        Route::get('/health', function () {
            return response()->json([
                'ok' => true,
                'package' => 'like/fcv-access',
                'version' => config('fcv.version'),
            ]);
        })->name('health');
    });
