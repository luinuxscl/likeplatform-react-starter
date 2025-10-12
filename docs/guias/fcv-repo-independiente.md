# 📦 FCV Package - Repositorio Independiente

Guía para convertir el package FCV en un repositorio Git independiente.

---

## 🎯 Objetivo

Separar el package FCV del starter kit para que tenga su propio repositorio, versionado y ciclo de desarrollo independiente.

---

## 📋 Pasos para Crear el Repo FCV

### 1. Crear el Repositorio en GitHub

```bash
# En GitHub, crear nuevo repositorio:
# Nombre: fcv-access
# Descripción: FCV Access Control Package for Laravel
# Visibilidad: Private o Public según necesites
```

### 2. Inicializar Git en el Package

```bash
# Desde la raíz del starter kit
cd packages/fcv

# Inicializar Git
git init

# Crear .gitignore específico para el package
cat > .gitignore << 'EOF'
/vendor/
/node_modules/
composer.lock
.phpunit.result.cache
.DS_Store
Thumbs.db
EOF

# Primer commit
git add .
git commit -m "feat: Initial commit - FCV Access Control Package

- Sistema completo de control de acceso
- Modelos: Person, Organization, Course, Membership, AccessLog, AccessException
- Services: AccessRuleService, FcvAnalyticsService
- Controllers: API completa con 12 endpoints
- Tests: 22 tests passing (82 assertions)
- Integración con sistema de auditoría
- Analytics basado en AuditLog
- Sistema de excepciones de acceso
- Seeders con datos de ejemplo"

# Conectar con GitHub
git remote add origin git@github.com:tu-org/fcv-access.git
git branch -M main
git push -u origin main
```

### 3. Actualizar composer.json del Package

Asegúrate que `packages/fcv/composer.json` tenga la configuración correcta:

```json
{
    "name": "like/fcv-access",
    "description": "FCV Access Control Package for Laravel",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Luis Sepúlveda",
            "email": "tu-email@example.com"
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0"
    },
    "autoload": {
        "psr-4": {
            "Like\\Fcv\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Like\\Fcv\\Providers\\FcvServiceProvider"
            ]
        }
    }
}
```

### 4. Crear README.md del Package

```bash
cd packages/fcv

cat > README.md << 'EOF'
# FCV Access Control Package

Sistema completo de control de acceso para Fundación Cristo Vive.

## Características

- ✅ Gestión de personas, organizaciones y membresías
- ✅ Control de acceso basado en cursos y horarios
- ✅ Sistema de excepciones de acceso
- ✅ Bitácora completa de accesos
- ✅ Analytics y reportes
- ✅ Integración con sistema de auditoría
- ✅ API REST completa
- ✅ Tests completos (22 tests, 82 assertions)

## Instalación

```bash
composer require like/fcv-access
```

## Configuración

Publicar configuración:

```bash
php artisan vendor:publish --tag=fcv-config
```

Ejecutar migraciones:

```bash
php artisan migrate
```

Seeders (opcional):

```bash
php artisan db:seed --class=Like\\Fcv\\Database\\Seeders\\FcvBaseSeeder
```

## Uso

Ver documentación completa en `/docs`.

## Licencia

MIT
EOF

git add README.md composer.json
git commit -m "docs: Add package README and update composer.json"
git push
```

### 5. Actualizar Script de Setup del Starter Kit

En el starter kit, edita `scripts/setup-packages.sh`:

```bash
# Reemplazar la línea comentada con:
git clone git@github.com:tu-org/fcv-access.git fcv
```

---

## 🔄 Workflow de Desarrollo

### Trabajar en el Package FCV

```bash
# 1. Ir al directorio del package
cd packages/fcv

# 2. Crear rama para nueva feature
git checkout -b feat/nueva-funcionalidad

# 3. Hacer cambios y commits
git add .
git commit -m "feat: descripción del cambio"

# 4. Push a tu repo
git push origin feat/nueva-funcionalidad

# 5. Crear Pull Request en GitHub
```

### Actualizar FCV en Proyectos

```bash
# En el starter kit o cualquier proyecto que use FCV
cd packages/fcv
git pull origin main

# Composer detecta cambios automáticamente (symlink)
```

---

## 📦 Publicar Versiones

### Crear Release en GitHub

```bash
cd packages/fcv

# Tag de versión
git tag -a v1.0.0 -m "Release v1.0.0

- Sistema completo de control de acceso
- API REST con 12 endpoints
- Analytics y reportes
- 22 tests passing"

git push origin v1.0.0
```

### Usar Versión Específica

En proyectos que usen FCV, actualizar `composer.json`:

```json
{
    "require": {
        "like/fcv-access": "^1.0"
    }
}
```

---

## 🔧 Mantenimiento

### Sincronizar con Starter Kit

Si hay cambios en el sistema de packages del starter kit que afecten a FCV:

```bash
cd packages/fcv

# Actualizar según cambios del starter kit
# Hacer commits específicos
git commit -m "chore: Update compatibility with starter kit v2.0"
git push
```

### Tests

```bash
cd packages/fcv

# Ejecutar tests del package
../../vendor/bin/pest tests/
```

---

## 📝 Convenciones

### Commits

Seguir [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` - Nueva funcionalidad
- `fix:` - Corrección de bugs
- `docs:` - Documentación
- `test:` - Tests
- `refactor:` - Refactorización
- `chore:` - Mantenimiento

### Versionado

Seguir [Semantic Versioning](https://semver.org/):

- `MAJOR.MINOR.PATCH`
- Ejemplo: `1.2.3`

---

## 🚀 Próximos Pasos

1. ✅ Crear repositorio en GitHub
2. ✅ Inicializar Git en `packages/fcv`
3. ✅ Primer commit y push
4. ✅ Actualizar `scripts/setup-packages.sh`
5. ⏳ Desarrollar frontend de FCV
6. ⏳ Crear releases versionadas
7. ⏳ Documentación completa

---

**Última actualización**: 2025-10-12  
**Versión**: 1.0.0
