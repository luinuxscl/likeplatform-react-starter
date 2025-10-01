<?php

namespace Like\Fcv\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')?->id ?? null;
        $organizationId = $this->input('organization_id');
        $toleranceModes = array_map(fn ($value) => (string) $value, (array) config('fcv.course_entry_tolerance_options', []));
        $toleranceMinutes = array_values(array_filter(array_map(static fn ($value) => is_numeric($value) ? (int) $value : null, $toleranceModes)));

        return [
            'organization_id' => ['required', 'exists:fcv_organizations,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fcv_courses', 'name')
                    ->ignore($courseId)
                    ->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'entry_tolerance_mode' => ['required', Rule::in($toleranceModes)],
            'entry_tolerance_minutes' => ['nullable', 'integer', Rule::in($toleranceMinutes)],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedSchedules = [];
        foreach ($this->input('schedules', []) as $schedule) {
            if (! is_array($schedule)) {
                continue;
            }

            $day = isset($schedule['day_of_week']) ? (int) $schedule['day_of_week'] : null;
            $start = $this->normalizeTime($schedule['start_time'] ?? null);
            $end = $this->normalizeTime($schedule['end_time'] ?? null);

            if ($day === null || $start === null || $end === null) {
                continue;
            }

            $normalizedSchedules[] = [
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
            ];
        }

        if ($normalizedSchedules !== []) {
            $this->merge(['schedules' => array_values($normalizedSchedules)]);
        }

        $mode = $this->input('entry_tolerance_mode');
        $minutes = $this->input('entry_tolerance_minutes');

        if ($mode === null) {
            return;
        }

        if ($mode === 'none') {
            $this->merge([
                'entry_tolerance_mode' => 'none',
                'entry_tolerance_minutes' => null,
            ]);

            return;
        }

        if (is_numeric($mode)) {
            $tolerance = (int) $mode;
        } elseif (is_numeric($minutes)) {
            $tolerance = (int) $minutes;
        } else {
            $tolerance = 20;
        }

        $this->merge([
            'entry_tolerance_mode' => (string) $tolerance,
            'entry_tolerance_minutes' => $tolerance,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $schedules = $this->input('schedules', []);

            foreach ($schedules as $index => $schedule) {
                $start = $schedule['start_time'] ?? null;
                $end = $schedule['end_time'] ?? null;

                if ($start === null || $end === null) {
                    continue;
                }

                if (strtotime($end) <= strtotime($start)) {
                    $validator->errors()->add(
                        "schedules.$index.end_time",
                        'La hora de término debe ser posterior a la de inicio.'
                    );
                }
            }
        });
    }

    private function normalizeTime(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $segments = explode(':', $value);

        if (count($segments) < 2) {
            return null;
        }

        $hour = str_pad((string) ((int) $segments[0]), 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string) ((int) $segments[1]), 2, '0', STR_PAD_LEFT);

        return sprintf('%s:%s', $hour, $minute);
    }
}
