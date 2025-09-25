<?php

namespace Like\Fcv\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Like\Fcv\Models\Person;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        $rutNumber = (string) $this->faker->unique()->numberBetween(1000000, 25000000);
        $dv = 'k';
        return [
            'rut' => strtolower($rutNumber.$dv),
            'name' => $this->faker->name(),
            'photo_path' => null,
            'contact_info' => [
                'email' => $this->faker->safeEmail(),
                'phone' => $this->faker->phoneNumber(),
            ],
            'status' => 'active',
        ];
    }
}
