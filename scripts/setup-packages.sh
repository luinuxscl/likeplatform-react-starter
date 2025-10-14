#!/bin/bash

# Script para clonar packages independientes
# Cada package en /packages tiene su propio repositorio Git

set -e

echo "🔧 Setup de Packages Independientes"
echo "===================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Directorio de packages
PACKAGES_DIR="packages"

# Crear directorio si no existe
if [ ! -d "$PACKAGES_DIR" ]; then
    mkdir -p "$PACKAGES_DIR"
    echo -e "${GREEN}✓${NC} Directorio packages/ creado"
fi

cd "$PACKAGES_DIR"

echo ""
echo "ℹ️  No se clona ningún package por defecto."
echo "    Agrega tus propios repositorios dentro de packages/ según sea necesario."
echo ""

cd ..

# ============================================
# Configurar Composer Repositories
# ============================================
echo ""
echo "⚙️  Configurando Composer repositories..."
echo "=========================================="

# Verificar si existe composer.json
if [ ! -f "composer.json" ]; then
    echo -e "${RED}✗${NC} composer.json no encontrado"
    exit 1
fi

# Agregar path repositories para packages clonados
PACKAGES_CLONED=()

if [ -d "packages/fcv" ]; then
    PACKAGES_CLONED+=("fcv")
fi

if [ ${#PACKAGES_CLONED[@]} -gt 0 ]; then
    echo -e "${YELLOW}ℹ${NC}  Packages encontrados: ${PACKAGES_CLONED[*]}"
    echo ""
    echo "Para usar estos packages, agrega a tu composer.json:"
    echo ""
    echo '  "repositories": ['
    
    for package in "${PACKAGES_CLONED[@]}"; do
        echo '    {'
        echo '      "type": "path",'
        echo "      \"url\": \"packages/$package\","
        echo '      "options": {'
        echo '        "symlink": true'
        echo '      }'
        echo '    },'
    done
    
    echo '  ],'
    echo ""
    echo "Y en require o require-dev según corresponda."
    echo ""
fi

# ============================================
# Composer Install
# ============================================
echo ""
echo "📦 Instalando dependencias con Composer..."
echo "==========================================="

composer install
echo -e "${GREEN}✓${NC} Dependencias instaladas"

echo ""
echo -e "${GREEN}✅ Setup completado${NC}"
echo ""
echo "Próximos pasos:"
echo "1. Configura los path repositories en composer.json (ver arriba)"
echo "2. Añade tus packages a composer.json (por ejemplo: composer require vendor/paquete:@dev)"
echo "3. Configura tu archivo .env"
echo "4. Ejecuta: php artisan migrate"
echo "5. Ejecuta: npm install && npm run dev"
echo ""
