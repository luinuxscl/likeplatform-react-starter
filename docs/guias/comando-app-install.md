# Comando de instalación de la aplicación

Comando Artisan para dejar operativa la app ejecutando migraciones, seeders y tareas de limpieza.

## Uso

- Instalación en modo desarrollo (recrea base de datos y ejecuta seeders):

```bash
php artisan app:install --dev
```

Incluye:
- `optimize:clear` (limpieza previa)
- `migrate:fresh --seed --force`
- `storage:link`
- `optimize:clear` (limpieza final)

- Instalación estándar (sin borrar tablas):

```bash
php artisan app:install
```

Incluye:
- `migrate --force`
- `db:seed --force`
- `storage:link`
- `optimize:clear`

## Notas
- Ejecuta siempre en el entorno correcto (`APP_ENV`) y con la conexión de DB configurada.
- En entornos CI puedes usar `--force` implícito dentro de los subcomandos.
- En producción, evita `--dev`.
