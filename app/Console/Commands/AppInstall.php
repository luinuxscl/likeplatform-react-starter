<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:install {--dev : Instalación de desarrollo (migrate:fresh --seed y optimize:clear)} {--fresh : Reinicia la base con migrate:fresh --seed sin datos de desarrollo}';

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
        $isFresh = (bool) $this->option('fresh');

        if ($isDev && $isFresh) {
            $this->components->error('No puedes combinar las opciones --dev y --fresh. Ejecuta el comando usando solo una de ellas.');

            return self::INVALID;
        }

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
        } elseif ($isFresh) {
            if (app()->runningUnitTests()) {
                $this->components->task('Entorno de tests: ejecutando migrate --seed (sin fresh)', function () {
                    $this->call('migrate', [
                        '--force' => true,
                    ]);
                    $this->call('db:seed', [
                        '--force' => true,
                    ]);

                    return true;
                });
            } else {
                $this->components->task('Ejecutando migrate:fresh --seed (sin datos de desarrollo)', function () {
                    $this->call('migrate:fresh', [
                        '--seed' => true,
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
        if (! app()->runningUnitTests()) {
            $this->components->task('Creando enlace de almacenamiento (storage:link)', function () {
                $this->call('storage:link');

                return true;
            });
        } else {
            $this->components->info('Omitiendo storage:link en entorno de testing');
        }

        if (! $isDev && ! app()->runningUnitTests()) {
            $this->promptForAdmin();
        }

        $this->components->task('Limpieza final (optimize:clear)', function () {
            $this->call('optimize:clear');

            return true;
        });

        $this->components->info('Instalación finalizada correctamente.');

        return self::SUCCESS;
    }

    protected function promptForAdmin(): void
    {
        $this->components->twoColumnDetail('Verificando usuario administrador', '');

        $existingAdmin = \App\Models\User::role('admin')->exists();

        if ($existingAdmin) {
            $this->components->info('Ya existe al menos un usuario con rol admin.');

            return;
        }

        if (! $this->components->confirm('¿Deseas crear un usuario administrador ahora?', true)) {
            $this->components->warn('Instalación sin usuario administrador. Puedes crearlo luego con php artisan make:user-admin');

            return;
        }

        $name = $this->ask('Nombre del administrador', 'Linus Torvalds');
        $email = $this->ask('Email del administrador', 'admin@demo.com');
        $password = $this->secret('Password (mínimo 8 caracteres) (dejar vacío para "password")') ?? '';

        if ($password === '') {
            $password = 'password';
        }

        if (strlen($password) < 8) {
            $this->components->error('Password demasiado corto. Cancela y vuelve a ejecutar el comando si deseas intentarlo nuevamente.');

            return;
        }

        $user = \App\Models\User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $this->components->info("Usuario admin creado: {$user->email}");
    }
}
