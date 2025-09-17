# Estructura del Paquete de Expansión Laravel React

## Estructura de Directorios Completa

```
packages/company/laravel-react-expansion/
├── composer.json                 # Configuración del paquete Composer
├── README.md                     # Documentación principal
├── CHANGELOG.md                  # Historial de cambios
├── LICENSE.md                    # Licencia del paquete
├── phpunit.xml                   # Configuración PHPUnit
├── .gitignore                    # Archivos ignorados por Git
├── src/                          # Código fuente PHP
│   ├── Console/                  # Comandos Artisan
│   │   └── Commands/
│   │       ├── InstallExpansionCommand.php
│   │       ├── PublishAssetsCommand.php
│   │       └── SetupPermissionsCommand.php
│   ├── Http/                     # Controladores y middleware HTTP
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── PermissionController.php
│   │   │   │   ├── MediaController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   └── ActivityLogController.php
│   │   │   ├── Dashboard/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── WidgetController.php
│   │   │   │   └── AnalyticsController.php
│   │   │   └── Settings/
│   │   │       ├── RoleManagementController.php
│   │   │       └── SystemConfigController.php
│   │   ├── Middleware/
│   │   │   ├── CheckPermissionMiddleware.php
│   │   │   ├── LogActivityMiddleware.php
│   │   │   └── ApiRateLimitMiddleware.php
│   │   └── Requests/
│   │       ├── RoleRequest.php
│   │       ├── PermissionRequest.php
│   │       ├── MediaUploadRequest.php
│   │       └── NotificationRequest.php
│   ├── Models/                   # Modelos Eloquent
│   │   ├── ActivityLog.php
│   │   ├── MediaFile.php
│   │   ├── Notification.php
│   │   ├── DashboardWidget.php
│   │   └── SystemConfig.php
│   ├── Services/                 # Servicios de lógica de negocio
│   │   ├── RolePermissionService.php
│   │   ├── MediaUploadService.php
│   │   ├── NotificationService.php
│   │   ├── ActivityLogService.php
│   │   └── DashboardService.php
│   ├── Traits/                   # Traits reutilizables
│   │   ├── HasActivityLogs.php
│   │   ├── HasPermissions.php
│   │   └── HasNotifications.php
│   ├── Observers/                # Observadores de modelos
│   │   ├── UserObserver.php
│   │   └── ActivityLogObserver.php
│   ├── Events/                   # Eventos
│   │   ├── UserRoleAssigned.php
│   │   ├── MediaFileUploaded.php
│   │   └── NotificationSent.php
│   ├── Listeners/                # Listeners de eventos
│   │   ├── LogUserActivity.php
│   │   ├── SendNotificationEmail.php
│   │   └── ProcessMediaFile.php
│   ├── Jobs/                     # Jobs en cola
│   │   ├── ProcessImageThumbnails.php
│   │   ├── SendBulkNotifications.php
│   │   └── CleanupOldLogs.php
│   ├── Exceptions/               # Excepciones personalizadas
│   │   ├── InsufficientPermissionException.php
│   │   ├── MediaUploadException.php
│   │   └── InvalidConfigurationException.php
│   ├── Providers/                # Service Providers
│   │   ├── ExpansionServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── Facades/                  # Facades
│       ├── ExpansionManager.php
│       ├── MediaManager.php
│       └── NotificationManager.php
├── resources/                    # Recursos del paquete
│   ├── js/                       # Componentes React/TypeScript
│   │   ├── components/
│   │   │   ├── expansion/
│   │   │   │   ├── roles/
│   │   │   │   │   ├── RoleManagement.tsx
│   │   │   │   │   ├── RoleSelector.tsx
│   │   │   │   │   ├── PermissionMatrix.tsx
│   │   │   │   │   └── UserRoleAssignment.tsx
│   │   │   │   ├── media/
│   │   │   │   │   ├── MediaUploader.tsx
│   │   │   │   │   ├── MediaGallery.tsx
│   │   │   │   │   ├── FilePreview.tsx
│   │   │   │   │   └── MediaSelector.tsx
│   │   │   │   ├── dashboard/
│   │   │   │   │   ├── DashboardGrid.tsx
│   │   │   │   │   ├── WidgetContainer.tsx
│   │   │   │   │   ├── widgets/
│   │   │   │   │   │   ├── StatCard.tsx
│   │   │   │   │   │   ├── ChartWidget.tsx
│   │   │   │   │   │   ├── RecentActivity.tsx
│   │   │   │   │   │   └── QuickActions.tsx
│   │   │   │   │   └── WidgetEditor.tsx
│   │   │   │   ├── notifications/
│   │   │   │   │   ├── NotificationCenter.tsx
│   │   │   │   │   ├── NotificationItem.tsx
│   │   │   │   │   ├── NotificationSettings.tsx
│   │   │   │   │   └── BulkNotificationSender.tsx
│   │   │   │   ├── activity/
│   │   │   │   │   ├── ActivityLogViewer.tsx
│   │   │   │   │   ├── ActivityFilters.tsx
│   │   │   │   │   ├── ActivityTimeline.tsx
│   │   │   │   │   └── ActivityExporter.tsx
│   │   │   │   └── ui/
│   │   │   │       ├── DataTable.tsx
│   │   │   │       ├── FilterDropdown.tsx
│   │   │   │       ├── SearchInput.tsx
│   │   │   │       ├── ConfirmDialog.tsx
│   │   │   │       └── LoadingSpinner.tsx
│   │   │   └── hooks/
│   │   │       ├── useRoles.tsx
│   │   │       ├── usePermissions.tsx
│   │   │       ├── useMedia.tsx
│   │   │       ├── useNotifications.tsx
│   │   │       ├── useActivityLogs.tsx
│   │   │       └── useDashboard.tsx
│   │   ├── pages/
│   │   │   ├── expansion/
│   │   │   │   ├── roles/
│   │   │   │   │   ├── index.tsx
│   │   │   │   │   ├── create.tsx
│   │   │   │   │   └── edit.tsx
│   │   │   │   ├── media/
│   │   │   │   │   ├── index.tsx
│   │   │   │   │   └── gallery.tsx
│   │   │   │   ├── dashboard/
│   │   │   │   │   ├── advanced.tsx
│   │   │   │   │   └── analytics.tsx
│   │   │   │   ├── notifications/
│   │   │   │   │   ├── index.tsx
│   │   │   │   │   └── settings.tsx
│   │   │   │   └── activity/
│   │   │   │       ├── index.tsx
│   │   │   │       └── reports.tsx
│   │   │   └── settings/
│   │   │       ├── roles.tsx
│   │   │       ├── system.tsx
│   │   │       └── integrations.tsx
│   │   ├── layouts/
│   │   │   └── expansion/
│   │   │       ├── ManagementLayout.tsx
│   │   │       └── DashboardLayout.tsx
│   │   ├── types/
│   │   │   ├── expansion.d.ts
│   │   │   ├── roles.d.ts
│   │   │   ├── media.d.ts
│   │   │   ├── notifications.d.ts
│   │   │   └── dashboard.d.ts
│   │   └── utils/
│   │       ├── permissions.ts
│   │       ├── media.ts
│   │       ├── notifications.ts
│   │       └── formatting.ts
│   ├── css/
│   │   └── expansion.css
│   └── views/
│       └── expansion/
│           ├── emails/
│           │   ├── notification.blade.php
│           │   └── welcome.blade.php
│           └── exports/
│               ├── activity-report.blade.php
│               └── user-report.blade.php
├── database/                     # Base de datos
│   ├── migrations/
│   │   ├── 2024_01_01_000000_add_expansion_permissions_table.php
│   │   ├── 2024_01_01_000001_create_media_files_table.php
│   │   ├── 2024_01_01_000002_create_notifications_table.php
│   │   ├── 2024_01_01_000003_create_activity_logs_table.php
│   │   ├── 2024_01_01_000004_create_dashboard_widgets_table.php
│   │   └── 2024_01_01_000005_create_system_configs_table.php
│   ├── seeders/
│   │   ├── ExpansionSeeder.php
│   │   ├── RolesAndPermissionsSeeder.php
│   │   ├── DefaultWidgetsSeeder.php
│   │   └── SystemConfigsSeeder.php
│   └── factories/
│       ├── MediaFileFactory.php
│       ├── NotificationFactory.php
│       ├── ActivityLogFactory.php
│       └── DashboardWidgetFactory.php
├── config/                       # Configuración
│   └── expansion.php
├── routes/                       # Rutas
│   ├── api.php
│   ├── web.php
│   └── console.php
├── tests/                        # Tests
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── RolePermissionServiceTest.php
│   │   │   ├── MediaUploadServiceTest.php
│   │   │   ├── NotificationServiceTest.php
│   │   │   └── ActivityLogServiceTest.php
│   │   ├── Models/
│   │   │   ├── ActivityLogTest.php
│   │   │   ├── MediaFileTest.php
│   │   │   └── NotificationTest.php
│   │   └── Traits/
│   │       ├── HasActivityLogsTest.php
│   │       └── HasPermissionsTest.php
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── RoleManagementTest.php
│   │   │   ├── MediaUploadTest.php
│   │   │   ├── NotificationTest.php
│   │   │   └── ActivityLogTest.php
│   │   ├── Dashboard/
│   │   │   ├── DashboardTest.php
│   │   │   └── WidgetTest.php
│   │   └── Settings/
│   │       ├── RoleSettingsTest.php
│   │       └── SystemSettingsTest.php
│   ├── Browser/
│   │   ├── RoleManagementTest.php
│   │   ├── MediaUploadTest.php
│   │   ├── DashboardTest.php
│   │   └── NotificationTest.php
│   └── TestCase.php
├── docs/                         # Documentación
│   ├── installation.md
│   ├── configuration.md
│   ├── api-reference.md
│   ├── components.md
│   ├── troubleshooting.md
│   └── examples/
│       ├── role-management.md
│       ├── media-upload.md
│       ├── dashboard-widgets.md
│       └── notifications.md
└── .github/                      # GitHub workflows
    └── workflows/
        ├── tests.yml
        ├── code-quality.yml
        └── release.yml
```

## Archivos de Configuración Principal

### composer.json
```json
{
    "name": "company/laravel-react-expansion",
    "description": "Paquete de expansión enterprise para Laravel 12 React Starter Kit",
    "keywords": [
        "laravel",
        "react",
        "inertia",
        "starter-kit",
        "enterprise",
        "roles",
        "permissions",
        "media",
        "dashboard"
    ],
    "homepage": "https://github.com/company/laravel-react-expansion",
    "license": "MIT",
    "type": "library",
    "authors": [
        {
            "name": "Company Development Team",
            "email": "dev@company.com",
            "role": "Developer"
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "inertiajs/inertia-laravel": "^2.0",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-medialibrary": "^11.0",
        "spatie/laravel-activitylog": "^4.8"
    },
    "require-dev": {
        "laravel/boost": "^1.1",
        "laravel/pint": "^1.18",
        "pestphp/pest": "^4.1",
        "pestphp/pest-plugin-laravel": "^4.0",
        "phpstan/phpstan": "^1.10"
    },
    "autoload": {
        "psr-4": {
            "Company\\LaravelReactExpansion\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Company\\LaravelReactExpansion\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "analyse": "vendor/bin/phpstan analyse",
        "test": "vendor/bin/pest",
        "test-coverage": "vendor/bin/pest --coverage",
        "format": "vendor/bin/pint"
    },
    "config": {
        "sort-packages": true
    },
    "extra": {
        "laravel": {
            "providers": [
                "Company\\LaravelReactExpansion\\Providers\\ExpansionServiceProvider"
            ]
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### package.json (para assets frontend)
```json
{
    "name": "@company/laravel-react-expansion",
    "version": "1.0.0",
    "description": "Frontend assets for Laravel React Expansion package",
    "main": "resources/js/index.ts",
    "scripts": {
        "build": "vite build",
        "dev": "vite",
        "test": "vitest",
        "test:ui": "vitest --ui",
        "lint": "eslint resources/js --ext .ts,.tsx",
        "lint:fix": "eslint resources/js --ext .ts,.tsx --fix",
        "type-check": "tsc --noEmit"
    },
    "dependencies": {
        "@tanstack/react-query": "^5.0.0",
        "recharts": "^2.8.0",
        "react-dropzone": "^14.2.0",
        "date-fns": "^3.0.0",
        "react-hook-form": "^7.48.0",
        "@hookform/resolvers": "^3.3.0",
        "zod": "^3.22.0"
    },
    "devDependencies": {
        "@testing-library/react": "^14.0.0",
        "@testing-library/jest-dom": "^6.1.0",
        "@vitejs/plugin-react": "^4.1.0",
        "vitest": "^1.0.0",
        "jsdom": "^23.0.0"
    },
    "peerDependencies": {
        "react": "^19.0.0",
        "react-dom": "^19.0.0",
        "@inertiajs/react": "^2.1.0"
    }
}
```

## Service Provider Principal

### src/Providers/ExpansionServiceProvider.php
```php
<?php

namespace Company\LaravelReactExpansion\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Company\LaravelReactExpansion\Console\Commands\InstallExpansionCommand;
use Company\LaravelReactExpansion\Console\Commands\PublishAssetsCommand;
use Company\LaravelReactExpansion\Console\Commands\SetupPermissionsCommand;

class ExpansionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/expansion.php', 'expansion'
        );

        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'expansion');

        $this->publishes([
            __DIR__.'/../../config/expansion.php' => config_path('expansion.php'),
        ], 'expansion-config');

        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/expansion'),
        ], 'expansion-assets');

        $this->publishes([
            __DIR__.'/../../resources/css' => resource_path('css/expansion'),
        ], 'expansion-styles');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'expansion-migrations');

        $this->publishes([
            __DIR__.'/../../database/seeders' => database_path('seeders'),
        ], 'expansion-seeders');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallExpansionCommand::class,
                PublishAssetsCommand::class,
                SetupPermissionsCommand::class,
            ]);
        }

        $this->registerGates();
    }

    protected function registerGates(): void
    {
        Gate::define('manage-roles', function ($user) {
            return $user->hasPermissionTo('manage roles');
        });

        Gate::define('manage-media', function ($user) {
            return $user->hasPermissionTo('manage media');
        });

        Gate::define('view-activity-logs', function ($user) {
            return $user->hasPermissionTo('view activity logs');
        });

        Gate::define('manage-dashboard', function ($user) {
            return $user->hasPermissionTo('manage dashboard');
        });
    }
}
```

## Configuración Principal

### config/expansion.php
```php
<?php

return [
    'features' => [
        'roles_permissions' => env('EXPANSION_ROLES_PERMISSIONS', true),
        'media_management' => env('EXPANSION_MEDIA_MANAGEMENT', true),
        'advanced_dashboard' => env('EXPANSION_ADVANCED_DASHBOARD', true),
        'notifications' => env('EXPANSION_NOTIFICATIONS', true),
        'activity_logs' => env('EXPANSION_ACTIVITY_LOGS', true),
        'api_endpoints' => env('EXPANSION_API_ENDPOINTS', true),
    ],

    'permissions' => [
        'default_roles' => ['admin', 'manager', 'user'],
        'super_admin_email' => env('SUPER_ADMIN_EMAIL'),
        'auto_assign_default_role' => env('AUTO_ASSIGN_DEFAULT_ROLE', true),
        'default_role' => env('DEFAULT_ROLE', 'user'),
    ],

    'media' => [
        'disk' => env('EXPANSION_MEDIA_DISK', 'public'),
        'max_file_size' => env('EXPANSION_MAX_FILE_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'image_optimization' => env('EXPANSION_IMAGE_OPTIMIZATION', true),
        'generate_thumbnails' => env('EXPANSION_GENERATE_THUMBNAILS', true),
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
    ],

    'dashboard' => [
        'cache_duration' => env('EXPANSION_DASHBOARD_CACHE', 300), // seconds
        'default_widgets' => [
            'stats_overview',
            'recent_activity',
            'quick_actions',
            'system_status',
        ],
        'max_widgets_per_user' => env('EXPANSION_MAX_WIDGETS', 12),
    ],

    'notifications' => [
        'channels' => ['database', 'mail', 'broadcast'],
        'queue' => env('EXPANSION_NOTIFICATIONS_QUEUE', 'default'),
        'batch_size' => env('EXPANSION_NOTIFICATION_BATCH_SIZE', 100),
        'rate_limit' => env('EXPANSION_NOTIFICATION_RATE_LIMIT', 60), // per minute
    ],

    'activity_logs' => [
        'enabled' => env('EXPANSION_ACTIVITY_LOGS', true),
        'retention_days' => env('EXPANSION_LOG_RETENTION_DAYS', 90),
        'log_auth_events' => env('EXPANSION_LOG_AUTH_EVENTS', true),
        'log_model_events' => env('EXPANSION_LOG_MODEL_EVENTS', true),
        'exclude_attributes' => ['password', 'remember_token'],
    ],

    'api' => [
        'rate_limit' => env('EXPANSION_API_RATE_LIMIT', '60,1'),
        'middleware' => ['api', 'auth:sanctum'],
        'pagination' => [
            'per_page' => env('EXPANSION_API_PER_PAGE', 15),
            'max_per_page' => env('EXPANSION_API_MAX_PER_PAGE', 100),
        ],
    ],
];
```

## Rutas Principales

### routes/web.php
```php
<?php

use Company\LaravelReactExpansion\Http\Controllers\Dashboard\DashboardController;
use Company\LaravelReactExpansion\Http\Controllers\Settings\RoleManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('expansion')->name('expansion.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('roles', RoleManagementController::class);
        Route::get('system', [SystemConfigController::class, 'index'])->name('system');
    });

    // Media
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/gallery', [MediaController::class, 'gallery'])->name('gallery');
    });

    // Activity Logs
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/reports', [ActivityLogController::class, 'reports'])->name('reports');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
    });
});
```

### routes/api.php
```php
<?php

use Company\LaravelReactExpansion\Http\Controllers\Api\RoleController;
use Company\LaravelReactExpansion\Http\Controllers\Api\MediaController;
use Company\LaravelReactExpansion\Http\Controllers\Api\NotificationController;
use Company\LaravelReactExpansion\Http\Controllers\Api\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('expansion')->name('expansion.api.')->group(function () {
    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // Media Management
    Route::prefix('media')->name('media.')->group(function () {
        Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    });

    // Activity Logs
    Route::prefix('activity-logs')->name('activity.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
    });

    // Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/widgets', [DashboardController::class, 'widgets'])->name('widgets');
        Route::post('/widgets', [DashboardController::class, 'saveWidgets'])->name('widgets.save');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
    });
});
```

## TypeScript Types

### resources/js/types/expansion.d.ts
```typescript
export interface ExpansionConfig {
    features: {
        roles_permissions: boolean;
        media_management: boolean;
        advanced_dashboard: boolean;
        notifications: boolean;
        activity_logs: boolean;
        api_endpoints: boolean;
    };
    permissions: string[];
    media: {
        max_file_size: number;
        allowed_extensions: string[];
    };
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: Permission[];
    users_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export interface MediaFile {
    id: number;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    url: string;
    thumbnail_url?: string;
    created_at: string;
    updated_at: string;
}

export interface ActivityLog {
    id: number;
    log_name: string;
    description: string;
    subject_type: string;
    subject_id: number;
    causer_type: string;
    causer_id: number;
    properties: Record<string, any>;
    created_at: string;
}

export interface DashboardWidget {
    id: string;
    type: string;
    title: string;
    config: Record<string, any>;
    position: {
        x: number;
        y: number;
        width: number;
        height: number;
    };
}

export interface NotificationItem {
    id: string;
    type: string;
    title: string;
    message: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
}
```

Esta estructura proporciona:

1. **Organización modular** por funcionalidad
2. **Separación clara** entre backend y frontend
3. **Tests completos** para todas las capas
4. **Configuración flexible** mediante archivos de config
5. **TypeScript types** para type safety
6. **Documentación estructurada** para desarrolladores
7. **CI/CD workflows** para automatización
8. **Compatibilidad total** con el starter kit Laravel 12

El paquete está diseñado para ser instalado y configurado con comandos simples, manteniendo la filosofía plug-and-play del ecosistema Laravel.
