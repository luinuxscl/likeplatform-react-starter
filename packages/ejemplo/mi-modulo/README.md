# Mi Módulo - Package de Ejemplo

Este es un package de ejemplo que demuestra cómo crear módulos personalizados que se integran automáticamente con el sistema de menús del starterkit Laravel 12 + React 19.

## Instalación

```bash
composer require ejemplo/mi-modulo
php artisan mi-modulo:install
```

## Características

- ✅ Auto-discovery de menús
- ✅ Registro automático de rutas
- ✅ Gestión de permisos
- ✅ Componentes React integrados
- ✅ Migraciones y seeders

## Estructura

```
mi-modulo/
├── config/
│   └── menu.php          # Configuración de menús y rutas
├── database/
│   ├── migrations/       # Migraciones del módulo
│   └── seeders/          # Seeders de permisos
├── resources/
│   └── js/
│       └── pages/        # Páginas React del módulo
├── src/
│   ├── Http/
│   │   └── Controllers/  # Controladores
│   ├── Models/           # Modelos Eloquent
│   ├── Console/
│   │   └── Commands/     # Comandos Artisan
│   ├── Package.php       # Clase principal del package
│   └── MiModuloServiceProvider.php
└── composer.json
```

## Uso

Una vez instalado, el módulo aparecerá automáticamente en el sidebar bajo la sección "Operación".
