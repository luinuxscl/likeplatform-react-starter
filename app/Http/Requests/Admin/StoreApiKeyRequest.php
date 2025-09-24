<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:191'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
