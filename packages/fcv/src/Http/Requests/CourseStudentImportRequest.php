<?php

namespace Like\Fcv\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStudentImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // 10MB max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debes seleccionar un archivo para importar.',
            'file.mimes' => 'El archivo debe ser formato Excel (.xlsx, .xls) o CSV.',
            'file.max' => 'El archivo no debe superar 10MB.',
        ];
    }
}
