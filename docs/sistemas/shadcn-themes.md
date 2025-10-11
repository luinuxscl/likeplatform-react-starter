# Sistema de Temas shadcn/ui

Sistema completo de temas basado en la paleta oficial de shadcn/ui, con 12 temas predefinidos y soporte para temas personalizados.

## 📋 Características

- ✅ **12 temas predefinidos** de shadcn/ui
- ✅ **Cambio en tiempo real** sin reload de página
- ✅ **Persistencia por usuario** en sesión
- ✅ **Compatible con dark/light mode** existente
- ✅ **CSS variables dinámicas** aplicadas al vuelo
- ✅ **TypeScript completo** sin tipos `any`
- ✅ **Tests Pest** con cobertura completa
- ✅ **Configuración declarativa** en `config/expansion.php`

## 🎨 Temas Disponibles

### Temas Neutrales
1. **Zinc** (default) - Gris neutro moderno
2. **Slate** - Gris azulado profesional
3. **Stone** - Gris cálido natural
4. **Gray** - Gris puro clásico
5. **Neutral** - Gris balanceado

### Temas de Color
6. **Red** - Rojo vibrante
7. **Rose** - Rosa elegante
8. **Orange** - Naranja energético
9. **Green** - Verde fresco
10. **Blue** - Azul profesional
11. **Yellow** - Amarillo brillante
12. **Violet** - Violeta sofisticado

## 🏗️ Arquitectura

### Backend

#### Configuración (`config/expansion.php`)

```php
'themes' => [
    'enabled' => env('EXPANSION_THEMES_ENABLED', true),
    'default_theme' => env('EXPANSION_DEFAULT_THEME', 'zinc'),
    'available_themes' => [
        'zinc' => [
            'name' => 'Zinc',
            'colors' => [
                'primary' => 'hsl(240 5.9% 10%)',
                'primary-foreground' => 'hsl(0 0% 98%)',
                'accent' => 'hsl(240 4.8% 95.9%)',
                'accent-foreground' => 'hsl(240 5.9% 10%)',
                'ring' => 'hsl(240 5% 64.9%)',
                // ... más tokens
            ],
        ],
        // ... 11 temas más
    ],
    'custom_themes' => [
        // Temas personalizados
    ],
],
```

#### Controller (`app/Http/Controllers/Expansion/ThemeController.php`)

```php
public function update(Request $request): RedirectResponse
{
    $available = array_keys(config('expansion.themes.available_themes', []));

    $validated = $request->validate([
        'theme' => ['required', 'string', Rule::in($available)],
    ]);

    Session::put('expansion.theme', $validated['theme']);

    return back()->with('status', 'theme-updated');
}
```

#### Middleware (`app/Http/Middleware/HandleInertiaRequests.php`)

```php
'expansion' => [
    'themes' => [
        'current' => session('expansion.theme', config('expansion.themes.default_theme')),
        'available' => config('expansion.themes.available_themes'),
        'default' => config('expansion.themes.default_theme'),
    ],
],
```

### Frontend

#### Hook (`resources/js/hooks/useTheme.tsx`)

```typescript
export function useTheme(): UseThemeReturn {
  const page = usePage<{ expansion?: { themes?: { ... } } }>()

  const current = page.props.expansion?.themes?.current ?? 'zinc'
  const available = page.props.expansion?.themes?.available ?? {}
  const defaultTheme = page.props.expansion?.themes?.default ?? 'zinc'

  const applyTheme = useCallback((theme: string) => {
    const cfg = available[theme]
    if (!cfg) return

    const root = document.documentElement
    const colors = cfg.colors || {}

    // Solo aplica tokens seguros que no conflictúan con dark/light mode
    const safeKeys = new Set([
      'primary',
      'primary-foreground',
      'accent',
      'accent-foreground',
      'ring',
    ])

    Object.entries(colors).forEach(([key, value]) => {
      if (safeKeys.has(key)) {
        root.style.setProperty(`--${key}`, value)
      }
    })
  }, [available])

  const setTheme = useCallback((theme: string) => {
    applyTheme(theme)
    router.patch('/expansion/themes', { theme }, { 
      preserveScroll: true, 
      preserveState: true 
    })
  }, [applyTheme])

  return { currentTheme, availableThemes, setTheme, applyTheme, defaultTheme }
}
```

#### Component (`resources/js/components/expansion/themes/ThemeSelector.tsx`)

```typescript
export default function ThemeSelector() {
  const { currentTheme, availableThemes, setTheme } = useTheme()
  const [value, setValue] = useState(currentTheme)

  const items = useMemo(
    () => Object.entries(availableThemes).map(([key, cfg]) => ({
      key,
      name: cfg.name,
      colors: cfg.colors
    })),
    [availableThemes]
  )

  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
      {items.map((item) => (
        <button
          key={item.key}
          onClick={() => { setValue(item.key); setTheme(item.key) }}
          className={value === item.key ? 'ring-2 ring-[--ring]' : ''}
        >
          <span>{item.name}</span>
          {/* Preview de colores */}
        </button>
      ))}
    </div>
  )
}
```

## 🚀 Uso

### Cambiar Tema Programáticamente

```typescript
import { useTheme } from '@/hooks/useTheme'

function MyComponent() {
  const { setTheme, currentTheme } = useTheme()

  return (
    <button onClick={() => setTheme('rose')}>
      Cambiar a Rose (actual: {currentTheme})
    </button>
  )
}
```

### Acceder a Temas Disponibles

```typescript
const { availableThemes } = useTheme()

Object.entries(availableThemes).forEach(([key, config]) => {
  console.log(`${key}: ${config.name}`)
  console.log('Colors:', config.colors)
})
```

### Aplicar Tema sin Persistencia

```typescript
const { applyTheme } = useTheme()

// Solo aplica CSS variables, no persiste en backend
applyTheme('blue')
```

### Resetear a Default

```typescript
const { resetDefaults, defaultTheme } = useTheme()

resetDefaults() // Vuelve al tema por defecto (zinc)
```

## 🎯 Integración con Dark/Light Mode

El sistema de temas **NO interfiere** con el sistema de dark/light mode existente (`use-appearance.tsx`):

- **Dark/Light mode** controla: `background`, `foreground`, `card`, `popover`, etc.
- **Theme system** controla: `primary`, `primary-foreground`, `accent`, `ring`

Esto permite combinar cualquier tema con dark o light mode:
- Zinc + Dark
- Rose + Light
- Blue + Dark
- etc.

## 🧪 Testing

### Tests Incluidos

```bash
vendor/bin/pest tests/Feature/ThemeManagementTest.php
```

**Cobertura:**
- ✅ Cambio de tema y persistencia
- ✅ Validación de temas inválidos
- ✅ Compartir datos via Inertia
- ✅ 12 temas configurados correctamente
- ✅ Cambio entre todos los temas
- ✅ Autenticación requerida
- ✅ Validación de parámetros
- ✅ Tokens de color requeridos
- ✅ Estructura de configuración
- ✅ Tema por defecto válido

### Verificación con Laravel Boost

```bash
# Verificar configuración
php artisan tinker
>>> config('expansion.themes.available_themes')
>>> count(config('expansion.themes.available_themes')) // 12

# Verificar tema actual
>>> session('expansion.theme')

# Probar cambio de tema
>>> session()->put('expansion.theme', 'rose')
```

## 🔧 Configuración

### Variables de Entorno

```env
EXPANSION_THEMES_ENABLED=true
EXPANSION_DEFAULT_THEME=zinc
```

### Agregar Tema Personalizado

En `config/expansion.php`:

```php
'custom_themes' => [
    'brand' => [
        'name' => 'Brand Theme',
        'colors' => [
            'primary' => 'hsl(210 100% 50%)',
            'primary-foreground' => 'hsl(0 0% 100%)',
            'accent' => 'hsl(210 100% 95%)',
            'accent-foreground' => 'hsl(210 100% 10%)',
            'ring' => 'hsl(210 100% 50%)',
        ],
    ],
],
```

O desde un Service Provider:

```php
config()->set('expansion.themes.custom_themes.brand', [
    'name' => 'Brand Theme',
    'colors' => [...],
]);
```

## 📊 Tokens CSS Variables

### Tokens Aplicados por el Sistema

El hook `useTheme` solo aplica estos tokens para evitar conflictos:

- `--primary`
- `--primary-foreground`
- `--accent`
- `--accent-foreground`
- `--ring`

### Tokens Controlados por Dark/Light Mode

Estos tokens son controlados por `use-appearance.tsx`:

- `--background`
- `--foreground`
- `--card`
- `--card-foreground`
- `--popover`
- `--popover-foreground`
- `--secondary`
- `--secondary-foreground`
- `--muted`
- `--muted-foreground`
- `--destructive`
- `--destructive-foreground`
- `--border`
- `--input`

## 🎨 Paleta de Colores por Tema

### Zinc (Default)
- Primary: `hsl(240 5.9% 10%)` - Negro suave
- Accent: `hsl(240 4.8% 95.9%)` - Gris muy claro

### Slate
- Primary: `hsl(221.2 83.2% 53.3%)` - Azul vibrante
- Accent: `hsl(210 40% 96.1%)` - Azul muy claro

### Rose
- Primary: `oklch(0.645 0.246 16.439)` - Rosa intenso
- Accent: `oklch(0.967 0.001 286.375)` - Rosa muy claro

### Orange
- Primary: `oklch(0.705 0.213 47.604)` - Naranja vibrante
- Accent: `oklch(0.967 0.001 286.375)` - Naranja muy claro

### Violet
- Primary: `oklch(0.606 0.25 292.717)` - Violeta profundo
- Accent: `oklch(0.967 0.001 286.375)` - Violeta muy claro

### Green
- Primary: `oklch(0.723 0.219 149.579)` - Verde fresco
- Accent: `oklch(0.967 0.001 286.375)` - Verde muy claro

### Blue
- Primary: `oklch(0.623 0.214 259.815)` - Azul profesional
- Accent: `oklch(0.967 0.001 286.375)` - Azul muy claro

### Yellow
- Primary: `oklch(0.795 0.184 86.047)` - Amarillo brillante
- Accent: `oklch(0.967 0.001 286.375)` - Amarillo muy claro

### Red
- Primary: `oklch(0.637 0.237 25.331)` - Rojo intenso
- Accent: `oklch(0.967 0.001 286.375)` - Rojo muy claro

### Stone
- Primary: `hsl(24 9.8% 10%)` - Marrón oscuro
- Accent: `hsl(60 4.8% 95.9%)` - Beige muy claro

### Gray
- Primary: `hsl(0 0% 9%)` - Gris oscuro puro
- Accent: `hsl(0 0% 96.1%)` - Gris muy claro

### Neutral
- Primary: `hsl(0 0% 9%)` - Gris oscuro balanceado
- Accent: `hsl(0 0% 96.1%)` - Gris muy claro

## 🔍 Troubleshooting

### El tema no se aplica

1. Verificar que el tema existe:
```bash
php artisan tinker
>>> array_keys(config('expansion.themes.available_themes'))
```

2. Verificar sesión:
```bash
>>> session('expansion.theme')
```

3. Verificar props de Inertia:
```typescript
console.log(usePage().props.expansion?.themes)
```

### Los colores no cambian

1. Verificar que `useTheme` está aplicando CSS variables:
```typescript
const { applyTheme, currentTheme } = useTheme()
useEffect(() => {
  applyTheme(currentTheme)
}, [currentTheme, applyTheme])
```

2. Inspeccionar CSS variables en DevTools:
```javascript
getComputedStyle(document.documentElement).getPropertyValue('--primary')
```

### Conflicto con dark/light mode

El sistema está diseñado para NO conflictuar. Si hay problemas:

1. Verificar que solo se aplican tokens seguros
2. No modificar `use-appearance.tsx`
3. Revisar que los componentes usan las variables correctas

## 📚 Referencias

- [shadcn/ui Themes](https://ui.shadcn.com/themes)
- [Tailwind CSS Variables](https://tailwindcss.com/docs/customizing-colors#using-css-variables)
- [Inertia.js Shared Data](https://inertiajs.com/shared-data)
- [Laravel Session](https://laravel.com/docs/12.x/session)

## 🎯 Próximos Pasos

- [ ] Persistencia en base de datos (opcional)
- [ ] Preview en tiempo real en ThemeSelector
- [ ] Exportar/importar configuraciones de tema
- [ ] Theme builder UI para temas personalizados
- [ ] Soporte para dark variants de cada tema

---

**Versión**: 1.0.0  
**Última actualización**: 2025-10-11  
**Estado**: ✅ Producción
