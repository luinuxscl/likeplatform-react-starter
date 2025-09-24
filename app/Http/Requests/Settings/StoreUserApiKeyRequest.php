<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:191', Rule::in(config('sanctum.user_abilities', []))],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
