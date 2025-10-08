<?php

namespace Like\Fcv\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
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
        $this->components?->task('Publicando configuración', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-config',
                '--force' => true,
            ]);
        });
    }

    protected function publishFrontend(): void
    {
        // Publicar assets de JavaScript/TypeScript
        $this->components?->task('Publicando componentes React/Inertia', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-js',
                '--force' => true,
            ]);
        });

        // Publicar vistas Blade (si existen)
        $this->components?->task('Publicando vistas Blade', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-views',
                '--force' => true,
            ]);
        });

        // Publicar assets públicos (CSS, JS, imágenes, etc.)
        $this->components?->task('Publicando assets públicos', function () {
            $this->call('vendor:publish', [
                '--tag' => 'fcv-assets',
                '--force' => true,
            ]);
        });

        // Ejecutar npm install y compilación de assets (si es necesario)
        if ($this->confirm('¿Desea instalar las dependencias de NPM y compilar los assets?', true)) {
            $this->components?->task('Instalando dependencias de NPM', function () {
                $this->executeCommand('npm install', base_path());
            });

            $this->components?->task('Compilando assets', function () {
                $this->executeCommand('npm run build', base_path());
            });
        }
    }

    /**
     * Ejecuta un comando en el directorio especificado
     */
    protected function executeCommand(string $command, string $directory): bool
    {
        $process = Process::path($directory)
            ->tty()
            ->run($command, function (string $type, string $output) {
                $this->output->write($output);
            });

        return $process->successful();
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
