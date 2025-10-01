<?php

namespace Like\Fcv\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organizationId = $this->route('organization')?->id ?? null;
        $types = ['interna', 'convenio', 'externa'];

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fcv_organizations', 'name')->ignore($organizationId),
            ],
            'acronym' => ['nullable', 'string', 'max:20'],
            'type' => ['required', Rule::in($types)],
            'description' => ['nullable', 'string'],
        ];
    }
}
