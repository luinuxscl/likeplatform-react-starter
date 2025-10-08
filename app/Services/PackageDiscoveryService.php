<?php

namespace App\Services;

use App\Contracts\CustomizationPackageInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Servicio para auto-descubrir packages de personalización instalados
 */
class PackageDiscoveryService
{
    /**
     * Cache key para los packages descubiertos
     */
    private const CACHE_KEY = 'customization_packages_discovered';

    /**
     * Cache TTL en segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    /**
     * Packages descubiertos
     *
     * @var array<string, CustomizationPackageInterface>
     */
    private array $packages = [];

    /**
     * Indica si ya se ejecutó el discovery
     */
    private bool $discovered = false;

    /**
     * Descubre todos los packages instalados
     *
     * @return array<string, CustomizationPackageInterface>
     */
    public function discover(bool $useCache = true): array
    {
        if ($this->discovered) {
            return $this->packages;
        }

        if ($useCache) {
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached !== null) {
                $this->packages = $this->instantiatePackages($cached);
                $this->discovered = true;

                return $this->packages;
            }
        }

        $this->packages = $this->scanPackages();
        $this->discovered = true;

        // Guardar en caché solo los nombres de clase
        $packageClasses = array_map(fn ($pkg) => get_class($pkg), $this->packages);
        Cache::put(self::CACHE_KEY, $packageClasses, self::CACHE_TTL);

        return $this->packages;
    }

    /**
     * Escanea el directorio packages/ buscando packages válidos
     *
     * @return array<string, CustomizationPackageInterface>
     */
    private function scanPackages(): array
    {
        $packages = [];
        $packagesPath = base_path('packages');

        if (! File::isDirectory($packagesPath)) {
            return $packages;
        }

        // Obtener todos los directorios en packages/
        $directories = File::directories($packagesPath);

        foreach ($directories as $directory) {
            // Intentar cargar directamente (packages/fcv/)
            $package = $this->loadPackage($directory);
            if ($package !== null) {
                $packages[$package->getName()] = $package;
                continue;
            }

            // Si no es un package, asumir que es un vendor (packages/vendor/)
            // y buscar packages dentro
            $vendorPackages = File::directories($directory);
            foreach ($vendorPackages as $packagePath) {
                $package = $this->loadPackage($packagePath);
                if ($package !== null) {
                    $packages[$package->getName()] = $package;
                }
            }
        }

        return $packages;
    }

    /**
     * Intenta cargar un package desde un directorio
     */
    private function loadPackage(string $packagePath): ?CustomizationPackageInterface
    {
        // Buscar archivo Package.php en src/
        $packageFile = $packagePath.'/src/Package.php';

        if (! File::exists($packageFile)) {
            return null;
        }

        // Leer el namespace del composer.json
        $composerFile = $packagePath.'/composer.json';
        if (! File::exists($composerFile)) {
            return null;
        }

        $composer = json_decode(File::get($composerFile), true);
        if (! isset($composer['autoload']['psr-4'])) {
            return null;
        }

        // Obtener el namespace principal
        $namespaces = array_keys($composer['autoload']['psr-4']);
        $namespace = rtrim($namespaces[0] ?? '', '\\');

        if (empty($namespace)) {
            return null;
        }

        $className = $namespace.'\\Package';

        // Verificar que la clase existe y es válida
        if (! class_exists($className)) {
            return null;
        }

        $instance = new $className($packagePath);

        if (! $instance instanceof CustomizationPackageInterface) {
            return null;
        }

        return $instance;
    }

    /**
     * Instancia packages desde nombres de clase cacheados
     *
     * @param  array<string>  $packageClasses
     * @return array<string, CustomizationPackageInterface>
     */
    private function instantiatePackages(array $packageClasses): array
    {
        $packages = [];

        foreach ($packageClasses as $className) {
            if (! class_exists($className)) {
                continue;
            }

            // Determinar el basePath desde el reflection
            $reflection = new \ReflectionClass($className);
            $basePath = dirname(dirname($reflection->getFileName())); // src/Package.php -> base

            $instance = new $className($basePath);

            if ($instance instanceof CustomizationPackageInterface) {
                $packages[$instance->getName()] = $instance;
            }
        }

        return $packages;
    }

    /**
     * Limpia la caché de packages descubiertos
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->packages = [];
        $this->discovered = false;
    }

    /**
     * Retorna un package específico por nombre
     */
    public function getPackage(string $name): ?CustomizationPackageInterface
    {
        if (! $this->discovered) {
            $this->discover();
        }

        return $this->packages[$name] ?? null;
    }

    /**
     * Retorna todos los packages descubiertos
     *
     * @return array<string, CustomizationPackageInterface>
     */
    public function getPackages(): array
    {
        if (! $this->discovered) {
            $this->discover();
        }

        return $this->packages;
    }

    /**
     * Retorna solo los packages habilitados
     *
     * @return array<string, CustomizationPackageInterface>
     */
    public function getEnabledPackages(): array
    {
        return array_filter(
            $this->getPackages(),
            fn (CustomizationPackageInterface $package) => $package->isEnabled()
        );
    }
}
