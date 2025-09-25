<?php

namespace Like\Fcv\Services;

use Illuminate\Support\Carbon;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Organization;
use Like\Fcv\Models\Person;

class AccessRuleService
{
    public function check(string $rut): array
    {
        $normalized = $this->normalizeRut($rut);
        /** @var Person|null $person */
        $person = Person::query()->where('rut', $normalized)->first();

        if (! $person) {
            return [
                'allowed' => false,
                'status' => 'denegado',
                'reason' => 'Persona no registrada',
                'person' => null,
            ];
        }

        // Obtener membresías activas
        $today = Carbon::today();
        $memberships = $person->memberships()
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->with('organization')
            ->get();

        // Regla por defecto: denegado salvo que una membresía permita
        $decision = [
            'allowed' => false,
            'status' => 'denegado',
            'reason' => 'Sin membresías activas',
        ];

        foreach ($memberships as $m) {
            $org = $m->organization;
            if (! $org instanceof Organization) {
                continue;
            }

            $preset = config("fcv.organization_rule_presets.{$org->access_rule_preset}", []);
            // Funcionarios: acceso total (por nuestra regla de negocio)
            if ($m->role === 'funcionario') {
                return [
                    'allowed' => true,
                    'status' => 'permitido',
                    'reason' => 'Acceso de funcionario',
                    'person' => $person->only(['id', 'rut', 'name']),
                    'organization' => $org->only(['id', 'name', 'access_rule_preset']),
                ];
            }

            if ($m->role === 'alumno') {
                // horario_estricto: validación por horario con tolerancia
                if (($preset['entry']['by_schedule'] ?? false) === true) {
                    $within = $this->isWithinSchedule($person);
                    if ($within) {
                        // Entrada permitida dentro de horario/tolerancia
                        $decision = [
                            'allowed' => true,
                            'status' => 'permitido',
                            'reason' => 'Alumno en horario',
                            'person' => $person->only(['id', 'rut', 'name']),
                            'organization' => $org->only(['id', 'name', 'access_rule_preset']),
                        ];
                    } else {
                        $decision = [
                            'allowed' => false,
                            'status' => 'denegado',
                            'reason' => 'Alumno fuera de horario',
                            'person' => $person->only(['id', 'rut', 'name']),
                            'organization' => $org->only(['id', 'name', 'access_rule_preset']),
                        ];
                    }
                } else {
                    // horario_flexible o acceso_total para alumnos
                    $decision = [
                        'allowed' => ($preset['entry']['allowed'] ?? false) === true,
                        'status' => (($preset['entry']['allowed'] ?? false) === true) ? 'permitido' : 'denegado',
                        'reason' => 'Alumno con acceso flexible',
                        'person' => $person->only(['id', 'rut', 'name']),
                        'organization' => $org->only(['id', 'name', 'access_rule_preset']),
                    ];
                }

                if ($decision['allowed'] === true) {
                    // La primera membresía que permite acceso, corta
                    return $decision;
                }
            }
        }

        // Si llegamos aquí, no hubo una membresía que permita acceso
        return array_merge($decision, [
            'person' => $person->only(['id', 'rut', 'name']),
        ]);
    }

    protected function normalizeRut(string $rut): string
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut) ?? $rut;
        $rut = strtolower($rut);
        return $rut;
    }

    // Stub: en esta fase sólo devolvemos true para simplificar; se completará en fase de horarios
    protected function isWithinSchedule(Person $person): bool
    {
        return true;
    }
}
