<?php

namespace App\Contracts;

/**
 * Interface para packages que soportan widgets en el dashboard
 *
 * @version 1.0.0
 */
interface WidgetablePackageInterface
{
    /**
     * Retorna los widgets definidos por el package
     *
     * @return array<int, array{
     *     key: string,
     *     component: string,
     *     title: string,
     *     description?: string,
     *     permission?: string,
     *     size: string,
     *     refresh_interval?: int,
     *     endpoint?: string,
     *     config?: array<string, mixed>,
     *     order?: int,
     *     active?: bool
     * }>
     */
    public function getWidgets(): array;

    /**
     * Indica si el package tiene widgets disponibles
     */
    public function hasWidgets(): bool;

    /**
     * Hook que se ejecuta cuando un widget es refrescado
     *
     * @param  string  $widgetKey
     * @param  array<string, mixed>  $data
     */
    public function onWidgetRefresh(string $widgetKey, array $data): void;

    /**
     * Hook que se ejecuta cuando la configuración de un widget cambia
     *
     * @param  string  $widgetKey
     * @param  array<string, mixed>  $config
     */
    public function onWidgetConfigUpdated(string $widgetKey, array $config): void;
}
