<?php

namespace Like\Fcv\Database\Seeders;

use Illuminate\Database\Seeder;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

class FcvBaseSeeder extends Seeder
{
    public function run(): void
    {
        $organizationsConfig = [
            'fundacion' => [
                'name' => 'Fundación Cristo Vive, Formación Laboral',
                'acronym' => 'FCV',
                'type' => 'interna',
                'description' => 'Organización principal de formación laboral FCV.',
            ],
            'cruz_andes' => [
                'name' => 'Cruz de los Andes',
                'acronym' => 'CDA',
                'type' => 'interna',
                'description' => 'Programas educativos complementarios de FCV.',
            ],
            'gore' => [
                'name' => 'GORE Metropolitano',
                'acronym' => 'GORE',
                'type' => 'convenio',
                'description' => 'Cursos financiados en convenio con el Gobierno Regional.',
            ],
        ];

        $organizations = [];
        foreach ($organizationsConfig as $key => $payload) {
            $organizations[$key] = Organization::query()->updateOrCreate(
                ['name' => $payload['name']],
                $payload
            );
        }

        $coursesByOrganization = [
            'fundacion' => [
                'Panadería y pastelería',
                'Panadería y pastelería saludable',
                'Técnicas administrativas con mención Excel avanzado',
                'Marketing digital y redes sociales',
                'Alfabetización digital',
                'Técnicas administrativas contables',
                'Recursos Humanos y remuneraciones',
                'Emprendimiento en diseño y producción de reciclaje textil',
            ],
            'cruz_andes' => [
                'Instalaciones sanitarias',
                'Instalaciones eléctricas y fotovoltaicas',
                'Instalaciones sanitarias, termosolares y de gas con certificación SEC',
                'Instalaciones eléctricas con certificación SEC y automatización industrial',
                'Jardinería urbana sustentable',
                'Asistente de enfermos y cuidado del adulto mayor',
                'Operario Bodega WMS',
            ],
            'gore' => [
                'Mecánica automotriz',
                'Mecánica industrial con CNC',
                'Técnicas de mecánica y electricidad automotriz',
                'Operación de grúa horquilla con licencia clase D',
                'Técnicas de aplicación de pintura automotriz',
                'Operario Tractor Renovación Licencia D',
            ],
        ];

        foreach ($coursesByOrganization as $orgKey => $courses) {
            $organization = $organizations[$orgKey] ?? null;
            if (! $organization) {
                continue;
            }

            foreach ($courses as $courseName) {
                /** @var Course $course */
                $course = Course::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $courseName,
                    ],
                    [
                        'description' => null,
                        'entry_tolerance_mode' => '20',
                        'entry_tolerance_minutes' => 20,
                        'valid_from' => now()->subWeek()->toDateString(),
                        'valid_until' => now()->addMonths(2)->toDateString(),
                    ]
                );

                if ($course->wasRecentlyCreated || $course->schedules()->count() === 0) {
                    $course->schedules()->delete();

                    $course->schedules()->createMany([
                        [
                            'day_of_week' => 1,
                            'start_time' => '09:00',
                            'end_time' => '12:00',
                        ],
                        [
                            'day_of_week' => 3,
                            'start_time' => '09:00',
                            'end_time' => '12:00',
                        ],
                    ]);
                }
            }
        }

        // Personas base para pruebas rápidas y endpoints
        $funcionario = Person::query()->updateOrCreate(
            ['rut' => '12345678k'],
            [
                'name' => 'Martín Alejandro Gutiérrez Rojas',
                'status' => 'active',
                'contact_info' => [
                    'email' => 'martin.gutierrez@fcv.test',
                    'phone' => '+56 9 7000 0001',
                ],
            ]
        );

        $alumnoFcv = Person::query()->updateOrCreate(
            ['rut' => '11111111k'],
            [
                'name' => 'Camila Andrea Pizarro Soto',
                'status' => 'active',
                'contact_info' => [
                    'email' => 'camila.pizarro@fcv.test',
                    'phone' => '+56 9 7000 0002',
                ],
            ]
        );

        $alumnoCruz = Person::query()->updateOrCreate(
            ['rut' => '22222222k'],
            [
                'name' => 'Diego Ignacio Morales Fuentes',
                'status' => 'active',
                'contact_info' => [
                    'email' => 'diego.morales@fcv.test',
                    'phone' => '+56 9 7000 0003',
                ],
            ]
        );

        // Membresías mínimas
        if (isset($organizations['fundacion'])) {
            Membership::query()->updateOrCreate(
                [
                    'person_id' => $funcionario->id,
                    'organization_id' => $organizations['fundacion']->id,
                ],
                [
                    'role' => 'funcionario',
                    'start_date' => now()->subMonths(18)->startOfMonth(),
                    'end_date' => null,
                ]
            );

            Membership::query()->updateOrCreate(
                [
                    'person_id' => $alumnoFcv->id,
                    'organization_id' => $organizations['fundacion']->id,
                ],
                [
                    'role' => 'alumno',
                    'start_date' => now()->subMonths(3)->startOfMonth(),
                    'end_date' => null,
                ]
            );
        }

        if (isset($organizations['cruz_andes'])) {
            Membership::query()->updateOrCreate(
                [
                    'person_id' => $alumnoCruz->id,
                    'organization_id' => $organizations['cruz_andes']->id,
                ],
                [
                    'role' => 'alumno',
                    'start_date' => now()->subMonths(2)->startOfMonth(),
                    'end_date' => null,
                ]
            );
        }

        // Vincular alumno FCV a un curso base para pruebas
        $foundationCourse = Course::query()
            ->where('organization_id', $organizations['fundacion']->id ?? null)
            ->orderBy('name')
            ->first();

        if ($foundationCourse) {
            $foundationCourse->students()->syncWithoutDetaching([$alumnoFcv->id]);
        }
    }
}
