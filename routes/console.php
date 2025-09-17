<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\AppInstall;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registrar comandos de la aplicación
Artisan::starting(function ($artisan) {
    $artisan->resolve(AppInstall::class);
});
