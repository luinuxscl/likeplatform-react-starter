<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller para gestionar settings de packages
 */
class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Muestra la página principal de settings
     */
    public function index(): Response
    {
        $packages = $this->settingsService->getSettingsForFrontend();

        return Inertia::render('settings/packages/index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Obtiene los settings de un package específico
     */
    public function show(string $packageName)
    {
        $schema = $this->settingsService->getPackageSchema($packageName);
        
        if (! $schema) {
            return response()->json([
                'message' => 'Package not found or not configurable',
            ], 404);
        }

        $settings = $this->settingsService->getPackageSettings($packageName);

        return response()->json([
            'package' => $packageName,
            'schema' => $schema,
            'settings' => $settings,
        ]);
    }

    /**
     * Actualiza los settings de un package
     */
    public function update(Request $request, string $packageName)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        // Validar settings según el schema
        if (! $this->settingsService->validateSettings($packageName, $validated['settings'])) {
            return back()->withErrors([
                'settings' => 'Invalid settings provided',
            ]);
        }

        $success = $this->settingsService->updatePackageSettings(
            $packageName,
            $validated['settings']
        );

        if (! $success) {
            return back()->withErrors([
                'settings' => 'Failed to update settings',
            ]);
        }

        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Resetea los settings de un package a sus valores por defecto
     */
    public function reset(string $packageName)
    {
        $success = $this->settingsService->resetToDefaults($packageName);

        if (! $success) {
            return back()->withErrors([
                'settings' => 'Failed to reset settings',
            ]);
        }

        return back()->with('success', 'Settings reset to defaults successfully');
    }

    /**
     * Exporta los settings de un package
     */
    public function export(string $packageName)
    {
        $settings = $this->settingsService->export($packageName);

        return response()->json([
            'package' => $packageName,
            'settings' => $settings,
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Importa settings de un package
     */
    public function import(Request $request, string $packageName)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $success = $this->settingsService->import($packageName, $validated['settings']);

        if (! $success) {
            return back()->withErrors([
                'settings' => 'Failed to import settings',
            ]);
        }

        return back()->with('success', 'Settings imported successfully');
    }

    /**
     * Limpia la caché de settings
     */
    public function clearCache(?string $packageName = null)
    {
        $this->settingsService->clearCache($packageName);

        return back()->with('success', 'Settings cache cleared successfully');
    }
}
