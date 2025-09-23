<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Services\Options;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OptionsController extends Controller
{
    public function index(Request $request, Options $options): Response
    {
        // Definición de opciones soportadas en la UI
        $schema = [
            ['key' => 'app.name', 'label' => 'Nombre de la app', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.description', 'label' => 'Descripción', 'type' => 'text', 'group' => 'general'],
            ['key' => 'app.date_format', 'label' => 'Formato de fecha', 'type' => 'string', 'group' => 'i18n'],
            ['key' => 'app.timezone', 'label' => 'Zona horaria', 'type' => 'string', 'group' => 'i18n'],
            ['key' => 'app.logo_url', 'label' => 'URL de logo personalizado', 'type' => 'url', 'group' => 'branding'],
        ];

        $values = [];
        foreach ($schema as $field) {
            $values[$field['key']] = $options->get($field['key']);
        }

        return Inertia::render('admin/options/index', [
            'schema' => $schema,
            'values' => $values,
        ]);
    }

    public function update(Request $request, Options $options)
    {
        $data = $request->validate([
            'values' => ['required', 'array'],
        ]);

        $schema = [
            ['key' => 'app.name', 'type' => 'string', 'group' => 'general', 'description' => 'Nombre visible de la aplicación'],
            ['key' => 'app.description', 'type' => 'text', 'group' => 'general', 'description' => 'Descripción corta de la aplicación'],
            ['key' => 'app.date_format', 'type' => 'string', 'group' => 'i18n', 'description' => 'Formato de fecha (p.ej. Y-m-d)'],
            ['key' => 'app.timezone', 'type' => 'string', 'group' => 'i18n', 'description' => 'Zona horaria por defecto'],
            ['key' => 'app.logo_url', 'type' => 'url', 'group' => 'branding', 'description' => 'URL de logo personalizado'],
        ];

        foreach ($schema as $field) {
            $key = $field['key'];
            if (array_key_exists($key, $data['values'])) {
                $options->set($key, $data['values'][$key], $field['type'], $field['group'], $field['description']);
            }
        }

        return back()->with('success', __('Opciones actualizadas correctamente'));
    }
}
