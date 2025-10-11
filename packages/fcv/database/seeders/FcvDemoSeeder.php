<?php

namespace Like\Fcv\Database\Seeders;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Like\Fcv\Models\AccessException;
use Like\Fcv\Models\AccessLog;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

class FcvDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding FCV demo data...');

        // 1. Crear organizaciones
        $this->command->info('Creating organizations...');
        $organizations = $this->createOrganizations();

        // 2. Crear personas
        $this->command->info('Creating persons...');
        $persons = $this->createPersons();

        // 3. Crear membresías
        $this->command->info('Creating memberships...');
        $this->createMemberships($persons, $organizations);

        // 4. Crear cursos
        $this->command->info('Creating courses...');
        $courses = $this->createCourses();

        // 5. Asignar estudiantes a cursos
        $this->command->info('Assigning students to courses...');
        $this->assignStudentsToCourses($persons, $courses);

        // 6. Crear logs de acceso
        $this->command->info('Creating access logs...');
        $this->createAccessLogs($persons);

        // 7. Crear excepciones de acceso
        $this->command->info('Creating access exceptions...');
        $this->createAccessExceptions($persons);

        $this->command->info('✅ FCV demo data seeded successfully!');
    }

    /**
     * Crear organizaciones de ejemplo
     */
    protected function createOrganizations(): array
    {
        $organizations = [
            [
                'name' => 'Universidad Técnica',
                'code' => 'UTECH',
                'type' => 'university',
                'active' => true,
            ],
            [
                'name' => 'Empresa Consultora ABC',
                'code' => 'ABC',
                'type' => 'company',
                'active' => true,
            ],
            [
                'name' => 'Instituto de Capacitación',
                'code' => 'INCAP',
                'type' => 'institute',
                'active' => true,
            ],
            [
                'name' => 'Organización Inactiva',
                'code' => 'INACTIVE',
                'type' => 'other',
                'active' => false,
            ],
        ];

        return collect($organizations)->map(function ($data) {
            return Organization::create($data);
        })->toArray();
    }

    /**
     * Crear personas de ejemplo
     */
    protected function createPersons(): array
    {
        $persons = [
            ['rut' => '12345678-9', 'name' => 'Juan Pérez González', 'email' => 'juan.perez@example.com'],
            ['rut' => '23456789-0', 'name' => 'María García López', 'email' => 'maria.garcia@example.com'],
            ['rut' => '34567890-1', 'name' => 'Pedro Rodríguez Silva', 'email' => 'pedro.rodriguez@example.com'],
            ['rut' => '45678901-2', 'name' => 'Ana Martínez Torres', 'email' => 'ana.martinez@example.com'],
            ['rut' => '56789012-3', 'name' => 'Luis Fernández Muñoz', 'email' => 'luis.fernandez@example.com'],
            ['rut' => '67890123-4', 'name' => 'Carmen Sánchez Díaz', 'email' => 'carmen.sanchez@example.com'],
            ['rut' => '78901234-5', 'name' => 'José López Ramírez', 'email' => 'jose.lopez@example.com'],
            ['rut' => '89012345-6', 'name' => 'Laura González Castro', 'email' => 'laura.gonzalez@example.com'],
            ['rut' => '90123456-7', 'name' => 'Carlos Hernández Ortiz', 'email' => 'carlos.hernandez@example.com'],
            ['rut' => '01234567-8', 'name' => 'Isabel Ruiz Moreno', 'email' => 'isabel.ruiz@example.com'],
            ['rut' => '11111111-1', 'name' => 'Roberto Vargas Soto', 'email' => 'roberto.vargas@example.com'],
            ['rut' => '22222222-2', 'name' => 'Patricia Flores Vega', 'email' => 'patricia.flores@example.com'],
            ['rut' => '33333333-3', 'name' => 'Miguel Ángel Rojas', 'email' => 'miguel.rojas@example.com'],
            ['rut' => '44444444-4', 'name' => 'Sofía Castro Núñez', 'email' => 'sofia.castro@example.com'],
            ['rut' => '55555555-5', 'name' => 'Diego Morales Pinto', 'email' => 'diego.morales@example.com'],
        ];

        return collect($persons)->map(function ($data) {
            return Person::create($data);
        })->toArray();
    }

    /**
     * Crear membresías
     */
    protected function createMemberships(array $persons, array $organizations): void
    {
        // Asignar personas a organizaciones
        foreach ($persons as $index => $person) {
            $orgIndex = $index % count($organizations);
            
            Membership::create([
                'person_id' => $person->id,
                'organization_id' => $organizations[$orgIndex]->id,
                'role' => ['student', 'employee', 'contractor', 'visitor'][rand(0, 3)],
                'active' => rand(0, 10) > 1, // 90% activos
            ]);
        }
    }

    /**
     * Crear cursos de ejemplo
     */
    protected function createCourses(): array
    {
        $courses = [
            [
                'code' => 'PROG101',
                'name' => 'Introducción a la Programación',
                'description' => 'Curso básico de programación con Python',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'active' => true,
            ],
            [
                'code' => 'WEB201',
                'name' => 'Desarrollo Web Avanzado',
                'description' => 'Laravel, React y bases de datos',
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(75),
                'active' => true,
            ],
            [
                'code' => 'DATA301',
                'name' => 'Ciencia de Datos',
                'description' => 'Análisis de datos con Python y R',
                'start_date' => Carbon::now()->subDays(45),
                'end_date' => Carbon::now()->addDays(45),
                'active' => true,
            ],
            [
                'code' => 'MOBILE401',
                'name' => 'Desarrollo Móvil',
                'description' => 'Apps nativas con React Native',
                'start_date' => Carbon::now()->subDays(60),
                'end_date' => Carbon::now()->subDays(10),
                'active' => false,
            ],
        ];

        return collect($courses)->map(function ($data) {
            $course = Course::create($data);
            
            // Agregar horarios
            $course->schedules()->createMany([
                ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '11:00'],
                ['day_of_week' => 3, 'start_time' => '09:00', 'end_time' => '11:00'],
                ['day_of_week' => 5, 'start_time' => '09:00', 'end_time' => '11:00'],
            ]);
            
            return $course;
        })->toArray();
    }

    /**
     * Asignar estudiantes a cursos
     */
    protected function assignStudentsToCourses(array $persons, array $courses): void
    {
        foreach ($courses as $course) {
            // Asignar 5-10 estudiantes por curso
            $numStudents = rand(5, 10);
            $selectedPersons = collect($persons)->random($numStudents);
            
            foreach ($selectedPersons as $person) {
                $course->students()->attach($person->id, [
                    'enrolled_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }

    /**
     * Crear logs de acceso realistas
     */
    protected function createAccessLogs(array $persons): void
    {
        $auditLogger = app(AuditLogger::class);
        $gatekeeper = User::first();

        // Crear logs de los últimos 30 días
        for ($day = 30; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Número de accesos por día (más en días laborales)
            $isWeekday = $date->isWeekday();
            $numAccesses = $isWeekday ? rand(20, 40) : rand(5, 15);
            
            for ($i = 0; $i < $numAccesses; $i++) {
                $person = $persons[array_rand($persons)];
                $hour = $isWeekday ? rand(7, 19) : rand(10, 18);
                $minute = rand(0, 59);
                
                $occurredAt = $date->copy()->setTime($hour, $minute);
                
                // 85% de accesos permitidos, 15% denegados
                $allowed = rand(1, 100) <= 85;
                
                $accessLog = AccessLog::create([
                    'person_id' => $person->id,
                    'direction' => ['entry', 'exit'][rand(0, 1)],
                    'status' => $allowed ? 'allowed' : 'denied',
                    'reason' => $allowed ? null : $this->getRandomDeniedReason(),
                    'gatekeeper_id' => $gatekeeper->id,
                    'occurred_at' => $occurredAt,
                    'meta' => [
                        'location' => ['Entrada Principal', 'Entrada Lateral', 'Estacionamiento'][rand(0, 2)],
                        'device' => 'Demo Seeder',
                    ],
                ]);

                // Registrar en auditoría
                $action = $accessLog->direction === 'entry' ? 'fcv.access.entry' : 'fcv.access.exit';
                $auditLogger->log(
                    action: $action,
                    model: $accessLog,
                    metadata: [
                        'status' => $accessLog->status,
                        'person_rut' => $person->rut,
                        'person_name' => $person->name,
                    ]
                );
            }
        }

        $this->command->info('  Created access logs for last 30 days');
    }

    /**
     * Crear excepciones de acceso
     */
    protected function createAccessExceptions(array $persons): void
    {
        $creator = User::first();
        $approver = User::skip(1)->first() ?? $creator;

        $exceptions = [
            // Excepción aprobada y activa
            [
                'person_id' => $persons[0]->id,
                'reason' => 'medical',
                'description' => 'Permiso médico por tratamiento',
                'valid_from' => Carbon::now()->subDays(5),
                'valid_until' => Carbon::now()->addDays(10),
                'status' => 'approved',
                'created_by' => $creator->id,
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now()->subDays(4),
            ],
            // Excepción aprobada pero vencida
            [
                'person_id' => $persons[1]->id,
                'reason' => 'special_event',
                'description' => 'Evento especial corporativo',
                'valid_from' => Carbon::now()->subDays(20),
                'valid_until' => Carbon::now()->subDays(10),
                'status' => 'approved',
                'created_by' => $creator->id,
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now()->subDays(19),
            ],
            // Excepción pendiente
            [
                'person_id' => $persons[2]->id,
                'reason' => 'maintenance',
                'description' => 'Acceso para mantenimiento de equipos',
                'valid_from' => Carbon::now()->addDays(1),
                'valid_until' => Carbon::now()->addDays(7),
                'status' => 'pending',
                'created_by' => $creator->id,
            ],
            // Excepción rechazada
            [
                'person_id' => $persons[3]->id,
                'reason' => 'administrative',
                'description' => 'Solicitud administrativa',
                'valid_from' => Carbon::now()->subDays(2),
                'valid_until' => Carbon::now()->addDays(5),
                'status' => 'rejected',
                'created_by' => $creator->id,
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now()->subDays(1),
                'rejection_reason' => 'Documentación incompleta',
            ],
            // Otra excepción pendiente
            [
                'person_id' => $persons[4]->id,
                'reason' => 'other',
                'description' => 'Visita especial de auditoría',
                'valid_from' => Carbon::now()->addDays(3),
                'valid_until' => Carbon::now()->addDays(5),
                'status' => 'pending',
                'created_by' => $creator->id,
            ],
        ];

        foreach ($exceptions as $data) {
            AccessException::create($data);
        }

        $this->command->info('  Created 5 access exceptions (approved, pending, rejected)');
    }

    /**
     * Obtener razón aleatoria de denegación
     */
    protected function getRandomDeniedReason(): string
    {
        $reasons = [
            'no_membership',
            'inactive_membership',
            'no_active_course',
            'outside_schedule',
            'expired_access',
        ];

        return $reasons[array_rand($reasons)];
    }
}
