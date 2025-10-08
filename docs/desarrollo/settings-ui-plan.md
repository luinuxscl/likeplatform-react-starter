# ⚙️ Settings/Configuration UI - Plan de Implementación

## 🎯 Objetivo

Permitir que cada package exponga su propio panel de configuración con UI auto-generada desde un schema, permitiendo a los clientes configurar el sistema sin tocar código.

---

## ✨ Características a Implementar

### 1. **Settings Schema per Package**
- Cada package define su schema en `config/settings.php`
- Tipos de campos: text, number, boolean, select, color, etc.
- Validación automática
- Valores por defecto

### 2. **Auto-Generated UI**
- Formularios generados automáticamente desde el schema
- Componentes React para cada tipo de campo
- Validación en tiempo real
- Preview de cambios

### 3. **Settings Storage**
- Almacenamiento en base de datos (tabla `package_settings`)
- Cache de settings
- API para leer/escribir settings
- Versionado de configuraciones

### 4. **Settings Page**
- Página `/settings` con tabs por package
- Navegación entre packages
- Búsqueda de settings
- Reset to defaults

### 5. **Permissions**
- Control de acceso a settings por package
- Permisos granulares por setting
- Audit log de cambios

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (React)                         │
├─────────────────────────────────────────────────────────────┤
│  SettingsPage                                                │
│  ├─ PackageTabs (uno por package)                           │
│  ├─ SettingsForm (auto-generado desde schema)              │
│  ├─ FieldComponents (text, select, color, etc.)            │
│  └─ SettingsPreview                                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Backend (Laravel)                         │
├─────────────────────────────────────────────────────────────┤
│  SettingsService                                             │
│  ├─ Descubre settings de packages                           │
│  ├─ Valida según schema                                     │
│  ├─ Guarda en DB                                            │
│  └─ Cachea settings                                         │
│                                                              │
│  SettingsRepository                                          │
│  ├─ CRUD de settings                                        │
│  └─ Query builder                                           │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                Package Configuration                         │
├─────────────────────────────────────────────────────────────┤
│  config/settings.php                                         │
│  ├─ Schema definition                                       │
│  ├─ Field types                                             │
│  ├─ Validation rules                                        │
│  └─ Default values                                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Estructura de Archivos

### Backend (Laravel)

```
app/
├── Services/
│   └── SettingsService.php           # Servicio principal
├── Repositories/
│   └── SettingsRepository.php        # Acceso a datos
├── Models/
│   └── PackageSetting.php            # Modelo Eloquent
├── Http/
│   └── Controllers/
│       └── SettingsController.php    # API endpoints
├── Support/
│   └── SettingsValidator.php         # Validador de schemas
└── Contracts/
    └── ConfigurablePackageInterface.php # Interface

database/
└── migrations/
    └── create_package_settings_table.php

config/
└── settings.php                      # Configuración global
```

### Frontend (React)

```
resources/js/
├── pages/
│   └── settings/
│       └── index.tsx                 # Página principal
├── components/
│   └── settings/
│       ├── SettingsForm.tsx          # Formulario auto-generado
│       ├── FieldRenderer.tsx         # Renderiza campos
│       ├── fields/                   # Componentes por tipo
│       │   ├── TextField.tsx
│       │   ├── SelectField.tsx
│       │   ├── ColorField.tsx
│       │   ├── BooleanField.tsx
│       │   └── NumberField.tsx
│       └── SettingsTabs.tsx          # Tabs por package
└── types/
    └── settings.d.ts                 # TypeScript types
```

---

## 🔧 Implementación Paso a Paso

### **Fase 1: Backend Foundation** (Día 1)

#### 1.1 Interface ConfigurablePackageInterface
```php
interface ConfigurablePackageInterface
{
    public function getSettingsSchema(): array;
    public function getDefaultSettings(): array;
    public function validateSettings(array $settings): bool;
}
```

#### 1.2 Modelo PackageSetting
```php
Schema::create('package_settings', function (Blueprint $table) {
    $table->id();
    $table->string('package_name');
    $table->string('key');
    $table->text('value');
    $table->string('type')->default('string');
    $table->timestamps();
    
    $table->unique(['package_name', 'key']);
    $table->index('package_name');
});
```

#### 1.3 SettingsService
- Descubrir packages configurables
- Leer/escribir settings
- Validar según schema
- Cachear settings

---

### **Fase 2: Settings Schema** (Día 1)

#### 2.1 Formato de config/settings.php
```php
return [
    'schema' => [
        'app_name' => [
            'type' => 'text',
            'label' => 'Nombre de la Aplicación',
            'description' => 'Nombre que aparece en el sidebar',
            'default' => 'Mi Aplicación',
            'validation' => 'required|string|max:100',
            'section' => 'general',
        ],
        'primary_color' => [
            'type' => 'color',
            'label' => 'Color Principal',
            'description' => 'Color primario del theme',
            'default' => '#3b82f6',
            'section' => 'appearance',
        ],
        'items_per_page' => [
            'type' => 'number',
            'label' => 'Items por Página',
            'default' => 15,
            'validation' => 'required|integer|min:5|max:100',
            'section' => 'general',
        ],
        'enable_notifications' => [
            'type' => 'boolean',
            'label' => 'Habilitar Notificaciones',
            'default' => true,
            'section' => 'features',
        ],
        'language' => [
            'type' => 'select',
            'label' => 'Idioma',
            'options' => [
                'es' => 'Español',
                'en' => 'English',
            ],
            'default' => 'es',
            'section' => 'general',
        ],
    ],
    
    'sections' => [
        'general' => 'General',
        'appearance' => 'Apariencia',
        'features' => 'Características',
    ],
    
    'permissions' => [
        'view' => 'mi-modulo.settings.view',
        'edit' => 'mi-modulo.settings.edit',
    ],
];
```

---

### **Fase 3: Frontend Components** (Día 2)

#### 3.1 SettingsPage
```tsx
export default function SettingsPage() {
    const { packages } = useSettings();
    
    return (
        <AppLayout>
            <Tabs>
                {packages.map(pkg => (
                    <TabsContent key={pkg.name}>
                        <SettingsForm package={pkg} />
                    </TabsContent>
                ))}
            </Tabs>
        </AppLayout>
    );
}
```

#### 3.2 FieldRenderer
```tsx
function FieldRenderer({ field, value, onChange }) {
    switch (field.type) {
        case 'text':
            return <TextField {...field} value={value} onChange={onChange} />;
        case 'color':
            return <ColorField {...field} value={value} onChange={onChange} />;
        case 'boolean':
            return <BooleanField {...field} value={value} onChange={onChange} />;
        // ... más tipos
    }
}
```

---

### **Fase 4: API Endpoints** (Día 2)

#### 4.1 Rutas
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/{package}', [SettingsController::class, 'show']);
    Route::put('/settings/{package}', [SettingsController::class, 'update']);
    Route::post('/settings/{package}/reset', [SettingsController::class, 'reset']);
});
```

#### 4.2 Controller
```php
public function update(Request $request, string $package)
{
    $settings = $request->validate([
        'settings' => 'required|array',
    ]);
    
    $this->settingsService->updatePackageSettings(
        $package,
        $settings['settings']
    );
    
    return back()->with('success', 'Settings updated');
}
```

---

### **Fase 5: Package Updates** (Día 3)

#### 5.1 Actualizar CustomizationPackage
```php
abstract class CustomizationPackage implements 
    CustomizationPackageInterface,
    ThemeablePackageInterface,
    ConfigurablePackageInterface
{
    public function getSettingsSchema(): array
    {
        $settingsPath = $this->basePath.'/config/settings.php';
        return file_exists($settingsPath) ? require $settingsPath : [];
    }
}
```

#### 5.2 Crear Settings para Packages
- FCV settings
- Mi-modulo settings

---

## 📝 Ejemplo de Uso

### En el Package

```php
// packages/mi-empresa/mi-modulo/config/settings.php
return [
    'schema' => [
        'module_enabled' => [
            'type' => 'boolean',
            'label' => 'Módulo Habilitado',
            'default' => true,
            'section' => 'general',
        ],
        'api_key' => [
            'type' => 'text',
            'label' => 'API Key',
            'description' => 'Clave de API externa',
            'default' => '',
            'validation' => 'nullable|string',
            'section' => 'integration',
            'encrypted' => true, // Encriptar en DB
        ],
    ],
];
```

### Leer Settings en el Código

```php
// En un controller
$apiKey = settings('mi-modulo', 'api_key');

// O con helper
$enabled = package_setting('mi-modulo.module_enabled', true);
```

---

## 🎨 UI Components

### Field Types Soportados

1. **text** - Input de texto
2. **textarea** - Área de texto
3. **number** - Input numérico
4. **boolean** - Switch/Toggle
5. **select** - Dropdown
6. **color** - Color picker
7. **date** - Date picker
8. **time** - Time picker
9. **file** - File upload
10. **json** - JSON editor

---

## ✅ Checklist de Implementación

### Backend
- [ ] Crear `ConfigurablePackageInterface`
- [ ] Crear modelo `PackageSetting`
- [ ] Crear migración
- [ ] Crear `SettingsService`
- [ ] Crear `SettingsRepository`
- [ ] Crear `SettingsValidator`
- [ ] Crear `SettingsController`
- [ ] Agregar rutas
- [ ] Implementar caché
- [ ] Crear helpers (settings(), package_setting())

### Frontend
- [ ] Crear página `/settings`
- [ ] Crear `SettingsForm` component
- [ ] Crear `FieldRenderer`
- [ ] Crear field components (10 tipos)
- [ ] Crear `SettingsTabs`
- [ ] Agregar validación
- [ ] Agregar preview
- [ ] TypeScript types

### Packages
- [ ] Crear `config/settings.php` para FCV
- [ ] Crear `config/settings.php` para mi-modulo
- [ ] Actualizar `CustomizationPackage`

### Testing
- [ ] Tests de `SettingsService`
- [ ] Tests de `SettingsRepository`
- [ ] Tests de validación
- [ ] Tests de API endpoints

### Documentación
- [ ] Guía de creación de settings
- [ ] Ejemplos de schemas
- [ ] API reference
- [ ] Troubleshooting

---

## 🚀 Resultado Esperado

### Antes
```
❌ Configuraciones hardcodeadas en código
❌ Clientes necesitan desarrollador para cambios
❌ Sin UI para configurar
```

### Después
```
✅ UI intuitiva para configurar packages
✅ Clientes pueden configurar sin código
✅ Validación automática
✅ Preview de cambios
✅ Audit log de configuraciones
✅ Permisos granulares
✅ Settings encriptados (API keys, etc.)
```

---

## 📊 Estimación de Tiempo

| Fase | Tiempo | Complejidad |
|------|--------|-------------|
| Backend Foundation | 4-6 horas | Media |
| Settings Schema | 2-3 horas | Baja |
| Frontend Components | 6-8 horas | Media-Alta |
| API Endpoints | 2-3 horas | Baja |
| Package Updates | 2-3 horas | Baja |
| Testing & Docs | 3-4 horas | Media |
| **TOTAL** | **19-27 horas** | **3-4 días** |

---

## 🎯 Métricas de Éxito

- ✅ Cada package puede definir sus settings
- ✅ UI auto-generada desde schema
- ✅ Validación funciona correctamente
- ✅ Settings se guardan en DB
- ✅ Caché funciona
- ✅ Permisos implementados
- ✅ 2+ packages con settings
- ✅ Documentación completa

---

## 🔄 Próximas Iteraciones

### v1.1 - Advanced Settings
- Import/Export de configuraciones
- Settings templates
- Bulk edit

### v1.2 - Settings History
- Versionado de cambios
- Rollback a versión anterior
- Diff entre versiones

### v1.3 - Settings Sync
- Sincronización entre entornos
- Deploy de configuraciones
- Settings as code

---

**Fecha**: 2025-10-08  
**Versión**: 1.0.0  
**Estado**: 📋 Planificación  
**Rama**: feat/settings-ui
