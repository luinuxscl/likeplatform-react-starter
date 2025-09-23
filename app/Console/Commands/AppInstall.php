<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:install {--dev : Instalación de desarrollo (migrate:fresh --seed y optimize:clear)}';

    /**
     * The console command description.
     */
    protected $description = 'Instala y deja operativa la aplicación (migraciones, seeders, limpieza y enlaces)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDev = (bool) $this->option('dev');

        $this->components->info('Iniciando instalación de la aplicación'.($isDev ? ' (modo desarrollo)' : ''));

        // Limpieza previa
        $this->components->task('Limpieza previa (optimize:clear)', function () {
            $this->call('optimize:clear');
            return true;
        });

        if ($isDev) {
            // Instalación de desarrollo: en tests evitamos migrate:fresh por VACUUM en sqlite
            if (app()->runningUnitTests()) {
                $this->components->task('Entorno de tests: ejecutando migrate --seed (sin fresh)', function () {
                    $this->call('migrate', [
                        '--force' => true,
                    ]);
                    $this->call('db:seed', [
                        '--force' => true,
                    ]);
                    // Datos de ejemplo de desarrollo adicionales
                    $this->call('db:seed', [
                        '--class' => \Database\Seeders\DevSampleDataSeeder::class,
                        '--force' => true,
                    ]);
                    return true;
                });
            } else {
                $this->components->task('Ejecutando migrate:fresh --seed', function () {
                    $this->call('migrate:fresh', [
                        '--seed' => true,
                        '--force' => true,
                    ]);
                    // Datos de ejemplo de desarrollo adicionales
                    $this->call('db:seed', [
                        '--class' => \Database\Seeders\DevSampleDataSeeder::class,
                        '--force' => true,
                    ]);
                    return true;
                });
            }
        } else {
            // Instalación normal: migra y ejecuta seeders (si aplica)
            $this->components->task('Ejecutando migraciones', function () {
                $this->call('migrate', [
                    '--force' => true,
                ]);
                return true;
            });

            $this->components->task('Ejecutando seeders (db:seed)', function () {
                $this->call('db:seed', [
                    '--force' => true,
                ]);
                return true;
            });
        }

        // Enlaces y tareas adicionales comunes
        if (!app()->runningUnitTests()) {
            $this->components->task('Creando enlace de almacenamiento (storage:link)', function () {
                $this->call('storage:link');
                return true;
            });
        } else {
            $this->components->info('Omitiendo storage:link en entorno de testing');
        }

        $this->components->task('Limpieza final (optimize:clear)', function () {
            $this->call('optimize:clear');
            return true;
        });

        $this->components->info('Instalación finalizada correctamente.');
        return self::SUCCESS;
    }
}
