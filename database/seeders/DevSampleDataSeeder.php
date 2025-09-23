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
        // Crear 500 usuarios con datos "realistas"
        // - 70% verificados
        // - created_at no-uniforme en los últimos 12 meses (mayor probabilidad en meses recientes)
        $created = collect();

        // Pesos para 12 meses (0 = mes actual, 11 = hace 11 meses)
        $weights = collect(range(12, 1)); // [12, 11, ..., 1]
        $totalWeight = $weights->sum();

        for ($i = 0; $i < 500; $i++) {
            // Elegir un mes con probabilidad proporcional a su peso
            $pick = random_int(1, $totalWeight);
            $acc = 0;
            $monthOffset = 0;
            foreach ($weights as $idx => $w) {
                $acc += $w;
                if ($pick <= $acc) {
                    $monthOffset = $idx; // 0..11
                    break;
                }
            }

            $start = now()->copy()->subMonths($monthOffset)->startOfMonth();
            $end = now()->copy()->subMonths($monthOffset)->endOfMonth();

            $user = User::factory()
                ->state([
                    'email_verified_at' => fake()->boolean(70) ? now() : null,
                    'created_at' => fake()->dateTimeBetween($start, $end),
                    'updated_at' => now(),
                ])
                ->create();

            $created->push($user);
        }

        // Asignar rol admin a 5 de esos 100 usuarios
        // (sin tocar al admin principal ya creado por AdminUserSeeder)
        $adminCandidates = $created->random(min(5, $created->count()));
        foreach ($adminCandidates as $u) {
            if (!$u->hasRole('admin')) {
                $u->assignRole('admin');
            }
        }
    }
}
