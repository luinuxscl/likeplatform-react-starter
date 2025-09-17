# Directrices para Agentes de IA - Paquete de Expansión Laravel 12 React

## Objetivo Principal

**Este proyecto es fundamentalmente una estructura de reglas y directrices para estandarizar el trabajo de agentes de IA** en el desarrollo de paquetes Laravel React de calidad enterprise. Los agentes deben seguir estas directrices de manera estricta para asegurar consistencia, calidad y compatibilidad.

## Principios Fundamentales para Agentes de IA

### 1. **Adherencia Estricta a Estándares**
- **OBLIGATORIO**: Seguir exactamente las convenciones establecidas en este documento
- **OBLIGATORIO**: Verificar compatibilidad con Laravel 12 React Starter Kit antes de cualquier implementación
- **OBLIGATORIO**: Usar Laravel-Boost para todas las verificaciones y debugging
- **PROHIBIDO**: Desviarse de la estructura de directorios establecida
- **PROHIBIDO**: Modificar archivos del starter kit original

### 1.1. Requisito Obligatorio de Internacionalización (i18n)
- **OBLIGATORIO**: Toda nueva feature debe incluir traducciones en Español (es) e Inglés (en).
- **OBLIGATORIO**: Todos los literales visibles, tooltips, `aria-label`, textos "sr-only" y breadcrumbs deben usar `t('...')`.
- **OBLIGATORIO**: Añadir claves en `lang/en.json` y `lang/es.json` para frontend y en `lang/en/` y `lang/es/` para backend (si aplica).
- **OBLIGATORIO**: Actualizar/crear documentación de i18n cuando se añadan dominios de texto nuevos.
- **REFERENCIA**: Ver `docs/GUIA_I18N.md` para proceso y buenas prácticas.

Checklist i18n obligatorio por feature
- [ ] Sin literales hardcodeados en componentes/páginas; todo usa `useI18n().t`.
- [ ] Claves añadidas en `lang/en.json` y `lang/es.json` (orden alfabético, sin duplicados).
- [ ] Tooltips, `sr-only`, `aria-*`, breadcrumbs y menús traducidos.
- [ ] Mensajes backend (validación/errores) presentes en `lang/en/` y `lang/es/` si corresponde.
- [ ] Prueba manual EN↔ES con el selector del header.
- [ ] Documentación actualizada (sección i18n del feature o referencia a `GUIA_I18N.md`).

### 2. **Workflow de Verificación Continua**
```bash
# Antes de cualquier implementación
php artisan boost:mcp application-info
php artisan boost:mcp list-routes
php artisan boost:mcp database-schema

# Durante implementación
vendor/bin/pest
php artisan boost:mcp last-error
php artisan boost:mcp read-log-entries 10

# Después de implementación
php artisan boost:mcp tinker --code="[verificación específica]"
```

### 3. **Feature Implementation Protocol**
Cada feature debe seguir este protocolo exacto:

#### A. Pre-Implementation Analysis
```bash
1. Analizar starter kit actual con Laravel-Boost
2. Verificar no conflictos con rutas/componentes existentes
3. Revisar documentación shadcn/ui para compatibilidad
4. Confirmar TypeScript types necesarios
```

#### B. Implementation Steps
```bash
1. Backend: Models → Migrations → Controllers → Tests
2. Frontend: Types → Components → Pages → Integration
3. Configuration: Config files → Service providers → Routes
4. Testing: Unit → Feature → Integration con Laravel-Boost
```

#### C. Post-Implementation Verification
```bash
1. Tests Pest: 100% passing
2. Laravel-Boost: Sin errores
3. TypeScript: Sin errores de tipos
4. shadcn/ui: Compatibilidad mantenida
5. Manual testing: Funcionalidad verificada
```

## Primera Feature Prioritaria: Sistema de Temas shadcn/ui

### Objetivo de la Feature
Implementar sistema de temas basado en [shadcn/ui themes](https://ui.shadcn.com/themes) que sea:
- **Configurable**: Múltiples temas predefinidos
- **Personalizable**: Custom themes vía configuración
- **Integrado**: Compatible con el sistema de apariencia existente
- **Extensible**: Fácil añadir nuevos temas

### Directrices Específicas para Agentes de IA

#### 1. **Análisis Requerido Antes de Implementar**
```bash
# Verificar sistema de apariencia actual
php artisan boost:mcp tinker --code="
  echo 'Current appearance system: ';
  var_dump(config('app'));
  echo 'Tailwind config: ';
  // Verificar recursos/js/hooks/use-appearance.tsx
"

# Verificar componentes UI existentes
php artisan boost:mcp list-routes | grep appearance
```

#### 2. **Estructura de Implementación Obligatoria**

##### Backend Configuration
```php
// config/expansion.php - Sección themes
'themes' => [
    'enabled' => env('EXPANSION_THEMES_ENABLED', true),
    'default_theme' => env('EXPANSION_DEFAULT_THEME', 'zinc'),
    'available_themes' => [
        'zinc' => [
            'name' => 'Zinc',
            'colors' => [
                'background' => 'hsl(0 0% 100%)',
                'foreground' => 'hsl(240 10% 3.9%)',
                // ... colores completos según shadcn/ui
            ]
        ],
        'slate' => [
            'name' => 'Slate', 
            'colors' => [
                // ... definición completa
            ]
        ],
        'stone' => [
            'name' => 'Stone',
            'colors' => [
                // ... definición completa
            ]
        ],
        'gray' => [
            'name' => 'Gray',
            'colors' => [
                // ... definición completa
            ]
        ],
        'neutral' => [
            'name' => 'Neutral',
            'colors' => [
                // ... definición completa
            ]
        ],
        'red' => [
            'name' => 'Red',
            'colors' => [
                // ... definición completa
            ]
        ],
        'rose' => [
            'name' => 'Rose',
            'colors' => [
                // ... definición completa
            ]
        ],
        'orange' => [
            'name' => 'Orange',
            'colors' => [
                // ... definición completa
            ]
        ],
        'green' => [
            'name' => 'Green',
            'colors' => [
                // ... definición completa
            ]
        ],
        'blue' => [
            'name' => 'Blue',
            'colors' => [
                // ... definición completa
            ]
        ],
        'yellow' => [
            'name' => 'Yellow',
            'colors' => [
                // ... definición completa
            ]
        ],
        'violet' => [
            'name' => 'Violet',
            'colors' => [
                // ... definición completa
            ]
        ]
    ],
    'custom_themes' => [
        // Permite temas personalizados
    ]
],
```

##### Frontend Components Obligatorios
```typescript
// resources/js/components/expansion/themes/ThemeSelector.tsx
interface ThemeConfig {
    name: string;
    colors: Record<string, string>;
}

interface ThemeSelectorProps {
    currentTheme: string;
    availableThemes: Record<string, ThemeConfig>;
    onThemeChange: (theme: string) => void;
}

// resources/js/hooks/useTheme.tsx  
interface UseThemeReturn {
    currentTheme: string;
    setTheme: (theme: string) => void;
    availableThemes: Record<string, ThemeConfig>;
    applyTheme: (theme: string) => void;
}

// resources/js/types/expansion.d.ts - Añadir
interface ThemeConfig {
    name: string;
    colors: Record<string, string>;
}

interface ExpansionConfig {
    themes: {
        enabled: boolean;
        default_theme: string;
        available_themes: Record<string, ThemeConfig>;
        custom_themes: Record<string, ThemeConfig>;
    };
}
```

#### 3. **Integration Requirements**

##### Con Sistema de Apariencia Existente
```typescript
// Debe integrarse con resources/js/hooks/use-appearance.tsx
// NO reemplazar, sino extender el sistema existente
// Mantener compatibilidad con dark/light mode actual
```

##### Con Middleware de Inertia
```php
// app/Http/Middleware/HandleInertiaRequests.php
// Añadir en método share():
'expansion' => [
    'themes' => [
        'current' => session('expansion.theme', config('expansion.themes.default_theme')),
        'available' => config('expansion.themes.available_themes'),
    ]
]
```

#### 4. **Testing Protocol para Agentes de IA**

```php
// tests/Feature/ThemeManagementTest.php
class ThemeManagementTest extends TestCase
{
    public function test_can_change_theme(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->patch('/expansion/themes', ['theme' => 'blue']);
            
        $response->assertRedirect();
        $this->assertEquals('blue', session('expansion.theme'));
    }
    
    public function test_theme_persists_across_sessions(): void
    {
        // Verificar persistencia
    }
    
    public function test_invalid_theme_rejected(): void
    {
        // Verificar validación
    }
    
    public function test_theme_css_variables_applied(): void
    {
        // Verificar aplicación de CSS
    }
}
```

##### Laravel-Boost Verification Protocol
```bash
# Después de implementar temas
php artisan boost:mcp tinker --code="
  // Verificar configuración
  \$themes = config('expansion.themes.available_themes');
  echo 'Available themes: ' . count(\$themes) . \"\n\";
  
  // Verificar sesión
  session(['expansion.theme' => 'blue']);
  echo 'Current theme: ' . session('expansion.theme') . \"\n\";
  
  return \$themes;
"

# Verificar rutas de temas
php artisan boost:mcp list-routes | grep theme

# Verificar base de datos si hay persistencia
php artisan boost:mcp database-query "SELECT * FROM user_preferences WHERE key = 'theme' LIMIT 5"
```

#### 5. **UI/UX Requirements**

##### Theme Selector Component
- **Ubicación**: Settings → Appearance (extender página existente)
- **Design**: Seguir patrones de shadcn/ui
- **Preview**: Mostrar preview de colores antes de aplicar
- **Persistence**: Guardar preferencia en sesión/database
- **Accessibility**: WCAG 2.1 AA compliance

##### Theme Application
- **CSS Variables**: Usar custom properties para cambios dinámicos
- **No Page Reload**: Cambio en tiempo real
- **Dark Mode**: Compatible con sistema dark/light existente
- **Components**: Todos los componentes shadcn/ui deben responder al tema

### Validation Checklist para Agentes de IA

Antes de considerar la feature completa, verificar:

#### Backend ✅
- [ ] Configuración de temas en `config/expansion.php`
- [ ] Controller para cambio de temas
- [ ] Middleware integration en `HandleInertiaRequests`
- [ ] Validación de temas disponibles
- [ ] Persistencia de preferencias (sesión o DB)

#### Frontend ✅
- [ ] `ThemeSelector` component funcional
- [ ] `useTheme` hook implementado
- [ ] Integration con `use-appearance` existente
- [ ] CSS variables aplicadas dinámicamente
- [ ] TypeScript types completos

#### Testing ✅
- [ ] Tests Pest passing al 100%
- [ ] Laravel-Boost verification clean
- [ ] Manual testing en todos los temas
- [ ] Dark/light mode compatibility
- [ ] Responsive design verificado

#### Integration ✅
- [ ] Zero breaking changes al starter kit
- [ ] Compatible con componentes existentes
- [ ] Performance acceptable (no lag en cambio)
- [ ] Accessibility compliant
- [ ] Documentation actualizada

## Error Prevention for AI Agents

### Common Mistakes to Avoid

#### 1. **NO modificar archivos del starter kit**
```bash
# PROHIBIDO tocar estos archivos:
resources/js/hooks/use-appearance.tsx  # Solo extender, no modificar
resources/js/components/app-*.tsx     # No modificar
tailwind.config.ts                   # Si existe, no modificar
```

#### 2. **NO ignorar Laravel-Boost verification**
```bash
# OBLIGATORIO después de cada cambio:
php artisan boost:mcp last-error
vendor/bin/pest
```

#### 3. **NO implementar sin planning**
```bash
# OBLIGATORIO antes de implementar:
1. Leer documentación shadcn/ui themes
2. Analizar starter kit actual
3. Verificar no conflictos
4. Planear integration strategy
```

#### 4. **NO commits sin verification completa**
```bash
# Checklist pre-commit:
- [ ] Tests passing
- [ ] Laravel-Boost clean
- [ ] TypeScript no errors
- [ ] Manual testing done
- [ ] Documentation updated
```

## Success Metrics for AI Agents

### Feature Implementation Success
- **Functionality**: Cambio de tema funciona sin errores
- **Performance**: Cambio < 200ms
- **Compatibility**: Zero breaking changes
- **Quality**: Tests coverage ≥ 90%
- **Documentation**: Completa y actualizada

### Code Quality Success
- **PSR-12**: PHP 100% compliant
- **ESLint**: TypeScript 100% compliant
- **TypeScript**: Sin `any` types
- **shadcn/ui**: Compatibility mantenida
- **Laravel-Boost**: Verification clean

Esta feature de temas servirá como **proof of concept** para verificar que los agentes de IA pueden seguir las directrices establecidas y producir código de calidad enterprise que se integre perfectamente con el Laravel 12 React Starter Kit.
