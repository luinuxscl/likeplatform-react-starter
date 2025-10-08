<?php

use Illuminate\Support\Facades\Route;
use Like\Fcv\Http\Controllers\VerificationController;
use Like\Fcv\Http\Controllers\AccessController;
use Like\Fcv\Http\Controllers\Guard\DashboardController;
use Like\Fcv\Http\Controllers\SearchController;
use Like\Fcv\Http\Controllers\AccessQueryController;
use Like\Fcv\Http\Controllers\CourseController;
use Like\Fcv\Http\Controllers\CourseStudentController;
use Like\Fcv\Http\Controllers\OrganizationController;
use Like\Fcv\Http\Controllers\Api\CourseApiController;

/*
|--------------------------------------------------------------------------
| FCV Package Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas del paquete FCV. Estas rutas
| son cargadas por el FcvServiceProvider y están mapeadas al grupo de
| middleware 'web'. ¡Ahora también incluyen soporte para API!
|
*/

// Rutas de API (para ser consumidas desde el frontend con Inertia)
Route::middleware(['web', 'auth'])
    ->prefix('fcv')
    ->name('fcv.')
    ->group(function () {
        // Dashboard de la aplicación
        Route::get('/guard', [DashboardController::class, 'index'])->name('guard');
        
        // Rutas de verificación y acceso
        Route::post('/verify', [VerificationController::class, 'verify'])->name('verify');
        Route::post('/access', [AccessController::class, 'store'])->name('access.store');
        
        // Búsquedas
        Route::get('/search', [SearchController::class, 'search'])->name('search');
        
        // Accesos recientes
        Route::get('/access/recent', [AccessQueryController::class, 'recent'])->name('access.recent');
        
        // Rutas de Cursos (API)
        Route::prefix('courses')->name('courses.')->group(function () {
            // Rutas web (Inertia)
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::get('/{course}', [CourseController::class, 'show'])->name('show');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
            
            // Rutas de API
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/{course}/stats', [CourseApiController::class, 'stats'])->name('stats');
                Route::put('/{course}/schedules', [CourseApiController::class, 'updateSchedules'])->name('schedules.update');
                Route::put('/{course}/restore', [CourseApiController::class, 'restore'])->name('restore');
            });
            
            // Estudiantes del curso
            Route::post('/{course}/students/import', [CourseStudentController::class, 'import'])->name('students.import');
            Route::post('/{course}/students/{person}', [CourseStudentController::class, 'attach'])->name('students.attach');
            Route::delete('/{course}/students/{person}', [CourseStudentController::class, 'detach'])->name('students.detach');
        });
        
        // Organizaciones
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [OrganizationController::class, 'index'])->name('index');
            Route::post('/', [OrganizationController::class, 'store'])->name('store');
            Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
            Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
            Route::put('/{organization}/restore', [OrganizationController::class, 'restore'])->name('restore');
        });
        
        // Health check del paquete
        Route::get('/health', function () {
            return response()->json([
                'ok' => true,
                'package' => 'like/fcv',
                'version' => config('fcv.version', '1.0.0'),
                'status' => 'operational'
            ]);
        })->name('health');
    });
