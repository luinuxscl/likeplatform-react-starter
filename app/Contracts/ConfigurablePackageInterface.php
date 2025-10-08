<?php

namespace App\Contracts;

/**
 * Interface para packages que soportan configuración mediante UI
 *
 * @version 1.0.0
 */
interface ConfigurablePackageInterface
{
    /**
     * Retorna el schema de configuración del package
     *
     * @return array{
     *     schema: array<string, array{
     *         type: string,
     *         label: string,
     *         description?: string,
     *         default: mixed,
     *         validation?: string,
     *         section?: string,
     *         options?: array<string, string>,
     *         encrypted?: bool,
     *         placeholder?: string,
     *         help?: string
     *     }>,
     *     sections?: array<string, string>,
     *     permissions?: array{
     *         view?: string,
     *         edit?: string
     *     }
     * }
     */
    public function getSettingsSchema(): array;

    /**
     * Retorna los valores por defecto de configuración
     *
     * @return array<string, mixed>
     */
    public function getDefaultSettings(): array;

    /**
     * Valida un conjunto de settings según el schema
     *
     * @param  array<string, mixed>  $settings
     * @return bool
     */
    public function validateSettings(array $settings): bool;

    /**
     * Hook que se ejecuta después de actualizar settings
     *
     * @param  array<string, mixed>  $settings
     */
    public function onSettingsUpdated(array $settings): void;

    /**
     * Indica si el package tiene configuración disponible
     */
    public function hasSettings(): bool;
}
