<?php

namespace Like\Fcv\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Like\Fcv\Models\Organization;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'acronym' => strtoupper($this->faker->lexify('???')),
            'type' => $this->faker->randomElement(['interna', 'convenio', 'externa']),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
