<?php

namespace App\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Trait para que los packages puedan configurar el tema durante la instalación
 */
trait ConfiguresTheme
{
    /**
     * Configura el tema por defecto de la aplicación
     *
     * @param string $theme Nombre del tema (zinc, slate, rose, etc.)
     * @param bool $updateEnv Si debe actualizar el archivo .env
     * @return bool
     */
    protected function setDefaultTheme(string $theme, bool $updateEnv = true): bool
    {
        // Validar que el tema existe
        $availableThemes = array_keys(config('expansion.themes.available_themes', []));
        
        if (!in_array($theme, $availableThemes)) {
            $this->logWarn("Theme '{$theme}' is not available. Available themes: " . implode(', ', $availableThemes));
            return false;
        }

        // Actualizar configuración en runtime
        config(['expansion.themes.default_theme' => $theme]);

        // Actualizar .env si se solicita
        if ($updateEnv) {
            $this->updateEnvFile('EXPANSION_DEFAULT_THEME', $theme);
        }

        $this->logInfo("✓ Default theme set to: {$theme}");
        
        return true;
    }

    /**
     * Registra un tema personalizado del package
     *
     * @param string $key Clave del tema
     * @param array $config Configuración del tema
     * @return void
     */
    protected function registerCustomTheme(string $key, array $config): void
    {
        $customThemes = config('expansion.themes.custom_themes', []);
        $customThemes[$key] = $config;
        
        config(['expansion.themes.custom_themes' => $customThemes]);
        
        $this->logInfo("✓ Custom theme registered: {$key}");
    }

    /**
     * Actualiza una variable en el archivo .env
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->logWarn('.env file not found');
            return;
        }

        $envContent = File::get($envPath);
        $pattern = "/^{$key}=.*/m";

        // Si la variable existe, actualizarla
        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
        } else {
            // Si no existe, agregarla al final
            $envContent .= "\n{$key}={$value}\n";
        }

        File::put($envPath, $envContent);

        // Limpiar cache de configuración
        if (app()->environment('production')) {
            Artisan::call('config:cache');
        }
    }

    /**
     * Pregunta al usuario si desea cambiar el tema
     *
     * @param string $suggestedTheme
     * @param string $packageName
     * @return bool
     */
    protected function askToChangeTheme(string $suggestedTheme, string $packageName): bool
    {
        if (!$this->hasCommand()) {
            return false;
        }

        $currentTheme = config('expansion.themes.default_theme', 'zinc');
        
        $message = "The {$packageName} package suggests using the '{$suggestedTheme}' theme.";
        $message .= "\nCurrent theme: {$currentTheme}";
        $message .= "\nDo you want to change the default theme?";

        return $this->confirm($message, false);
    }

    /**
     * Verifica si estamos en contexto de comando
     *
     * @return bool
     */
    protected function hasCommand(): bool
    {
        return method_exists($this, 'confirm') && method_exists($this, 'info');
    }

    /**
     * Muestra información (compatible con Command y ServiceProvider)
     *
     * @param string $message
     * @return void
     */
    protected function logInfo(string $message): void
    {
        if (method_exists($this, 'line')) {
            $this->line($message);
        }
    }

    /**
     * Muestra advertencia (compatible con Command y ServiceProvider)
     *
     * @param string $message
     * @return void
     */
    protected function logWarn(string $message): void
    {
        if (method_exists($this, 'line')) {
            $this->line($message);
        }
    }
}
