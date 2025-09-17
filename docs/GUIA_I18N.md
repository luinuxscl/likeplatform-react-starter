# Guía de Internacionalización (i18n)

Esta guía define el flujo para traducir nuevas funcionalidades a Español (es) e Inglés (en) en el Starter Kit Laravel 12 + React 19 + Inertia 2.

## Principios
- Máxima integración con el ecosistema Laravel oficial.
- Sencillez y claridad: claves cortas y reutilizables.
- Compatibilidad con el Starter Kit base.
- Toda feature debe salir al menos en ES y EN.

## Estructura de archivos
- Backend: mensajes de validación y texto del sistema en `lang/{locale}/` (Laravel).
- Frontend: textos en `lang/{locale}.json` consumidos por `useI18n()`.

## Flujo para nuevas features
1. Diseña los textos en inglés (base) y español.
2. En frontend, usa `const { t } = useI18n();` y reemplaza literales por `t('Clave')`.
3. Añade claves en `lang/en.json` y `lang/es.json`.
4. En backend (si aplica), añade traducciones a `lang/en/` y `lang/es/` para validación/mensajes.
5. Si hay navegación o tooltips, incluye accesibilidad (sr-only, aria-label) con `t('...')`.
6. Verifica EN↔ES con el selector del header.
7. Añade tests (Pest) mínimos que validen presencia de claves críticas.

## Nomenclatura de claves
- Usa mayúsculas/minúsculas tipo frase: `"Password settings"`, `"Log in"`.
- Evita concatenaciones programáticas; crea claves completas.
- Reutiliza claves existentes antes de crear nuevas.

## Checklist de PR (obligatorio)
- [ ] Todos los literales visibles usan `t('...')`.
- [ ] Claves agregadas en `lang/en.json` y `lang/es.json`.
- [ ] Tooltips/sr-only/aria-label traducidos.
- [ ] Breadcrumbs y menús traducidos.
- [ ] Mensajes backend (si aplica) en `lang/en/` y `lang/es/`.
- [ ] Screens de Auth y Settings sin literales sin traducir.
- [ ] Pruebas rápidas en ambos idiomas realizadas.

## Buenas prácticas
- Mantén las claves ordenadas alfabéticamente.
- Evita duplicados; si encuentras uno, consolida.
- Usa títulos y descripciones coherentes en `Head` y `Heading`.

## Ejemplo mínimo (React)
```tsx
import { useI18n } from '@/lib/i18n/I18nProvider';

export default function Example() {
  const { t } = useI18n();
  return <button aria-label={t('Save')}>{t('Save')}</button>;
}
```

## Ejemplo de actualización de diccionarios
- `lang/en.json`:
```json
{
  "Save": "Save",
  "Profile": "Profile"
}
```
- `lang/es.json`:
```json
{
  "Save": "Guardar",
  "Profile": "Perfil"
}
```

## Validación Laravel (backend)
Ubica mensajes en `lang/{locale}/validation.php` y otros archivos según corresponda. Evita hardcodear cadenas en controladores.
