<?php

namespace Like\Fcv\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Like\Fcv\Models\AccessException;
use Like\Fcv\Models\Person;

class AccessExceptionFactory extends Factory
{
    protected $model = AccessException::class;

    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'reason' => $this->faker->randomElement(['medical', 'special_event', 'maintenance', 'administrative', 'other']),
            'description' => $this->faker->sentence(10),
            'valid_from' => now()->subDays($this->faker->numberBetween(0, 5)),
            'valid_until' => now()->addDays($this->faker->numberBetween(1, 10)),
            'created_by' => User::factory(),
            'approved_by' => null,
            'status' => 'pending',
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'valid_from' => now()->subHour(),
            'valid_until' => now()->addHours(2),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'valid_from' => now()->subDays(5),
            'valid_until' => now()->subDay(),
        ]);
    }
}
