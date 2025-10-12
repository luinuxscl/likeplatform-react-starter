<?php

use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\I18n\LanguageController;
use App\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('changelog', [ChangelogController::class, 'index'])->name('changelog');

    // Widget routes
    Route::prefix('api/widgets')->name('widgets.')->group(function () {
        Route::get('/', [WidgetController::class, 'index'])->name('index');
        Route::get('/layout', [WidgetController::class, 'getLayout'])->name('layout.get');
        Route::put('/layout', [WidgetController::class, 'saveLayout'])->name('layout.save');
        Route::post('/layout/reset', [WidgetController::class, 'resetLayout'])->name('layout.reset');
        Route::post('/{key}/toggle', [WidgetController::class, 'toggleVisibility'])->name('toggle');
        Route::post('/{key}/refresh', [WidgetController::class, 'refresh'])->name('refresh');
        Route::put('/{key}/config', [WidgetController::class, 'updateConfig'])->name('config.update');
        Route::post('/cache/clear', [WidgetController::class, 'clearCache'])->name('cache.clear')->middleware('role:admin');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

// i18n
Route::patch('locale', [LanguageController::class, 'update'])->name('locale.update');
Route::get('i18n/{locale}.json', [LanguageController::class, 'json'])->whereIn('locale', ['en', 'es'])->name('i18n.json');
