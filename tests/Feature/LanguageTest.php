<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('can update locale via PATCH and persist in session and cookie', function () {
    $user = User::factory()->create();

    $resp = $this->actingAs($user)
        ->patch('/locale', ['locale' => 'es']);

    $resp->assertRedirect();
    expect(session('locale'))->toBe('es');
    $resp->assertCookie('locale', 'es');
});

it('rejects invalid locale on PATCH', function () {
    $user = User::factory()->create();

    $resp = $this->actingAs($user)
        ->patch('/locale', ['locale' => 'fr']);

    $resp->assertSessionHasErrors('locale');
});

it('serves i18n json for allowed locales', function () {
    $this->get('/i18n/en.json')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json; charset=utf-8');

    $this->get('/i18n/es.json')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json; charset=utf-8');
});

it('returns 404 for unsupported locales in i18n json', function () {
    $this->get('/i18n/fr.json')->assertNotFound();
});

it('shares i18n props via Inertia', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/appearance')
        ->assertInertia(fn (Assert $page) => $page
            ->has('i18n')
            ->where('i18n.available', ['en', 'es'])
        );
});
