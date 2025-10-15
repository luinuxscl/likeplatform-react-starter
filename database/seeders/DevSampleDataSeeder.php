<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\UserSession;
use App\Services\ApiKeys\ApiKeyManager;
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
        // Crear 20 usuarios con datos "realistas" sin asignar roles
        $users = User::factory()
            ->count(20)
            ->state(fn () => [
                'email_verified_at' => fake()->boolean(60) ? now() : null,
                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ])
            ->create();

        $users->each(fn (User $user) => $user->syncRoles([]));

        $admin = User::where('email', 'admin@demo.com')->first();

        // Registrar logs de auditoría ficticios mínimos
        if ($admin && $users->isNotEmpty()) {
            $actions = collect(['profile_updated', 'password_reset', 'status_disabled', 'status_enabled']);
            $sampledUsers = $users->shuffle()->take(10);

            foreach ($sampledUsers as $user) {
                $action = $actions->random();

                [$oldValues, $newValues] = match ($action) {
                    'profile_updated' => [
                        ['name' => $user->name, 'phone' => null],
                        ['name' => $user->name.' '.fake()->lastName(), 'phone' => fake()->e164PhoneNumber()],
                    ],
                    'password_reset' => [
                        ['must_change_password' => false],
                        ['must_change_password' => true],
                    ],
                    'status_disabled' => [
                        ['status' => 'active'],
                        ['status' => 'disabled'],
                    ],
                    'status_enabled' => [
                        ['status' => 'disabled'],
                        ['status' => 'active'],
                    ],
                    default => [[], []],
                };

                $timestamp = Carbon::instance(fake()->dateTimeBetween('-45 days', 'now'))->setTimezone('UTC');

                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => $action,
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'metadata' => [
                        'channel' => fake()->randomElement(['web', 'admin']),
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

        // Registrar sesiones ficticias para subset de usuarios
        if ($users->isNotEmpty()) {
            $sessionUsers = $users->shuffle()->take(8);

            foreach ($sessionUsers as $user) {
                $loginAt = Carbon::instance(fake()->dateTimeBetween('-30 days', '-1 days'));
                $lastActivity = (clone $loginAt)->addMinutes(random_int(15, 240));
                $logoutAt = fake()->boolean(60) ? (clone $lastActivity)->addMinutes(random_int(5, 60)) : null;

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

        // Generar API keys de ejemplo para validar funcionalidades
        $apiKeyManager = app(ApiKeyManager::class);
        $allowedAbilities = collect(config('sanctum.user_abilities', ['read']));

        if ($admin && $users->count() > 3) {
            $adminIssuedUsers = $users->shuffle()->take(5);

            foreach ($adminIssuedUsers as $user) {
                $result = $apiKeyManager->createToken(
                    $user,
                    'Integración '.$user->id.'-'.Str::upper(Str::random(3)),
                    fake()->sentence(4),
                    ['*'],
                    fake()->boolean(50) ? now()->addDays(random_int(30, 120)) : null,
                    $admin,
                    false,
                );

                $tokenModel = PersonalAccessToken::find($result['id']);

                if ($tokenModel && fake()->boolean(70)) {
                    $tokenModel->forceFill([
                        'last_used_at' => Carbon::instance(fake()->dateTimeBetween('-15 days', 'now')),
                        'last_used_ip' => fake()->ipv4(),
                        'last_used_user_agent' => fake()->userAgent(),
                    ])->save();
                }
            }
        }

        if ($users->isNotEmpty()) {
            $selfManagedUsers = $users->shuffle()->take(5);

            foreach ($selfManagedUsers as $user) {
                $abilityPool = $allowedAbilities->isNotEmpty() ? $allowedAbilities : collect(['read']);
                $abilities = $abilityPool->shuffle()->take(random_int(1, $abilityPool->count()))->values()->all();

                $result = $apiKeyManager->createToken(
                    $user,
                    'Clave personal '.Str::upper(Str::random(4)),
                    fake()->sentence(6),
                    $abilities,
                    fake()->boolean(40) ? now()->addDays(random_int(15, 90)) : null,
                    $user,
                    true,
                );

                $tokenModel = PersonalAccessToken::find($result['id']);

                if ($tokenModel && fake()->boolean(60)) {
                    $tokenModel->forceFill([
                        'last_used_at' => Carbon::instance(fake()->dateTimeBetween('-10 days', 'now')),
                        'last_used_ip' => fake()->ipv4(),
                        'last_used_user_agent' => fake()->userAgent(),
                    ])->save();
                }
            }
        }
    }
}
