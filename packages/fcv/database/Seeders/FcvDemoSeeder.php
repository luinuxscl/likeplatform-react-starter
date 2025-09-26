<?php

namespace Like\Fcv\Database\Seeders;

use Illuminate\Database\Seeder;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

class FcvDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FcvBaseSeeder::class);

        $courses = Course::with('organization')->get();

        foreach ($courses as $course) {
            $students = Person::factory()
                ->count(20)
                ->create();

            $course->students()->syncWithoutDetaching($students->pluck('id')->all());

            foreach ($students as $student) {
                Membership::query()->updateOrCreate([
                    'person_id' => $student->id,
                    'organization_id' => $course->organization_id,
                ], [
                    'role' => 'alumno',
                    'start_date' => now()->subMonths(rand(1, 8))->startOfMonth(),
                ]);
            }
        }

        $organizations = Organization::all();
        foreach ($organizations as $organization) {
            $staff = Person::factory()
                ->count(20)
                ->create();

            foreach ($staff as $person) {
                Membership::query()->updateOrCreate([
                    'person_id' => $person->id,
                    'organization_id' => $organization->id,
                ], [
                    'role' => 'funcionario',
                    'start_date' => now()->subMonths(rand(6, 24))->startOfMonth(),
                ]);
            }
        }
    }
}
