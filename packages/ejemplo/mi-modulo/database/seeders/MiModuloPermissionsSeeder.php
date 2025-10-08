<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder de permisos del módulo Mi Módulo
 */
class MiModuloPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos del módulo
        $permissions = [
            'mi-modulo.view' => 'Ver el módulo principal',
            'mi-modulo.items.view' => 'Ver items del módulo',
            'mi-modulo.items.create' => 'Crear items del módulo',
            'mi-modulo.items.edit' => 'Editar items del módulo',
            'mi-modulo.items.delete' => 'Eliminar items del módulo',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Asignar todos los permisos al rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        $this->command->info('Permisos del módulo creados exitosamente');
    }
}
