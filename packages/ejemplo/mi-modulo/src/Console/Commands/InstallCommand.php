<?php

namespace Ejemplo\MiModulo\Console\Commands;

use Illuminate\Console\Command;

/**
 * Comando de instalación del módulo
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mi-modulo:install';

    /**
     * The console command description.
     */
    protected $description = 'Instala el módulo Mi Módulo y configura todo lo necesario';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Instalando Mi Módulo...');
        $this->newLine();

        // 1. Publicar configuración
        $this->info('📝 Publicando configuración...');
        $this->call('vendor:publish', [
            '--tag' => 'mi-modulo-config',
            '--force' => true,
        ]);

        // 2. Ejecutar migraciones
        $this->info('🗄️  Ejecutando migraciones...');
        $this->call('migrate', [
            '--path' => 'packages/ejemplo/mi-modulo/database/migrations',
            '--force' => true,
        ]);

        // 3. Ejecutar seeders de permisos
        $this->info('🔐 Creando permisos...');
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\MiModuloPermissionsSeeder',
            '--force' => true,
        ]);

        // 4. Limpiar caché
        $this->info('🧹 Limpiando caché...');
        $this->call('cache:clear');
        $this->call('config:clear');

        $this->newLine();
        $this->info('✅ Mi Módulo instalado exitosamente!');
        $this->newLine();
        $this->line('El módulo ya está disponible en el sidebar de la aplicación.');
        $this->line('Visita /mi-modulo para comenzar a usarlo.');

        return self::SUCCESS;
    }
}
