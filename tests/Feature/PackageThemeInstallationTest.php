<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Backup del .env original
    if (File::exists(base_path('.env'))) {
        File::copy(base_path('.env'), base_path('.env.backup'));
    }
});

afterEach(function () {
    // Restaurar .env original
    if (File::exists(base_path('.env.backup'))) {
        File::move(base_path('.env.backup'), base_path('.env'), true);
    }
});

it('can set default theme during package installation', function () {
    // Configurar tema inicial
    config(['expansion.themes.default_theme' => 'zinc']);
    
    // Simular instalación con tema específico
    Artisan::call('fcv:install', [
        '--theme' => 'blue',
        '--no-interaction' => true,
    ]);
    
    // Verificar que el .env fue actualizado
    $envContent = File::get(base_path('.env'));
    expect($envContent)->toContain('EXPANSION_DEFAULT_THEME=blue');
});

it('validates theme exists before setting it', function () {
    $availableThemes = array_keys(config('expansion.themes.available_themes', []));
    
    expect($availableThemes)->toContain('zinc');
    expect($availableThemes)->toContain('blue');
    expect($availableThemes)->toContain('rose');
    expect($availableThemes)->not->toContain('invalid-theme');
});

it('respects no-theme flag during installation', function () {
    $originalTheme = config('expansion.themes.default_theme');
    
    // Instalar sin cambiar tema
    Artisan::call('fcv:install', [
        '--no-theme' => true,
        '--no-interaction' => true,
    ]);
    
    // El tema no debería haber cambiado
    expect(config('expansion.themes.default_theme'))->toBe($originalTheme);
});

it('package can define suggested theme in config', function () {
    $suggestedTheme = config('fcv.theme');
    
    expect($suggestedTheme)->toBe('blue');
    expect($suggestedTheme)->toBeIn(array_keys(config('expansion.themes.available_themes', [])));
});

it('can register custom theme from package', function () {
    // Simular registro de tema personalizado
    $customTheme = [
        'name' => 'FCV Custom',
        'colors' => [
            'primary' => 'hsl(210 100% 50%)',
            'primary-foreground' => 'hsl(0 0% 100%)',
            'accent' => 'hsl(150 100% 40%)',
            'accent-foreground' => 'hsl(0 0% 0%)',
            'ring' => 'hsl(210 100% 50%)',
        ],
    ];
    
    config(['expansion.themes.custom_themes.fcv-custom' => $customTheme]);
    
    $customThemes = config('expansion.themes.custom_themes');
    
    expect($customThemes)->toHaveKey('fcv-custom');
    expect($customThemes['fcv-custom']['name'])->toBe('FCV Custom');
    expect($customThemes['fcv-custom']['colors'])->toHaveKey('primary');
});

it('updates env file correctly', function () {
    $envPath = base_path('.env');
    $originalContent = File::get($envPath);
    
    // Agregar/actualizar variable
    $pattern = "/^EXPANSION_DEFAULT_THEME=.*/m";
    
    if (preg_match($pattern, $originalContent)) {
        $newContent = preg_replace($pattern, "EXPANSION_DEFAULT_THEME=rose", $originalContent);
    } else {
        $newContent = $originalContent . "\nEXPANSION_DEFAULT_THEME=rose\n";
    }
    
    File::put($envPath, $newContent);
    
    // Verificar que se actualizó correctamente
    $updatedContent = File::get($envPath);
    expect($updatedContent)->toContain('EXPANSION_DEFAULT_THEME=rose');
});

it('all 12 shadcn themes are available for packages', function () {
    $availableThemes = array_keys(config('expansion.themes.available_themes', []));
    
    $expectedThemes = [
        'zinc', 'slate', 'stone', 'gray', 'neutral',
        'red', 'rose', 'orange', 'green', 'blue', 'yellow', 'violet'
    ];
    
    foreach ($expectedThemes as $theme) {
        expect($availableThemes)->toContain($theme);
    }
    
    expect($availableThemes)->toHaveCount(12);
});
