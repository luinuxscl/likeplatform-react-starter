<?php

namespace App\Support;

use App\Contracts\CustomizationPackageInterface;
use App\Contracts\ThemeablePackageInterface;

/**
 * Clase base abstracta para facilitar la creación de packages de personalización
 */
abstract class CustomizationPackage implements 
    CustomizationPackageInterface,
    ThemeablePackageInterface
{
    /**
     * Path base del package
     */
    protected string $basePath;

    /**
     * Configuración del package cargada desde config/menu.php
     */
    protected ?array $config = null;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Carga la configuración del package desde config/menu.php
     */
    protected function loadConfig(): array
    {
        if ($this->config === null) {
            $configPath = $this->basePath.'/config/menu.php';
            if (file_exists($configPath)) {
                $this->config = require $configPath;
            } else {
                $this->config = [];
            }
        }

        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        $config = $this->loadConfig();

        return $config['items'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        $config = $this->loadConfig();

        return $config['routes'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(): array
    {
        $config = $this->loadConfig();

        return $config['permissions'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getReactComponents(): array
    {
        $config = $this->loadConfig();

        return $config['react_components'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function install(): void
    {
        // Hook vacío por defecto
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Hook vacío por defecto
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return config("customization.packages.{$this->getName()}.enabled", true);
    }

    /**
     * Retorna el path base del package
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme(): array
    {
        $themePath = $this->basePath.'/config/theme.php';
        
        if (file_exists($themePath)) {
            return require $themePath;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeMode(): string
    {
        $theme = $this->getTheme();

        return $theme['mode'] ?? config('themes.default_mode', 'auto');
    }

    /**
     * {@inheritdoc}
     */
    public function hasCustomTheme(): bool
    {
        $themePath = $this->basePath.'/config/theme.php';

        return file_exists($themePath);
    }
}
