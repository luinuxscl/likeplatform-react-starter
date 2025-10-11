<?php

namespace Like\Fcv\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Organization;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->bothify('???-###')),
            'description' => $this->faker->optional()->sentence(),
            'valid_from' => now()->subDays($this->faker->numberBetween(0, 30)),
            'valid_until' => now()->addDays($this->faker->numberBetween(30, 90)),
            'entry_tolerance_mode' => $this->faker->randomElement(['10', '20', '30', 'none']),
            'entry_tolerance_minutes' => $this->faker->randomElement([10, 20, 30, null]),
        ];
    }
}
