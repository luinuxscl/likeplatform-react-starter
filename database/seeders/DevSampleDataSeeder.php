<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DevSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 100 usuarios con datos "realistas"
        // - 70% verificados
        // - created_at en los últimos 180 días
        $users = User::factory()
            ->count(100)
            ->state(function () {
                return [
                    'email_verified_at' => fake()->boolean(70) ? now() : null,
                    'created_at' => fake()->dateTimeBetween('-180 days', 'now'),
                    'updated_at' => now(),
                ];
            })
            ->create();

        // Asignar rol admin a 5 de esos 100 usuarios
        // (sin tocar al admin principal ya creado por AdminUserSeeder)
        $adminCandidates = $users->random(min(5, $users->count()));
        foreach ($adminCandidates as $u) {
            if (!$u->hasRole('admin')) {
                $u->assignRole('admin');
            }
        }
    }
}
