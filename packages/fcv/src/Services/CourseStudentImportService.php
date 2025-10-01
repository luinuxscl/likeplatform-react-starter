<?php

namespace Like\Fcv\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Like\Fcv\Models\Course;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Person;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CourseStudentImportService
{
    public function import(Course $course, UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return [
                'created' => 0,
                'updated' => 0,
                'attached' => 0,
                'errors' => [['row' => 1, 'message' => 'El archivo está vacío o no tiene datos']],
            ];
        }

        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $rutIndex = $this->findColumnIndex($header, ['rut', 'run']);
        $nameIndex = $this->findColumnIndex($header, ['nombre', 'name']);
        $emailIndex = $this->findColumnIndex($header, ['email', 'correo']);
        $phoneIndex = $this->findColumnIndex($header, ['telefono', 'teléfono', 'phone', 'celular']);

        if ($rutIndex === null || $nameIndex === null) {
            return [
                'created' => 0,
                'updated' => 0,
                'attached' => 0,
                'errors' => [['row' => 1, 'message' => 'El archivo debe contener columnas "rut" y "nombre"']],
            ];
        }

        $created = 0;
        $updated = 0;
        $attached = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $rut = $this->normalizeRut($row[$rutIndex] ?? '');
                $name = trim($row[$nameIndex] ?? '');

                if (empty($rut) || empty($name)) {
                    $errors[] = ['row' => $rowNumber, 'message' => 'RUT o nombre vacío'];
                    continue;
                }

                if (! $this->isValidRut($rut)) {
                    $errors[] = ['row' => $rowNumber, 'message' => "RUT inválido: $rut"];
                    continue;
                }

                $contactInfo = [];
                if ($emailIndex !== null && ! empty($row[$emailIndex])) {
                    $contactInfo['email'] = trim($row[$emailIndex]);
                }
                if ($phoneIndex !== null && ! empty($row[$phoneIndex])) {
                    $contactInfo['phone'] = trim($row[$phoneIndex]);
                }

                DB::transaction(function () use ($rut, $name, $contactInfo, $course, &$created, &$updated, &$attached) {
                    $person = Person::query()->where('rut', $rut)->first();

                    if ($person) {
                        $person->update([
                            'name' => $name,
                            'contact_info' => array_merge($person->contact_info ?? [], $contactInfo),
                        ]);
                        $updated++;
                    } else {
                        $person = Person::query()->create([
                            'rut' => $rut,
                            'name' => $name,
                            'status' => 'active',
                            'contact_info' => $contactInfo,
                        ]);
                        $created++;
                    }

                    // Crear membresía como alumno si no existe
                    $membership = Membership::query()
                        ->where('person_id', $person->id)
                        ->where('organization_id', $course->organization_id)
                        ->first();

                    if (! $membership) {
                        Membership::query()->create([
                            'person_id' => $person->id,
                            'organization_id' => $course->organization_id,
                            'role' => 'alumno',
                            'start_date' => now()->toDateString(),
                            'end_date' => null,
                        ]);
                    }

                    // Vincular al curso
                    if (! $course->students()->where('person_id', $person->id)->exists()) {
                        $course->students()->attach($person->id);
                        $attached++;
                    }
                });
            } catch (\Exception $e) {
                $errors[] = ['row' => $rowNumber, 'message' => $e->getMessage()];
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'attached' => $attached,
            'errors' => $errors,
        ];
    }

    protected function findColumnIndex(array $header, array $possibleNames): ?int
    {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $header, true);
            if ($index !== false) {
                return $index;
            }
        }

        return null;
    }

    protected function normalizeRut(string $rut): string
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut) ?? $rut;

        return strtolower($rut);
    }

    protected function isValidRut(string $rut): bool
    {
        if (strlen($rut) < 2) {
            return false;
        }

        $body = substr($rut, 0, -1);
        $dv = substr($rut, -1);

        if (! is_numeric($body)) {
            return false;
        }

        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += (int) $body[$i] * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $calculatedDv = 11 - ($sum % 11);
        $calculatedDv = $calculatedDv === 11 ? '0' : ($calculatedDv === 10 ? 'k' : (string) $calculatedDv);

        return $dv === $calculatedDv;
    }
}
