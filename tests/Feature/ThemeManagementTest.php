<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('can change theme via PATCH and persists in session', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch('/expansion/themes', ['theme' => 'blue']);

    $response->assertRedirect();
    expect(session('expansion.theme'))->toBe('blue');
});

it('rejects invalid theme', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch('/expansion/themes', ['theme' => 'not-a-theme']);

    $response->assertSessionHasErrors('theme');
});

it('shares themes via Inertia on settings/appearance', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/appearance')
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/appearance')
            ->has('expansion.themes')
            ->where('expansion.themes.current', session('expansion.theme', config('expansion.themes.default_theme')))
            ->has('expansion.themes.available')
        );
});

it('injects SSR safe theme variables for first paint', function () {
    // Ensure no theme is set to use default
    session()->forget('expansion.theme');

    $default = config('expansion.themes.default_theme');
    $colors = config("expansion.themes.available_themes.{$default}.colors");
    $expectedPrimary = $colors['primary'] ?? null;

    $resp = $this->get('/');
    $resp->assertOk();

    if ($expectedPrimary) {
        $resp->assertSee($expectedPrimary, false);
    }
});

it('has all 12 shadcn themes configured', function () {
    $themes = config('expansion.themes.available_themes');
    
    $expectedThemes = [
        'zinc', 'slate', 'stone', 'gray', 'neutral',
        'red', 'rose', 'orange', 'green', 'blue', 'yellow', 'violet'
    ];
    
    expect($themes)->toBeArray();
    expect(array_keys($themes))->toHaveCount(12);
    
    foreach ($expectedThemes as $theme) {
        expect($themes)->toHaveKey($theme);
        expect($themes[$theme])->toHaveKey('name');
        expect($themes[$theme])->toHaveKey('colors');
        expect($themes[$theme]['colors'])->toBeArray();
    }
});

it('can switch between all 12 themes', function () {
    $user = User::factory()->create();
    $themes = ['zinc', 'slate', 'stone', 'gray', 'neutral', 'red', 'rose', 'orange', 'green', 'blue', 'yellow', 'violet'];
    
    foreach ($themes as $theme) {
        $response = $this->actingAs($user)
            ->patch('/expansion/themes', ['theme' => $theme]);
        
        $response->assertRedirect();
        expect(session('expansion.theme'))->toBe($theme);
    }
});

it('requires authentication to change theme', function () {
    $response = $this->patch('/expansion/themes', ['theme' => 'blue']);
    
    $response->assertRedirect('/login');
});

it('validates theme parameter is required', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->patch('/expansion/themes', []);
    
    $response->assertSessionHasErrors('theme');
});

it('each theme has required color tokens', function () {
    $themes = config('expansion.themes.available_themes');
    $requiredTokens = ['primary', 'primary-foreground', 'accent', 'accent-foreground', 'ring'];
    
    foreach ($themes as $themeKey => $themeConfig) {
        $colors = $themeConfig['colors'];
        
        foreach ($requiredTokens as $token) {
            expect($colors)->toHaveKey($token)
                ->and($colors[$token])->not->toBeEmpty();
        }
    }
});

it('theme config has correct structure', function () {
    $config = config('expansion.themes');
    
    expect($config)->toHaveKey('enabled');
    expect($config)->toHaveKey('default_theme');
    expect($config)->toHaveKey('available_themes');
    expect($config)->toHaveKey('custom_themes');
    
    expect($config['enabled'])->toBeBool();
    expect($config['default_theme'])->toBeString();
    expect($config['available_themes'])->toBeArray();
    expect($config['custom_themes'])->toBeArray();
});

it('default theme exists in available themes', function () {
    $defaultTheme = config('expansion.themes.default_theme');
    $availableThemes = config('expansion.themes.available_themes');
    
    expect($availableThemes)->toHaveKey($defaultTheme);
});

it('preserves scroll and state when changing theme', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->patch('/expansion/themes', ['theme' => 'rose']);
    
    // Inertia should preserve scroll and state
    $response->assertRedirect();
    $response->assertSessionHas('expansion.theme', 'rose');
});
