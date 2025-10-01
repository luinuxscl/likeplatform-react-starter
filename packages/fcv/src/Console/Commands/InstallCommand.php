<?php

namespace Like\Fcv\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Like\Fcv\Database\Seeders\FcvBaseSeeder;
use Like\Fcv\Database\Seeders\FcvDemoSeeder;
use Spatie\Permission\Models\Role;

class InstallCommand extends Command
{
    protected $signature = 'fcv:install {--dev : Instala datos de prueba (alumnos, funcionarios, cursos).}';

    protected $description = 'Instala recursos y datos base del paquete FCV. Use --dev para datos de prueba masivos.';

    public function handle(): int
    {
        $this->components?->info('Iniciando instalación del paquete FCV…');

        $this->publishConfig();
        $this->publishFrontend();

        $this->components?->task('Aplicando migraciones del paquete', function () {
            $this->call('migrate', [
                '--path' => 'packages/fcv/database/migrations',
                '--force' => true,
            ]);
        });

        $this->ensureDefaultRoles();

        $this->components?->task('Sembrando datos base', function () {
            $this->call('db:seed', ['--class' => FcvBaseSeeder::class, '--force' => true]);
        });

        if ($this->option('dev')) {
            $this->components?->task('Sembrando datos demo (alumnos y funcionarios)', function () {
                $this->call('db:seed', ['--class' => FcvDemoSeeder::class, '--force' => true]);
            });
        }

        $this->components?->info('Instalación FCV finalizada.');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        if (file_exists(config_path('fcv.php'))) {
            return;
        }

        $this->components?->task('Publicando configuración', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-config',
                '--force' => false,
            ]);
        });
    }

    protected function publishFrontend(): void
    {
        $target = resource_path('js/pages/fcv');
        if (is_dir($target) && count(glob($target.'/*')) > 0) {
            return;
        }

        $this->components?->task('Publicando componentes Inertia (guardia)', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-js',
                '--force' => false,
            ]);
        });
    }

    protected function ensureDefaultRoles(): void
    {
        $roles = array_filter((array) config('fcv.default_roles', []));

        if (empty($roles)) {
            return;
        }

        $guard = config('auth.defaults.guard', 'web');

        $this->components?->task('Creando roles base', function () use ($roles, $guard) {
            foreach ($roles as $role) {
                Role::findOrCreate($role, $guard);
            }
        });
    }
}
