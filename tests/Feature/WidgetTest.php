<?php

use App\Models\User;
use App\Models\UserWidget;
use App\Services\WidgetService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear roles y permisos
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);

    \Artisan::call('db:seed', ['--class' => 'WidgetPermissionsSeeder']);
});

describe('Widget Discovery', function () {
    test('discovers core widgets from config', function () {
        $service = app(WidgetService::class);
        $widgets = $service->discover(useCache: false);

        expect($widgets)->toBeArray()
            ->and($widgets)->not->toBeEmpty();

        $welcomeWidget = collect($widgets)->firstWhere('key', 'welcome');
        expect($welcomeWidget)->not->toBeNull()
            ->and($welcomeWidget['component'])->toBe('WelcomeWidget')
            ->and($welcomeWidget['package'])->toBe('core');
    });

    test('validates widget structure', function () {
        $service = app(WidgetService::class);
        $widgets = $service->discover(useCache: false);

        foreach ($widgets as $widget) {
            expect($widget)->toHaveKeys(['key', 'component', 'title', 'size', 'package']);
        }
    });

    test('caches discovered widgets', function () {
        $service = app(WidgetService::class);

        // Primera llamada sin caché
        $widgets1 = $service->discover(useCache: false);

        // Segunda llamada con caché
        $widgets2 = $service->discover(useCache: true);

        expect($widgets1)->toEqual($widgets2);
    });

    test('clears widget cache', function () {
        $service = app(WidgetService::class);

        $service->discover(useCache: false);
        $service->clearCache();

        // Verificar que la caché se limpió
        expect(Cache::has('widgets_compiled_all'))->toBeFalse();
    });
});

describe('Widget Permissions', function () {
    test('filters widgets by user permissions', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $service = app(WidgetService::class);
        $widgets = $service->getForUser($user);

        expect($widgets)->toBeArray();

        // Verificar que solo obtiene widgets sin permisos o con permisos que tiene
        foreach ($widgets as $widget) {
            if (isset($widget['permission'])) {
                expect($user->can($widget['permission']))->toBeTrue();
            }
        }
    });

    test('admin can see all widgets', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $service = app(WidgetService::class);
        $allWidgets = $service->discover(useCache: false);
        $adminWidgets = $service->getForUser($admin);

        // Admin debería ver todos los widgets activos
        $activeWidgets = collect($allWidgets)->where('active', true)->count();
        expect(count($adminWidgets))->toBe($activeWidgets);
    });

    test('filters inactive widgets', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $service = app(WidgetService::class);
        $widgets = $service->getForUser($user);

        foreach ($widgets as $widget) {
            expect($widget['active'] ?? true)->toBeTrue();
        }
    });
});

describe('Widget API Endpoints', function () {
    test('requires authentication for widget endpoints', function () {
        $response = $this->getJson('/api/widgets');

        $response->assertStatus(401);
    });

    test('returns available widgets for authenticated user', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->getJson('/api/widgets');

        $response->assertStatus(200)
            ->assertJsonStructure(['widgets']);
    });

    test('returns user widget layout', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->getJson('/api/widgets/layout');

        $response->assertStatus(200)
            ->assertJsonStructure(['layout']);
    });

    test('saves user widget layout', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $layout = [
            'widgets' => [
                [
                    'key' => 'welcome',
                    'size' => 'col-span-12',
                    'visible' => true,
                    'config' => null,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->putJson('/api/widgets/layout', $layout);

        $response->assertStatus(200);

        // Verificar que se guardó en la base de datos
        $this->assertDatabaseHas('user_widgets', [
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'visible' => true,
        ]);
    });

    test('resets user widget layout', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Crear algunos widgets personalizados
        UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'position' => 0,
            'size' => 'col-span-6',
            'visible' => false,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/widgets/layout/reset');

        $response->assertStatus(200);

        // Verificar que se eliminaron los widgets personalizados
        $this->assertDatabaseMissing('user_widgets', [
            'user_id' => $user->id,
        ]);
    });

    test('toggles widget visibility', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson('/api/widgets/welcome/toggle');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'visible']);

        // Verificar que se creó el registro
        $this->assertDatabaseHas('user_widgets', [
            'user_id' => $user->id,
            'widget_key' => 'welcome',
        ]);
    });

    test('refreshes widget', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson('/api/widgets/welcome/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'timestamp']);
    });

    test('updates widget configuration', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $config = [
            'config' => [
                'show_greeting' => true,
                'theme' => 'dark',
            ],
        ];

        $response = $this->actingAs($user)
            ->putJson('/api/widgets/welcome/config', $config);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'config']);

        // Verificar que se guardó la configuración
        $userWidget = UserWidget::where('user_id', $user->id)
            ->where('widget_key', 'welcome')
            ->first();

        expect($userWidget->config)->toBe($config['config']);
    });

    test('only admin can clear widget cache', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson('/api/widgets/cache/clear');

        $response->assertStatus(403);
    });

    test('admin can clear widget cache', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->postJson('/api/widgets/cache/clear');

        $response->assertStatus(200);
    });
});

describe('UserWidget Model', function () {
    test('creates user widget', function () {
        $user = User::factory()->create();

        $widget = UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'position' => 0,
            'size' => 'col-span-12',
            'visible' => true,
        ]);

        expect($widget)->toBeInstanceOf(UserWidget::class)
            ->and($widget->user_id)->toBe($user->id)
            ->and($widget->widget_key)->toBe('welcome');
    });

    test('casts config to array', function () {
        $user = User::factory()->create();

        $widget = UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'position' => 0,
            'size' => 'col-span-12',
            'config' => ['key' => 'value'],
            'visible' => true,
        ]);

        expect($widget->config)->toBeArray()
            ->and($widget->config['key'])->toBe('value');
    });

    test('gets layout for user', function () {
        $user = User::factory()->create();

        UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'position' => 0,
            'size' => 'col-span-12',
            'visible' => true,
        ]);

        UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'stats',
            'position' => 1,
            'size' => 'col-span-6',
            'visible' => false,
        ]);

        $layout = UserWidget::getLayoutForUser($user->id);

        expect($layout)->toHaveCount(1) // Solo visible
            ->and($layout->first()->widget_key)->toBe('welcome');
    });

    test('saves layout for user', function () {
        $user = User::factory()->create();

        $widgets = [
            ['key' => 'welcome', 'size' => 'col-span-12', 'visible' => true],
            ['key' => 'stats', 'size' => 'col-span-6', 'visible' => true],
        ];

        UserWidget::saveLayoutForUser($user->id, $widgets);

        $this->assertDatabaseCount('user_widgets', 2);
    });

    test('resets layout for user', function () {
        $user = User::factory()->create();

        UserWidget::create([
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'position' => 0,
            'size' => 'col-span-12',
            'visible' => true,
        ]);

        UserWidget::resetLayoutForUser($user->id);

        $this->assertDatabaseMissing('user_widgets', [
            'user_id' => $user->id,
        ]);
    });

    test('toggles widget visibility', function () {
        $user = User::factory()->create();

        // Primera vez: crear con visible = false (toggle desde default true)
        $visible1 = UserWidget::toggleVisibility($user->id, 'welcome');
        expect($visible1)->toBe(false);

        // Verificar en DB
        $this->assertDatabaseHas('user_widgets', [
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'visible' => false,
        ]);

        // Segunda vez: toggle a visible
        $visible2 = UserWidget::toggleVisibility($user->id, 'welcome');
        expect($visible2)->toBe(true);

        // Verificar en DB
        $this->assertDatabaseHas('user_widgets', [
            'user_id' => $user->id,
            'widget_key' => 'welcome',
            'visible' => true,
        ]);
    });
});

describe('Widget Service', function () {
    test('gets specific widget by key', function () {
        $service = app(WidgetService::class);

        $widget = $service->getWidget('welcome');

        expect($widget)->not->toBeNull()
            ->and($widget['key'])->toBe('welcome');
    });

    test('returns null for non-existent widget', function () {
        $service = app(WidgetService::class);

        $widget = $service->getWidget('non-existent-widget');

        expect($widget)->toBeNull();
    });

    test('validates refresh interval', function () {
        $service = app(WidgetService::class);

        expect($service->validateRefreshInterval(60))->toBeTrue()
            ->and($service->validateRefreshInterval(300))->toBeTrue()
            ->and($service->validateRefreshInterval(3600))->toBeTrue()
            ->and($service->validateRefreshInterval(30))->toBeFalse() // Muy corto
            ->and($service->validateRefreshInterval(7200))->toBeFalse(); // Muy largo
    });

    test('gets default refresh interval', function () {
        $service = app(WidgetService::class);

        $interval = $service->getDefaultRefreshInterval();

        expect($interval)->toBe(300); // 5 minutos por defecto
    });

    test('groups widgets by package', function () {
        $service = app(WidgetService::class);

        $grouped = $service->getGroupedByPackage();

        expect($grouped)->toBeArray()
            ->and($grouped)->toHaveKey('core');
    });
});
