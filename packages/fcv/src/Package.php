<?php

namespace Like\Fcv;

use App\Support\CustomizationPackage;

/**
 * Clase principal del package FCV
 */
class Package extends CustomizationPackage
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'fcv-access';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function install(): void
    {
        // La instalación se maneja con el comando fcv:install existente
        \Artisan::call('fcv:install');
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Lógica de desinstalación si es necesaria
    }
}
