<?php

namespace App\Contracts;

/**
 * Interface para packages que soportan theming personalizado
 *
 * @version 1.0.0
 */
interface ThemeablePackageInterface
{
    /**
     * Retorna la configuración del theme del package
     *
     * @return array{
     *     name: string,
     *     colors: array{
     *         light: array<string, string>,
     *         dark: array<string, string>
     *     },
     *     typography?: array<string, string>,
     *     spacing?: array<string, string>,
     *     borders?: array<string, string>,
     *     shadows?: array<string, string>
     * }
     */
    public function getTheme(): array;

    /**
     * Retorna el modo de theme preferido
     *
     * @return string 'light', 'dark', o 'auto'
     */
    public function getThemeMode(): string;

    /**
     * Indica si el package tiene theme personalizado
     */
    public function hasCustomTheme(): bool;
}
