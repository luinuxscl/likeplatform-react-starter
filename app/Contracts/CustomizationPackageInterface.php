<?php

namespace App\Contracts;

/**
 * Interface que deben implementar todos los packages de personalización
 * para integrarse automáticamente con el sistema de menús y rutas.
 *
 * @version 1.0.0
 */
interface CustomizationPackageInterface
{
    /**
     * Retorna el nombre único del package
     */
    public function getName(): string;

    /**
     * Retorna la versión del package
     */
    public function getVersion(): string;

    /**
     * Retorna los items de menú del package
     *
     * @return array<int, array{
     *     section: string,
     *     label: string,
     *     icon: string,
     *     route: string,
     *     permission?: string,
     *     order?: int,
     *     active?: bool
     * }>
     */
    public function getMenuItems(): array;

    /**
     * Retorna las rutas web del package
     *
     * @return array<string, array{
     *     method: string,
     *     uri: string,
     *     action: string,
     *     middleware?: array<string>,
     *     name?: string
     * }>
     */
    public function getRoutes(): array;

    /**
     * Retorna los permisos que debe registrar el package
     *
     * @return array<int, array{
     *     name: string,
     *     description?: string,
     *     guard_name?: string
     * }>
     */
    public function getPermissions(): array;

    /**
     * Retorna los componentes React que deben registrarse en Vite
     *
     * @return array<string, string> [alias => path]
     */
    public function getReactComponents(): array;

    /**
     * Hook que se ejecuta durante la instalación del package
     */
    public function install(): void;

    /**
     * Hook que se ejecuta durante la desinstalación del package
     */
    public function uninstall(): void;

    /**
     * Indica si el package está habilitado
     */
    public function isEnabled(): bool;
}
