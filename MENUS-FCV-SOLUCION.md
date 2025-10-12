# 🔧 Solución: Menús FCV No Visibles

## ✅ Diagnóstico Completo

El backend está **100% funcional**:
- ✅ Package FCV descubierto correctamente
- ✅ 5 menús configurados y cargados
- ✅ MenuService compilando correctamente
- ✅ Datos compartidos con Inertia

**Los menús están en el backend, solo necesitan verse en el frontend.**

---

## 🚀 Solución en 3 Pasos

### 1. Limpiar Cachés del Backend
```bash
php artisan optimize:clear
php artisan customization:clear-cache
```

### 2. Rebuild del Frontend
```bash
npm run build
```

O si estás en desarrollo:
```bash
npm run dev
```

### 3. Limpiar Caché del Navegador

**Opción A: Hard Refresh**
- **Chrome/Firefox (Linux/Windows):** `Ctrl + Shift + R`
- **Chrome/Firefox (Mac):** `Cmd + Shift + R`

**Opción B: DevTools**
1. Abrir DevTools (F12)
2. Click derecho en el botón de refresh
3. Seleccionar "Empty Cache and Hard Reload"

**Opción C: Ventana de Incógnito**
- `Ctrl + Shift + N` (Chrome)
- `Ctrl + Shift + P` (Firefox)

---

## 📋 Menús que Deberías Ver

Después de seguir los pasos, en el sidebar verás:

### Sección "Operación" (7 items)
1. Mi Módulo (otro package)
2. **Portería** ✨ (FCV)
3. Gestión de Items (otro package)
4. **Cursos** ✨ (FCV)
5. **Organizaciones** ✨ (FCV)
6. **Analytics** ✨ (FCV)
7. **Excepciones** ✨ (FCV)

---

## 🔍 Verificación

### Comando de Diagnóstico
```bash
php debug-menus.php
```

Este script verifica:
- ✅ Archivos del package
- ✅ Clase Package
- ✅ Discovery Service
- ✅ Menu Service
- ✅ Datos de Inertia

### Verificar Packages
```bash
php artisan customization:list-packages
```

Deberías ver:
```
+------------+---------+---------------+-------+
| Nombre     | Versión | Estado        | Menús |
+------------+---------+---------------+-------+
| fcv-access | 0.1.0   | ✅ Habilitado | 5     |
+------------+---------+---------------+-------+
```

---

## 🐛 Si Aún No Se Ven

### 1. Verificar que Vite está corriendo
```bash
# Si usas npm run dev
ps aux | grep vite
```

### 2. Verificar el build
```bash
ls -lh public/build/manifest.json
# Debe ser reciente (minutos atrás)
```

### 3. Verificar consola del navegador
1. Abrir DevTools (F12)
2. Ir a Console
3. Buscar errores
4. Verificar que `page.props.packages.menus.operation` tenga datos

### 4. Verificar Network
1. Abrir DevTools (F12)
2. Ir a Network
3. Recargar página
4. Buscar la petición al dashboard
5. Ver Response → props → packages → menus → operation

---

## 📚 Cómo Funciona

```
packages/fcv/config/menu.php
         ↓
PackageDiscoveryService (auto-discovery)
         ↓
MenuService (compila menús)
         ↓
CustomizationServiceProvider (Inertia::share)
         ↓
app-sidebar.tsx (renderiza)
```

### Código Relevante

**Backend (CustomizationServiceProvider.php):**
```php
Inertia::share('packages', function () {
    $menuService = $this->app->make(MenuService::class);
    return [
        'menus' => [
            'platform' => $menuService->getMenuItemsForSection('platform'),
            'admin' => $menuService->getMenuItemsForSection('admin'),
            'operation' => $menuService->getMenuItemsForSection('operation'),
        ],
    ];
});
```

**Frontend (app-sidebar.tsx):**
```tsx
const packageMenus = page.props?.packages?.menus || {};
const operationItems = packageMenus.operation || [];

// Renderizado
{operationItems.length > 0 && (
    <NavMain items={operationItems} label={t('Operación')} />
)}
```

---

## ✅ Checklist Final

- [ ] Backend: `php artisan optimize:clear`
- [ ] Backend: `php artisan customization:clear-cache`
- [ ] Frontend: `npm run build` (o `npm run dev`)
- [ ] Navegador: Hard refresh (Ctrl+Shift+R)
- [ ] Verificar: Sección "Operación" visible
- [ ] Verificar: 5 menús FCV presentes

---

## 🎯 Resultado Esperado

![Sidebar con menús FCV](https://via.placeholder.com/300x500?text=Sidebar+con+5+men%C3%BAs+FCV)

Deberías ver:
- ✅ Sección "Operación" en el sidebar
- ✅ 5 items del package FCV
- ✅ Iconos correctos (Lucide)
- ✅ Links funcionando

---

## 📞 Soporte

Si después de seguir todos los pasos aún no ves los menús:

1. Ejecutar: `php debug-menus.php`
2. Tomar screenshot de la consola del navegador
3. Verificar que el build sea reciente
4. Probar en ventana de incógnito

**El problema es 99% de caché del navegador.**
