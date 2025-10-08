#!/bin/bash

# Script de verificación del Sistema de Packages de Personalización
# Autor: Sistema de Packages
# Fecha: 2025-10-07

set -e

echo "🔍 Verificando Sistema de Packages de Personalización..."
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para verificar archivo
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        return 0
    else
        echo -e "${RED}✗${NC} $1 (no encontrado)"
        return 1
    fi
}

# Función para verificar directorio
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        return 0
    else
        echo -e "${RED}✗${NC} $1 (no encontrado)"
        return 1
    fi
}

echo "📁 Verificando estructura de archivos..."
echo ""

# Core del sistema
echo "Core del Sistema:"
check_file "app/Contracts/CustomizationPackageInterface.php"
check_file "app/Support/CustomizationPackage.php"
check_file "app/Services/PackageDiscoveryService.php"
check_file "app/Services/MenuService.php"
check_file "app/Providers/CustomizationServiceProvider.php"
check_file "app/Console/Commands/CustomizationClearCacheCommand.php"
check_file "app/Console/Commands/CustomizationListPackagesCommand.php"
check_file "config/customization.php"
echo ""

# Frontend
echo "Frontend:"
check_file "resources/js/lib/icon-resolver.ts"
check_file "resources/js/components/app-sidebar.tsx"
check_file "resources/js/components/nav-main.tsx"
check_file "resources/js/types/index.d.ts"
echo ""

# Documentación
echo "Documentación:"
check_file "docs/SISTEMA_PACKAGES_PERSONALIZACION.md"
check_file "docs/GUIA_RAPIDA_PACKAGES.md"
check_file "docs/CHANGELOG_PACKAGES_SYSTEM.md"
check_file "INSTALACION_SISTEMA_PACKAGES.md"
echo ""

# Package de ejemplo
echo "Package de Ejemplo:"
check_dir "packages/ejemplo/mi-modulo"
check_file "packages/ejemplo/mi-modulo/composer.json"
check_file "packages/ejemplo/mi-modulo/src/Package.php"
check_file "packages/ejemplo/mi-modulo/config/menu.php"
echo ""

# Tests
echo "Tests:"
check_file "tests/Feature/Customization/PackageDiscoveryTest.php"
check_file "tests/Feature/Customization/MenuServiceTest.php"
echo ""

echo "🧪 Verificando comandos Artisan..."
echo ""

# Verificar comandos
if php artisan list | grep -q "customization:list-packages"; then
    echo -e "${GREEN}✓${NC} Comando customization:list-packages registrado"
else
    echo -e "${RED}✗${NC} Comando customization:list-packages no encontrado"
fi

if php artisan list | grep -q "customization:clear-cache"; then
    echo -e "${GREEN}✓${NC} Comando customization:clear-cache registrado"
else
    echo -e "${RED}✗${NC} Comando customization:clear-cache no encontrado"
fi

echo ""

echo "📦 Descubriendo packages..."
echo ""

# Ejecutar discovery
php artisan customization:list-packages

echo ""
echo "✅ Verificación completada!"
echo ""
echo "📚 Próximos pasos:"
echo "  1. Leer: docs/SISTEMA_PACKAGES_PERSONALIZACION.md"
echo "  2. Tutorial: docs/GUIA_RAPIDA_PACKAGES.md"
echo "  3. Instalar ejemplo: php artisan mi-modulo:install"
echo ""
