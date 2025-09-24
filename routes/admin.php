<?php

use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\OptionsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogsController;
use App\Http\Controllers\Admin\AuditSessionsController;
use App\Http\Controllers\Admin\ApiKeysController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    // Roles
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');

    // Permissions
    Route::get('/permissions', [PermissionsController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionsController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionsController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionsController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionsController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])->name('permissions.destroy');

    // Options (permisos finos)
    Route::get('/options', [OptionsController::class, 'index'])
        ->middleware('permission:options.view')
        ->name('options.index');
    Route::put('/options', [OptionsController::class, 'update'])
        ->middleware('permission:options.update')
        ->name('options.update');

    // Auditoría
    Route::get('/audit/logs', [AuditLogsController::class, 'index'])->name('audit.logs.index');
    Route::get('/audit/sessions', [AuditSessionsController::class, 'index'])->name('audit.sessions.index');

    // API Keys
    Route::get('/api-keys', [ApiKeysController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeysController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{token}', [ApiKeysController::class, 'destroy'])->name('api-keys.destroy');
});
