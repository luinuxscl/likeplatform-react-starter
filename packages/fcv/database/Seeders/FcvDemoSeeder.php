<?php

namespace Like\Fcv\Database\Seeders;

use Illuminate\Database\Seeder;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;
use Like\Fcv\Models\Schedule;

class FcvDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Organizaciones base
        $fcvAdmin = Organization::query()->firstOrCreate([
            'name' => 'FCV Administración',
        ], [
            'type' => 'interna',
            'access_rule_preset' => 'acceso_total',
            'description' => 'Funcionarios administración FCV',
        ]);

        $fcvEscuela = Organization::query()->firstOrCreate([
            'name' => 'FCV Escuela de Oficios',
        ], [
            'type' => 'interna',
            'access_rule_preset' => 'horario_estricto',
            'description' => 'Alumnos y funcionarios escuela',
        ]);

        $cruzAndes = Organization::query()->firstOrCreate([
            'name' => 'Cruz de los Andes',
        ], [
            'type' => 'interna',
            'access_rule_preset' => 'horario_flexible',
            'description' => 'Organización parte de FCV',
        ]);

        $gore = Organization::query()->firstOrCreate([
            'name' => 'GORE Metropolitano',
        ], [
            'type' => 'convenio',
            'access_rule_preset' => 'horario_estricto',
            'description' => 'Cursos financiados por GORE',
        ]);

        // Personas con RUTs fijos para tests
        $funcionario = Person::query()->firstOrCreate([
            'rut' => '12345678k',
        ], [
            'name' => 'Funcionario FCV',
            'status' => 'active',
        ]);

        $alumnoFcv = Person::query()->firstOrCreate([
            'rut' => '11111111k',
        ], [
            'name' => 'Alumno FCV',
            'status' => 'active',
        ]);

        $alumnoCda = Person::query()->firstOrCreate([
            'rut' => '22222222k',
        ], [
            'name' => 'Alumno Cruz de los Andes',
            'status' => 'active',
        ]);

        // Membresías
        Membership::query()->firstOrCreate([
            'person_id' => $funcionario->id,
            'organization_id' => $fcvAdmin->id,
            'role' => 'funcionario',
        ]);

        Membership::query()->firstOrCreate([
            'person_id' => $alumnoFcv->id,
            'organization_id' => $fcvEscuela->id,
            'role' => 'alumno',
        ]);

        Membership::query()->firstOrCreate([
            'person_id' => $alumnoCda->id,
            'organization_id' => $cruzAndes->id,
            'role' => 'alumno',
        ]);

        // Curso y horario para FCV Escuela (para futuro uso de horarios)
        $curso = Course::query()->firstOrCreate([
            'organization_id' => $fcvEscuela->id,
            'name' => 'Soldadura Básica',
        ]);

        $curso->students()->syncWithoutDetaching([$alumnoFcv->id]);

        // Lunes a viernes 08:30 - 12:30
        foreach ([1,2,3,4,5] as $dow) {
            Schedule::query()->firstOrCreate([
                'scheduleable_type' => Course::class,
                'scheduleable_id' => $curso->id,
                'day_of_week' => $dow,
                'start_time' => '08:30:00',
                'end_time' => '12:30:00',
            ]);
        }
    }
}
