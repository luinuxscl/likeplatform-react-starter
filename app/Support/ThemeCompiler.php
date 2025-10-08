<?php

namespace App\Support;

/**
 * Compilador de themes a CSS variables
 */
class ThemeCompiler
{
    /**
     * Compila un theme a CSS variables
     *
     * @param  array  $theme  Configuración del theme
     * @param  string  $packageName  Nombre del package
     * @return array{css: string, variables: array}
     */
    public function compile(array $theme, string $packageName): array
    {
        $variables = [];
        $css = [];

        // Compilar colores para light mode
        if (isset($theme['colors']['light'])) {
            $lightVars = $this->compileColors($theme['colors']['light']);
            $variables['light'] = $lightVars;
            $css[] = $this->generateCssBlock($packageName, $lightVars, 'light');
        }

        // Compilar colores para dark mode
        if (isset($theme['colors']['dark'])) {
            $darkVars = $this->compileColors($theme['colors']['dark']);
            $variables['dark'] = $darkVars;
            $css[] = $this->generateCssBlock($packageName, $darkVars, 'dark');
        }

        // Compilar tipografía
        if (isset($theme['typography'])) {
            $typoVars = $this->compileTypography($theme['typography']);
            $variables['typography'] = $typoVars;
            $css[] = $this->generateCssBlock($packageName, $typoVars);
        }

        // Compilar spacing
        if (isset($theme['spacing'])) {
            $spacingVars = $this->compileSpacing($theme['spacing']);
            $variables['spacing'] = $spacingVars;
            $css[] = $this->generateCssBlock($packageName, $spacingVars);
        }

        return [
            'css' => implode("\n\n", array_filter($css)),
            'variables' => $variables,
        ];
    }

    /**
     * Compila colores a formato CSS variables
     */
    private function compileColors(array $colors): array
    {
        $variables = [];

        foreach ($colors as $key => $value) {
            $cssVar = '--'.$this->kebabCase($key);
            $variables[$cssVar] = $this->normalizeColor($value);
        }

        return $variables;
    }

    /**
     * Compila tipografía a CSS variables
     */
    private function compileTypography(array $typography): array
    {
        $variables = [];

        foreach ($typography as $key => $value) {
            $cssVar = '--'.$this->kebabCase($key);
            $variables[$cssVar] = $value;
        }

        return $variables;
    }

    /**
     * Compila spacing a CSS variables
     */
    private function compileSpacing(array $spacing): array
    {
        $variables = [];

        foreach ($spacing as $key => $value) {
            $cssVar = '--'.$this->kebabCase($key);
            $variables[$cssVar] = $value;
        }

        return $variables;
    }

    /**
     * Genera un bloque CSS con las variables
     */
    private function generateCssBlock(
        string $packageName,
        array $variables,
        ?string $mode = null
    ): string {
        if (empty($variables)) {
            return '';
        }

        $selector = $this->generateSelector($packageName, $mode);
        $props = [];

        foreach ($variables as $name => $value) {
            $props[] = "  {$name}: {$value};";
        }

        return $selector." {\n".implode("\n", $props)."\n}";
    }

    /**
     * Genera el selector CSS apropiado
     */
    private function generateSelector(string $packageName, ?string $mode = null): string
    {
        $dataAttr = "[data-package=\"{$packageName}\"]";

        if ($mode === 'dark') {
            return ".dark {$dataAttr}";
        }

        return $dataAttr;
    }

    /**
     * Normaliza un color al formato HSL de Tailwind
     *
     * Acepta:
     * - HSL: "221.2 83.2% 53.3%"
     * - Hex: "#3b82f6"
     * - RGB: "59 130 246"
     */
    private function normalizeColor(string $color): string
    {
        // Si ya está en formato HSL (contiene %), retornar tal cual
        if (str_contains($color, '%')) {
            return $color;
        }

        // Si es hex, convertir a RGB
        if (str_starts_with($color, '#')) {
            return $this->hexToRgb($color);
        }

        // Si ya es RGB (tres números separados por espacios), retornar tal cual
        if (preg_match('/^\d+\s+\d+\s+\d+$/', $color)) {
            return $color;
        }

        // Fallback: retornar tal cual
        return $color;
    }

    /**
     * Convierte hex a RGB en formato Tailwind
     */
    private function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r} {$g} {$b}";
    }

    /**
     * Convierte camelCase o snake_case a kebab-case
     */
    private function kebabCase(string $string): string
    {
        // Primero convertir camelCase a snake_case
        $string = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);

        // Luego convertir a lowercase y reemplazar _ por -
        return strtolower(str_replace('_', '-', $string));
    }

    /**
     * Compila todos los themes de packages
     *
     * @param  array  $packages  Array de packages con sus themes
     * @return string CSS compilado
     */
    public function compileAll(array $packages): string
    {
        $css = [];

        foreach ($packages as $packageName => $theme) {
            if (empty($theme)) {
                continue;
            }

            $compiled = $this->compile($theme, $packageName);
            if (! empty($compiled['css'])) {
                $css[] = "/* Theme: {$packageName} */";
                $css[] = $compiled['css'];
            }
        }

        return implode("\n\n", $css);
    }
}
