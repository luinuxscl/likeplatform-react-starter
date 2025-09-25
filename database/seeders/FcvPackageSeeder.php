<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FcvPackageSeeder extends Seeder
{
    public function run(): void
    {
        $class = 'Like\\Fcv\\Database\\Seeders\\FcvDemoSeeder';

        if (! class_exists($class)) {
            $path = base_path('packages/fcv/database/Seeders/FcvDemoSeeder.php');
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists($class)) {
            app($class)->run();
        } else {
            $this->command?->warn('No se pudo cargar el seeder del paquete FCV.');
        }
    }
}
