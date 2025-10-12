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

# ============================================
# FCV Package
# ============================================
echo ""
echo "📦 FCV Package"
echo "-------------"

if [ -d "fcv" ]; then
    echo -e "${YELLOW}⚠${NC}  FCV ya existe. Actualizando..."
    cd fcv
    git pull
    cd ..
else
    echo "Clonando FCV package..."
    # TODO: Reemplazar con la URL real del repositorio FCV
    # git clone git@github.com:tu-org/fcv.git fcv
    echo -e "${YELLOW}⚠${NC}  Necesitas configurar la URL del repositorio FCV"
    echo "   Edita: scripts/setup-packages.sh"
    echo "   Descomenta y actualiza la línea git clone"
fi

# ============================================
# Otros Packages (agregar según necesites)
# ============================================
# echo ""
# echo "📦 Otro Package"
# echo "-------------"
# if [ ! -d "otro-package" ]; then
#     git clone git@github.com:tu-org/otro-package.git otro-package
#     echo -e "${GREEN}✓${NC} Otro Package clonado"
# fi

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
echo "2. Ejecuta: composer require like/fcv-access:@dev (o el package que necesites)"
echo "3. Configura tu archivo .env"
echo "4. Ejecuta: php artisan migrate"
echo "5. Ejecuta: npm install && npm run dev"
echo ""
