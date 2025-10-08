# 🎨 Theme System - Plan de Implementación

## 🎯 Objetivo

Permitir que cada package defina su propio theme (colores, tipografía, estilos) que se aplica automáticamente cuando el usuario navega por las páginas del package.

---

## ✨ Características a Implementar

### 1. **Theme Configuration per Package**
- Cada package define su theme en `config/theme.php`
- Colores primarios, secundarios, accent
- Tipografía personalizada
- Bordes, sombras, espaciados

### 2. **CSS Variables System**
- Generación automática de CSS variables
- Scope por package (evitar conflictos)
- Fallback al theme base del starterkit

### 3. **Theme Switcher**
- Detección automática del package activo
- Aplicación de theme en tiempo real
- Transiciones suaves entre themes

### 4. **Dark Mode Support**
- Cada package define colores para light/dark
- Respeta preferencia del usuario
- Toggle global de dark mode

### 5. **Theme Preview**
- Vista previa del theme en settings
- Editor visual de colores (opcional)
- Export/Import de themes

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (React)                         │
├─────────────────────────────────────────────────────────────┤
│  ThemeProvider                                               │
│  ├─ Detecta package activo desde URL                        │
│  ├─ Carga theme del package                                 │
│  ├─ Aplica CSS variables                                    │
│  └─ Maneja dark mode                                        │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Backend (Laravel)                         │
├─────────────────────────────────────────────────────────────┤
│  ThemeService                                                │
│  ├─ Compila themes de packages                              │
│  ├─ Genera CSS variables                                    │
│  ├─ Cachea themes compilados                                │
│  └─ Comparte con Inertia                                    │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                Package Configuration                         │
├─────────────────────────────────────────────────────────────┤
│  config/theme.php                                            │
│  ├─ colors (primary, secondary, accent, etc.)               │
│  ├─ typography (fonts, sizes, weights)                      │
│  ├─ spacing (margins, paddings)                             │
│  ├─ borders (radius, width)                                 │
│  └─ shadows                                                  │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Estructura de Archivos

### Backend (Laravel)

```
app/
├── Services/
│   └── ThemeService.php              # Servicio principal de themes
├── Support/
│   └── ThemeCompiler.php             # Compilador de CSS variables
└── Contracts/
    └── ThemeablePackageInterface.php # Interface para packages con theme

config/
└── themes.php                        # Configuración global de themes
```

### Frontend (React)

```
resources/js/
├── contexts/
│   └── ThemeContext.tsx              # Context de React para themes
├── hooks/
│   └── useTheme.ts                   # Hook para usar themes
├── lib/
│   └── theme-utils.ts                # Utilidades de themes
└── types/
    └── theme.d.ts                    # TypeScript types
```

### Package Configuration

```
packages/vendor/package-name/
└── config/
    └── theme.php                     # Theme del package
```

---

## 🔧 Implementación Paso a Paso

### **Fase 1: Backend Foundation** (Día 1)

#### 1.1 Interface ThemeablePackageInterface
```php
interface ThemeablePackageInterface
{
    public function getTheme(): array;
    public function getThemeMode(): string; // 'light', 'dark', 'auto'
}
```

#### 1.2 ThemeService
- Descubrir packages con themes
- Compilar themes a CSS variables
- Cachear themes compilados
- Compartir con Inertia

#### 1.3 Configuración Global
```php
// config/themes.php
return [
    'default' => 'starterkit',
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'modes' => ['light', 'dark'],
];
```

---

### **Fase 2: Theme Configuration** (Día 1)

#### 2.1 Formato de config/theme.php
```php
return [
    'name' => 'Mi Package Theme',
    'colors' => [
        'light' => [
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
            'accent' => '#8b5cf6',
            'background' => '#ffffff',
            'foreground' => '#0f172a',
            'muted' => '#f1f5f9',
            'border' => '#e2e8f0',
        ],
        'dark' => [
            'primary' => '#60a5fa',
            'secondary' => '#94a3b8',
            'accent' => '#a78bfa',
            'background' => '#0f172a',
            'foreground' => '#f8fafc',
            'muted' => '#1e293b',
            'border' => '#334155',
        ],
    ],
    'typography' => [
        'font_family' => 'Inter, system-ui, sans-serif',
        'font_size_base' => '16px',
    ],
    'spacing' => [
        'radius' => '0.5rem',
    ],
];
```

#### 2.2 CSS Variables Generation
```css
[data-package="mi-modulo"] {
  --primary: 59 130 246;
  --secondary: 100 116 139;
  --accent: 139 92 246;
  /* ... más variables */
}
```

---

### **Fase 3: Frontend Integration** (Día 2)

#### 3.1 ThemeContext
```tsx
interface ThemeContextValue {
  currentTheme: Theme | null;
  mode: 'light' | 'dark';
  setMode: (mode: 'light' | 'dark') => void;
  packageTheme: string | null;
}
```

#### 3.2 useTheme Hook
```tsx
const { currentTheme, mode, setMode } = useTheme();
```

#### 3.3 Theme Detection
- Detectar package desde URL
- Aplicar data-attribute al body
- Cargar CSS variables del package

---

### **Fase 4: Package Updates** (Día 2)

#### 4.1 Actualizar CustomizationPackage
```php
abstract class CustomizationPackage implements 
    CustomizationPackageInterface,
    ThemeablePackageInterface
{
    public function getTheme(): array
    {
        $themePath = $this->basePath.'/config/theme.php';
        return file_exists($themePath) ? require $themePath : [];
    }
}
```

#### 4.2 Crear Themes para Packages Existentes
- FCV package theme
- Mi-modulo package theme

---

### **Fase 5: UI Components** (Día 3)

#### 5.1 Theme Switcher Component
```tsx
<ThemeSwitcher 
  mode={mode} 
  onModeChange={setMode}
/>
```

#### 5.2 Theme Preview
- Mostrar colores del theme actual
- Preview de componentes con el theme

#### 5.3 Settings Page
- Configuración de theme por package
- Toggle dark mode
- Reset to default

---

## 📝 Ejemplo de Uso

### En el Package

```php
// packages/mi-empresa/mi-modulo/config/theme.php
return [
    'name' => 'Mi Módulo Theme',
    'colors' => [
        'light' => [
            'primary' => '#10b981', // Verde
            'accent' => '#f59e0b',  // Naranja
        ],
        'dark' => [
            'primary' => '#34d399',
            'accent' => '#fbbf24',
        ],
    ],
];
```

### En el Frontend

```tsx
// Automático - el theme se aplica al navegar
<Link href="/mi-modulo">Mi Módulo</Link>

// O manual
const { currentTheme } = useTheme();
<div style={{ color: currentTheme.colors.primary }}>
  Texto con color del theme
</div>
```

---

## 🎨 CSS Variables Generadas

```css
/* Base del starterkit */
:root {
  --primary: 59 130 246;
  --secondary: 100 116 139;
  /* ... */
}

/* Theme del package FCV */
[data-package="fcv-access"] {
  --primary: 239 68 68;  /* Rojo FCV */
  --secondary: 251 146 60;
  /* ... */
}

/* Theme del package Mi Módulo */
[data-package="mi-modulo"] {
  --primary: 16 185 129;  /* Verde */
  --accent: 245 158 11;   /* Naranja */
  /* ... */
}

/* Dark mode */
.dark [data-package="mi-modulo"] {
  --primary: 52 211 153;
  --accent: 251 191 36;
  /* ... */
}
```

---

## ✅ Checklist de Implementación

### Backend
- [ ] Crear `ThemeablePackageInterface`
- [ ] Crear `ThemeService`
- [ ] Crear `ThemeCompiler`
- [ ] Actualizar `CustomizationPackage`
- [ ] Agregar `config/themes.php`
- [ ] Compartir themes con Inertia
- [ ] Implementar caché de themes
- [ ] Comando Artisan `theme:compile`

### Frontend
- [ ] Crear `ThemeContext`
- [ ] Crear `useTheme` hook
- [ ] Crear `theme-utils.ts`
- [ ] Agregar TypeScript types
- [ ] Implementar detección de package
- [ ] Aplicar data-attributes
- [ ] Crear `ThemeSwitcher` component
- [ ] Integrar con dark mode existente

### Packages
- [ ] Crear `config/theme.php` para FCV
- [ ] Crear `config/theme.php` para mi-modulo
- [ ] Actualizar documentación de packages

### Testing
- [ ] Tests de `ThemeService`
- [ ] Tests de `ThemeCompiler`
- [ ] Tests de `useTheme` hook
- [ ] Tests de detección de package

### Documentación
- [ ] Guía de creación de themes
- [ ] Ejemplos de configuración
- [ ] API reference
- [ ] Troubleshooting

---

## 🚀 Resultado Esperado

### Antes
```
Todos los packages usan los mismos colores del starterkit
```

### Después
```
✅ FCV tiene su theme rojo corporativo
✅ Mi Módulo tiene su theme verde/naranja
✅ Cada cliente puede tener sus colores de marca
✅ Dark mode funciona con todos los themes
✅ Transiciones suaves entre themes
✅ Sin conflictos de estilos
```

---

## 📊 Estimación de Tiempo

| Fase | Tiempo | Complejidad |
|------|--------|-------------|
| Backend Foundation | 4-6 horas | Media |
| Theme Configuration | 2-3 horas | Baja |
| Frontend Integration | 4-6 horas | Media |
| Package Updates | 2-3 horas | Baja |
| UI Components | 3-4 horas | Media |
| Testing & Docs | 2-3 horas | Baja |
| **TOTAL** | **17-25 horas** | **2-3 días** |

---

## 🎯 Métricas de Éxito

- ✅ Cada package puede definir su theme
- ✅ Themes se aplican automáticamente
- ✅ Dark mode funciona correctamente
- ✅ Sin conflictos de estilos
- ✅ Performance: < 50ms para cambiar theme
- ✅ Documentación completa
- ✅ 2+ packages con themes personalizados

---

## 🔄 Próximas Iteraciones

### v1.1 - Theme Editor Visual
- UI para editar colores
- Preview en tiempo real
- Export/Import de themes

### v1.2 - Advanced Theming
- Gradientes personalizados
- Animaciones por theme
- Componentes styled por theme

### v1.3 - Theme Marketplace
- Catálogo de themes predefinidos
- Instalación con un click
- Ratings y reviews

---

**Fecha**: 2025-10-08  
**Versión**: 1.0.0  
**Estado**: 📋 Planificación  
**Rama**: feat/theme-system
