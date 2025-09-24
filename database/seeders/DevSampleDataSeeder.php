<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\UserSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
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
            if ($end->greaterThan(now())) {
                $end = now();
            }
            if ($start->greaterThan($end)) {
                $start = $end->copy()->subDays(1);
            }

            $user = User::factory()
                ->state([
                    'email_verified_at' => fake()->boolean(70) ? now() : null,
                    'created_at' => fake()->dateTimeBetween($start, $end),
                    'updated_at' => now(),
                ])
                ->create();

            $created->push($user);
        }

        // Asignar rol admin a 5 de esos usuarios
        // (sin tocar al admin principal ya creado por AdminUserSeeder)
        $adminCandidates = $created->random(min(5, $created->count()));
        foreach ($adminCandidates as $u) {
            if (!$u->hasRole('admin')) {
                $u->assignRole('admin');
            }
        }

        // Registrar logs de auditoría ficticios
        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty() && $created->isNotEmpty()) {
            $actions = collect(['profile_updated', 'role_assigned', 'password_reset', 'status_disabled', 'status_enabled']);
            $sampleSize = min(60, $created->count());
            $sampledUsers = $created->take($sampleSize);

            foreach ($sampledUsers as $user) {
                $actor = $admins->random();
                $action = $actions->random();

                $oldValues = [];
                $newValues = [];

                switch ($action) {
                    case 'profile_updated':
                        $oldValues = [
                            'name' => $user->name,
                            'phone' => null,
                        ];
                        $newValues = [
                            'name' => $user->name.' '.fake()->lastName(),
                            'phone' => fake()->e164PhoneNumber(),
                        ];
                        break;
                    case 'role_assigned':
                        $oldValues = [
                            'roles' => $user->roles->pluck('name')->toArray(),
                        ];
                        $newValues = [
                            'roles' => array_values(array_unique(array_merge($oldValues['roles'], [fake()->randomElement(['editor', 'manager', 'viewer'])]))),
                        ];
                        break;
                    case 'password_reset':
                        $oldValues = ['must_change_password' => false];
                        $newValues = ['must_change_password' => true];
                        break;
                    case 'status_disabled':
                        $oldValues = ['status' => 'active'];
                        $newValues = ['status' => 'disabled'];
                        break;
                    case 'status_enabled':
                        $oldValues = ['status' => 'disabled'];
                        $newValues = ['status' => 'active'];
                        break;
                }

                $baseCreated = $user->created_at instanceof Carbon ? $user->created_at : Carbon::parse($user->created_at ?? now()->subMonths(6));
                if ($baseCreated->greaterThan(now())) {
                    $baseCreated = now()->copy()->subDays(1);
                }

                $timestamp = Carbon::instance(fake()->dateTimeBetween($baseCreated, now()))->setTimezone('UTC');

                AuditLog::create([
                    'user_id' => $actor->id,
                    'action' => $action,
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'metadata' => [
                        'channel' => fake()->randomElement(['web', 'api', 'admin']),
                        'request_id' => (string) Str::uuid(),
                    ],
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'url' => fake()->url(),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Registrar sesiones ficticias
        if ($created->isNotEmpty()) {
            $sessionSample = min(80, $created->count());
            $sessionUsers = $created->take($sessionSample);

            foreach ($sessionUsers as $user) {
                $loginAt = Carbon::instance(fake()->dateTimeBetween('-45 days', '-1 days'));
                $lastActivity = (clone $loginAt)->addMinutes(random_int(10, 360));
                $logoutAt = fake()->boolean(55) ? (clone $lastActivity)->addMinutes(random_int(5, 90)) : null;

                UserSession::create([
                    'user_id' => $user->id,
                    'session_id' => (string) Str::uuid(),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'device' => fake()->randomElement(['Desktop', 'Mobile', 'Tablet']),
                    'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'Android', 'iOS']),
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                    'login_at' => $loginAt,
                    'last_activity_at' => $lastActivity,
                    'logout_at' => $logoutAt,
                    'metadata' => [
                        'locale' => fake()->locale(),
                        'timezone' => fake()->timezone(),
                    ],
                    'created_at' => $loginAt,
                    'updated_at' => $lastActivity,
                ]);
            }
        }
    }
}
