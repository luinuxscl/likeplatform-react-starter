<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\I18n\LanguageController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// i18n
Route::patch('locale', [LanguageController::class, 'update'])->name('locale.update');
Route::get('i18n/{locale}.json', [LanguageController::class, 'json'])->whereIn('locale', ['en', 'es'])->name('i18n.json');
