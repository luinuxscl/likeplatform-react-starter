# Instalación de maatwebsite/excel

Para habilitar la funcionalidad de importación de alumnos desde Excel, necesitas instalar el paquete `maatwebsite/excel`.

## Instalación

Ejecuta el siguiente comando en la raíz del proyecto:

```bash
composer require maatwebsite/excel
```

## Publicar configuración (opcional)

Si necesitas personalizar la configuración del paquete:

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## Verificación

Para verificar que el paquete está instalado correctamente, puedes ejecutar:

```bash
php artisan tinker
```

Y luego:

```php
use PhpOffice\PhpSpreadsheet\IOFactory;
echo "PhpSpreadsheet instalado correctamente";
```

## Uso en el proyecto

El servicio `CourseStudentImportService` utiliza este paquete para:
- Leer archivos Excel (.xlsx, .xls)
- Leer archivos CSV
- Procesar nóminas de alumnos
- Validar RUTs chilenos
- Crear/actualizar personas y membresías

## Formato del archivo Excel

El archivo debe contener las siguientes columnas (case-insensitive):

| Columna requerida | Alternativas aceptadas |
|-------------------|------------------------|
| `rut` o `run`     | -                      |
| `nombre` o `name` | -                      |
| `email` o `correo` (opcional) | -         |
| `telefono`, `teléfono`, `phone`, `celular` (opcional) | - |

### Ejemplo de archivo válido:

```
RUT         | Nombre                    | Email                  | Telefono
12345678-9  | Juan Pérez González       | juan@example.com       | +56912345678
98765432-1  | María López Silva         | maria@example.com      | +56987654321
```

## Límites

- Tamaño máximo de archivo: 10MB
- Formatos soportados: .xlsx, .xls, .csv
- Sin límite de filas (procesamiento por lotes)

## Troubleshooting

Si encuentras errores relacionados con memoria al procesar archivos grandes:

1. Aumenta el límite de memoria en `php.ini`:
   ```ini
   memory_limit = 512M
   ```

2. O temporalmente en el código (ya incluido en el servicio):
   ```php
   ini_set('memory_limit', '512M');
   ```

## Documentación oficial

Para más información: https://docs.laravel-excel.com/
