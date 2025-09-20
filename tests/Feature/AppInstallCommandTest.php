<?php

use Illuminate\Support\Facades\Artisan;

it('registers the app:install command', function () {
    // Verifica que el comando esté disponible en la lista
    $exit = Artisan::call('list');
    expect($exit)->toBe(0);
    $output = Artisan::output();
    expect($output)->toContain('app:install');
});

it('runs app:install (standard)', function () {
    // Ejecuta instalación estándar: migra/seed/optimize sin fresh
    $this->artisan('app:install')
        ->assertExitCode(0);
});

it('runs app:install --dev', function () {
    // Ejecuta instalación de desarrollo: migrate:fresh --seed
    // En entorno de tests debería ser seguro (sqlite en memoria)
    $this->artisan('app:install --dev')
        ->assertExitCode(0);
});
