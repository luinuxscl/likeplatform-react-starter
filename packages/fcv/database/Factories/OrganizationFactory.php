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
            'type' => $this->faker->randomElement(['interna', 'convenio', 'externa']),
            'access_rule_preset' => $this->faker->randomElement(['horario_estricto', 'horario_flexible', 'acceso_total']),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
