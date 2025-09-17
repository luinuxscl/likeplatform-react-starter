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
