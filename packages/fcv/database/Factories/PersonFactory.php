<?php

namespace Like\Fcv\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Like\Fcv\Models\Person;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        $localeFaker = fake('es_CL');

        $rutNumber = $this->faker->unique()->numberBetween(1000000, 25000000);
        $dv = $this->calculateDv($rutNumber);

        $first = $localeFaker->firstName();
        $second = $localeFaker->firstName();
        $last1 = $localeFaker->lastName();
        $last2 = $localeFaker->lastName();

        $name = sprintf('%s %s %s %s', $first, $second, $last1, $last2);

        return [
            'rut' => strtolower($rutNumber . $dv),
            'name' => $name,
            'photo_path' => null,
            'contact_info' => [
                'email' => $this->makeEmailFromNames([$first, $second, $last1, $last2]),
                'phone' => sprintf('+56 9 %04d %04d', $this->faker->numberBetween(1000, 9999), $this->faker->numberBetween(1000, 9999)),
            ],
            'status' => 'active',
        ];
    }

    protected function calculateDv(int $number): string
    {
        $sum = 0;
        $factor = 2;

        foreach (array_reverse(str_split((string) $number)) as $digit) {
            $sum += (int) $digit * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $remainder = 11 - ($sum % 11);

        return match ($remainder) {
            11 => '0',
            10 => 'k',
            default => (string) $remainder,
        };
    }

    protected function makeEmailFromNames(array $parts): string
    {
        $normalized = collect($parts)
            ->map(function ($part) {
                $slug = strtolower(str_replace(['ñ', 'Ñ'], 'n', $part));
                return preg_replace('/[^a-z]/', '', $slug ?? '');
            })
            ->filter()
            ->values();

        $username = $normalized->take(2)->implode('.');

        if ($username === '') {
            $username = 'persona'.rand(100, 999);
        }

        return $username.'@fcv-demo.test';
    }
}
