<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ApiKeysController;
use App\Http\Controllers\Expansion\ThemeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance.edit');

    Route::patch('expansion/themes', [ThemeController::class, 'update'])
        ->name('expansion.themes.update');

    Route::get('settings/api-keys', [ApiKeysController::class, 'index'])->name('api-keys.index');
    Route::post('settings/api-keys', [ApiKeysController::class, 'store'])->name('api-keys.store');
    Route::delete('settings/api-keys/{token}', [ApiKeysController::class, 'destroy'])->name('api-keys.destroy');
});
