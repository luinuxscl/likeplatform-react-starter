# FCV Access Package

Paquete modular para control de acceso de Fundación Cristo Vive.

## Características (MVP)
- Gestión de organizaciones, personas, membresías, cursos/horarios.
- Lógica centralizada de reglas de acceso (horario estricto / flexible / acceso total).
- Bitácora de accesos con retención configurable.
- Integración Inertia/React (shadcn/ui) sin modificar el starter kit base.

## Instalación (en app host)
1. Asegúrate de que el paquete esté disponible en `packages/fcv` y autoload PSR-4 configurado.
2. El Service Provider `Like\Fcv\Providers\FcvServiceProvider` se registra automáticamente vía `extra.laravel.providers`.
3. Publica configuración y assets si es necesario:

```bash
php artisan vendor:publish --tag=fcv-config
php artisan vendor:publish --tag=fcv-js
```

## Rutas
- `GET /fcv/health` (autenticado): endpoint de salud del paquete.

## Configuración
- Archivo `config/fcv.php` con presets de reglas y retención de bitácora.
