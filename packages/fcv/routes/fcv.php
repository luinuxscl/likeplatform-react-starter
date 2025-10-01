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

Route::middleware(['web', 'auth'])
    ->prefix('fcv')
    ->name('fcv.')
    ->group(function () {
        Route::get('/guard', [DashboardController::class, 'index'])->name('guard');
        Route::post('/verify', [VerificationController::class, 'verify'])->name('verify');
        Route::get('/search', [SearchController::class, 'search'])->name('search');
        Route::post('/access', [AccessController::class, 'store'])->name('access.store');
        Route::get('/access/recent', [AccessQueryController::class, 'recent'])->name('access.recent');
        
        // Courses
        Route::resource('courses', CourseController::class)->except(['create', 'edit']);
        Route::put('courses/{course}/restore', [CourseController::class, 'restore'])->name('courses.restore');
        Route::get('courses/{course}/stats', [CourseController::class, 'stats'])->name('courses.stats');
        Route::put('courses/{course}/schedules', [CourseController::class, 'updateSchedules'])->name('courses.schedules.update');
        
        // Course Students
        Route::post('courses/{course}/students/import', [CourseStudentController::class, 'import'])->name('courses.students.import');
        Route::post('courses/{course}/students/{person}', [CourseStudentController::class, 'attach'])->name('courses.students.attach');
        Route::delete('courses/{course}/students/{person}', [CourseStudentController::class, 'detach'])->name('courses.students.detach');
        
        Route::resource('organizations', OrganizationController::class)->except(['create', 'edit', 'show']);
        Route::put('organizations/{organization}/restore', [OrganizationController::class, 'restore'])->name('organizations.restore');
        Route::get('/health', function () {
            return response()->json([
                'ok' => true,
                'package' => 'like/fcv-access',
                'version' => config('fcv.version'),
            ]);
        })->name('health');
    });
