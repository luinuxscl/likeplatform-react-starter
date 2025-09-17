# Roadmap Actualizado - Sistema de Temas como Primera Prioridad

## Nuevo Objetivo Principal

**Este proyecto es fundamentalmente una estructura de reglas y directrices para estandarizar el trabajo de agentes de IA** en el desarrollo de paquetes Laravel React de calidad enterprise. La primera feature implementada servirá como **proof of concept** para verificar que los agentes pueden seguir las directrices establecidas.

## Primera Feature: Sistema de Temas shadcn/ui (Prioridad Máxima)

### Objetivo de Validación
Esta feature servirá para **probar que los agentes de IA respetan las directrices** establecidas en la documentación del proyecto. Es una feature relativamente simple pero que toca todos los aspectos críticos:
- Backend (configuración, controladores)
- Frontend (React, TypeScript, shadcn/ui)
- Integration (con starter kit existente)
- Testing (Pest + Laravel-Boost)

### Feature Specification: Temas shadcn/ui
**Basado en**: https://ui.shadcn.com/themes

#### Funcionalidades Requeridas
- **12 temas predefinidos**: zinc, slate, stone, gray, neutral, red, rose, orange, green, blue, yellow, violet
- **Configuración fácil**: Via archivo config y UI
- **Personalización**: Permite temas custom via configuración
- **Persistencia**: Guardar preferencia por usuario
- **Tiempo real**: Cambio sin reload de página
- **Compatibilidad**: Con sistema dark/light existente

## Roadmap Actualizado por Fases

### 🔴 **PRIORIDAD CRÍTICA - Fase 0: Sistema de Temas** (1-2 semanas)

#### Semana 1: Setup y Backend
```bash
# Día 1-2: Setup de estructura base
- Crear estructura de paquete según docs/ESTRUCTURA_PAQUETE_EXPANSION.md
- Setup básico de composer.json y service provider
- Configuración de temas en config/expansion.php

# Día 3-5: Backend implementation
- ThemeController para cambio de temas
- Middleware integration en HandleInertiaRequests
- Models/migrations si se requiere persistencia en DB
- Tests Pest para backend

# Verificación Laravel-Boost cada día:
php artisan boost:mcp application-info
php artisan boost:mcp last-error
vendor/bin/pest
```

#### Semana 2: Frontend y Integration
```bash
# Día 1-3: React Components
- ThemeSelector component
- useTheme hook
- Integration con use-appearance existente
- CSS variables system

# Día 4-5: Testing y Polish
- Tests Pest para frontend behavior
- Manual testing de todos los temas
- Performance optimization
- Documentation

# Verificación Laravel-Boost continua:
php artisan boost:mcp browser-logs 10
php artisan boost:mcp tinker --code="config('expansion.themes')"
```

#### Success Criteria para Agentes de IA
- ✅ **12 temas shadcn/ui funcionando** perfectamente
- ✅ **Zero breaking changes** al starter kit original
- ✅ **Tests Pest al 100%** sin errores
- ✅ **Laravel-Boost verification clean** en todas las verificaciones
- ✅ **TypeScript types completos** sin `any`
- ✅ **Configurable via archivo config** y UI
- ✅ **Persistencia de preferencias** funcional
- ✅ **Responsive y accessible** WCAG 2.1 AA

### 🟠 **PRIORIDAD ALTA - Fase 1: Roles Básicos** (2-3 semanas)

#### Solo después de que Fase 0 esté 100% completa
- Sistema de roles y permisos básico
- UI React para gestión de roles
- Tests Pest completos
- Integration con spatie/laravel-permission

### 🟡 **PRIORIDAD MEDIA - Fase 2: Media Básico** (2-3 semanas)
- Sistema de upload de archivos
- Gallery básica
- Tests y integration

### 🟢 **PRIORIDAD BAJA - Fase 3: Dashboard Funcional** (1-2 semanas)
- Dashboard con widgets reales
- Analytics básicos
- Configuración de widgets

## Validation Protocol para Agentes de IA

### Pre-Implementation (Obligatorio)
```bash
# 1. Análisis del starter kit actual
php artisan boost:mcp application-info
php artisan boost:mcp list-routes
php artisan boost:mcp database-schema

# 2. Verificar sistema de apariencia existente
php artisan boost:mcp tinker --code="
  echo 'Current appearance hook: ';
  // Analizar resources/js/hooks/use-appearance.tsx
"

# 3. Estudiar documentación shadcn/ui themes
# Leer: https://ui.shadcn.com/themes
# Verificar: Compatibilidad con Tailwind 4.x
```

### During Implementation (Cada commit)
```bash
# Tests obligatorios
vendor/bin/pest

# Laravel-Boost verification
php artisan boost:mcp last-error
php artisan boost:mcp read-log-entries 5

# TypeScript verification
npm run type-check
npm run lint
```

### Post-Implementation (Antes de merge)
```bash
# Comprehensive testing
vendor/bin/pest --coverage
php artisan boost:mcp tinker --code="
  // Test all themes
  \$themes = config('expansion.themes.available_themes');
  foreach(\$themes as \$key => \$theme) {
    echo 'Theme: ' . \$key . ' - ' . \$theme['name'] . \"\n\";
  }
  return count(\$themes);
"

# Manual testing checklist:
# - [ ] Todos los 12 temas se aplican correctamente
# - [ ] Cambio en tiempo real sin reload
# - [ ] Persistencia funciona entre sesiones
# - [ ] Compatible con dark/light mode
# - [ ] Responsive en mobile
# - [ ] No errores en console
# - [ ] Performance acceptable (<200ms)
```

## Technical Specifications para la Feature de Temas

### Backend Architecture
```php
// config/expansion.php - Themes section
'themes' => [
    'enabled' => env('EXPANSION_THEMES_ENABLED', true),
    'default_theme' => env('EXPANSION_DEFAULT_THEME', 'zinc'),
    'persistence' => env('EXPANSION_THEMES_PERSISTENCE', 'session'), // session|database
    'available_themes' => [
        'zinc' => [
            'name' => 'Zinc',
            'type' => 'neutral',
            'colors' => [
                'background' => 'hsl(0 0% 100%)',
                'foreground' => 'hsl(240 10% 3.9%)',
                'card' => 'hsl(0 0% 100%)',
                'card-foreground' => 'hsl(240 10% 3.9%)',
                'popover' => 'hsl(0 0% 100%)',
                'popover-foreground' => 'hsl(240 10% 3.9%)',
                'primary' => 'hsl(240 5.9% 10%)',
                'primary-foreground' => 'hsl(0 0% 98%)',
                'secondary' => 'hsl(240 4.8% 95.9%)',
                'secondary-foreground' => 'hsl(240 5.9% 10%)',
                'muted' => 'hsl(240 4.8% 95.9%)',
                'muted-foreground' => 'hsl(240 3.8% 46.1%)',
                'accent' => 'hsl(240 4.8% 95.9%)',
                'accent-foreground' => 'hsl(240 5.9% 10%)',
                'destructive' => 'hsl(0 84.2% 60.2%)',
                'destructive-foreground' => 'hsl(0 0% 98%)',
                'border' => 'hsl(240 5.9% 90%)',
                'input' => 'hsl(240 5.9% 90%)',
                'ring' => 'hsl(240 5.9% 10%)',
                'radius' => '0.5rem',
            ],
            'dark_colors' => [
                'background' => 'hsl(240 10% 3.9%)',
                'foreground' => 'hsl(0 0% 98%)',
                // ... dark theme colors
            ]
        ],
        // ... otros 11 temas con especificaciones completas
    ]
],
```

### Frontend Architecture
```typescript
// resources/js/types/themes.d.ts
interface ThemeColor {
    [key: string]: string;
}

interface ThemeConfig {
    name: string;
    type: 'neutral' | 'colored';
    colors: ThemeColor;
    dark_colors: ThemeColor;
}

interface ThemesConfig {
    enabled: boolean;
    default_theme: string;
    persistence: 'session' | 'database';
    available_themes: Record<string, ThemeConfig>;
}

// resources/js/hooks/useTheme.tsx
interface UseThemeReturn {
    currentTheme: string;
    setTheme: (theme: string) => void;
    availableThemes: Record<string, ThemeConfig>;
    applyTheme: (theme: string) => void;
    isLoading: boolean;
}

export function useTheme(): UseThemeReturn {
    // Implementation with persistence and CSS variables
}

// resources/js/components/expansion/themes/ThemeSelector.tsx
interface ThemeSelectorProps {
    className?: string;
    showPreview?: boolean;
    groupByType?: boolean;
}

export function ThemeSelector(props: ThemeSelectorProps) {
    // Implementation with shadcn/ui components
}
```

### CSS Variables System
```css
/* Debe aplicar dinámicamente en :root */
:root {
  --background: [theme-color];
  --foreground: [theme-color];
  --card: [theme-color];
  --card-foreground: [theme-color];
  --popover: [theme-color];
  --popover-foreground: [theme-color];
  --primary: [theme-color];
  --primary-foreground: [theme-color];
  --secondary: [theme-color];
  --secondary-foreground: [theme-color];
  --muted: [theme-color];
  --muted-foreground: [theme-color];
  --accent: [theme-color];
  --accent-foreground: [theme-color];
  --destructive: [theme-color];
  --destructive-foreground: [theme-color];
  --border: [theme-color];
  --input: [theme-color];
  --ring: [theme-color];
  --radius: [theme-radius];
}
```

## Error Prevention Checklist

### Para Agentes de IA - OBLIGATORIO verificar

#### ❌ **NO HACER** (Causará fallos)
- Modificar `resources/js/hooks/use-appearance.tsx`
- Modificar `tailwind.config.ts` si existe
- Reemplazar sistema de dark/light mode existente
- Ignorar Laravel-Boost verification
- Usar `any` types en TypeScript
- Commitear sin tests passing
- Implementar sin leer documentación shadcn/ui

#### ✅ **HACER** (Para éxito)
- Extender sistema de apariencia existente
- Verificar con Laravel-Boost cada cambio
- Tests Pest para toda funcionalidad nueva
- TypeScript types completos y precisos
- Seguir estructura de directorios establecida
- Documentar cada component y function
- Manual testing en todos los temas

## Success Metrics para Fase 0

### Funcionalidad ✅
- **12 temas shadcn/ui** funcionando sin errores
- **Cambio en tiempo real** < 200ms
- **Persistencia** entre sesiones
- **Compatibility** con dark/light mode
- **Responsive** en todos los breakpoints

### Código ✅
- **Tests Pest** 100% passing con coverage ≥ 90%
- **Laravel-Boost** verification clean
- **TypeScript** 0 errores, 0 warnings
- **ESLint** 0 errores, 0 warnings
- **Performance** sin memory leaks o lag

### Integration ✅
- **Zero breaking changes** al starter kit
- **Seamless integration** con componentes existentes
- **shadcn/ui compatibility** mantenida
- **Tailwind 4** compatibility verificada
- **Accessibility** WCAG 2.1 AA compliant

Esta feature de temas servirá como el **gold standard** para validar que los agentes de IA pueden:
1. **Seguir directrices** estrictamente
2. **Integrar sin romper** funcionalidad existente
3. **Producir código de calidad** enterprise
4. **Usar Laravel-Boost** efectivamente para development
5. **Crear features complejas** que funcionen perfectamente

Una vez completada exitosamente, servirá como template y referencia para todas las features futuras del paquete de expansión.
