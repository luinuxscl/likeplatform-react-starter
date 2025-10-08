import type { PackageTheme, ThemeMode } from '@/types/theme';

/**
 * Detecta el package activo desde la URL actual
 */
export function detectActivePackage(pathname: string): string | null {
    // Detectar packages conocidos desde la URL
    const packagePatterns = [
        { pattern: /^\/fcv\//, name: 'fcv-access' },
        { pattern: /^\/mi-modulo\//, name: 'mi-modulo' },
        // Agregar más patterns según sea necesario
    ];

    for (const { pattern, name } of packagePatterns) {
        if (pattern.test(pathname)) {
            return name;
        }
    }

    return null;
}

/**
 * Aplica el theme de un package al DOM
 */
export function applyPackageTheme(packageName: string | null): void {
    const body = document.body;

    // Remover data-package anterior
    body.removeAttribute('data-package');

    // Aplicar nuevo data-package si existe
    if (packageName) {
        body.setAttribute('data-package', packageName);
    }
}

/**
 * Aplica el modo de theme (light/dark) al DOM
 */
export function applyThemeMode(mode: ThemeMode): void {
    const root = document.documentElement;

    if (mode === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }

    // Guardar preferencia en localStorage
    localStorage.setItem('theme-mode', mode);
}

/**
 * Obtiene el modo de theme guardado o el preferido del sistema
 */
export function getPreferredThemeMode(): ThemeMode {
    // Primero intentar desde localStorage
    const stored = localStorage.getItem('theme-mode');
    if (stored === 'light' || stored === 'dark') {
        return stored;
    }

    // Fallback a preferencia del sistema
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }

    return 'light';
}

/**
 * Genera CSS variables desde un theme
 */
export function generateCssVariables(
    theme: PackageTheme,
    mode: ThemeMode
): Record<string, string> {
    const colors = mode === 'dark' ? theme.colors.dark : theme.colors.light;
    const variables: Record<string, string> = {};

    for (const [key, value] of Object.entries(colors)) {
        variables[`--${key}`] = value;
    }

    return variables;
}

/**
 * Aplica CSS variables al DOM
 */
export function applyCssVariables(variables: Record<string, string>, selector: string = ':root'): void {
    const element = selector === ':root' ? document.documentElement : document.querySelector(selector);

    if (!element) return;

    for (const [property, value] of Object.entries(variables)) {
        (element as HTMLElement).style.setProperty(property, value);
    }
}

/**
 * Escucha cambios en la preferencia de color del sistema
 */
export function watchSystemThemePreference(callback: (mode: ThemeMode) => void): () => void {
    if (!window.matchMedia) {
        return () => {};
    }

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    const handler = (e: MediaQueryListEvent) => {
        callback(e.matches ? 'dark' : 'light');
    };

    // Usar addEventListener si está disponible, sino usar addListener (deprecated pero compatible)
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', handler);
        return () => mediaQuery.removeEventListener('change', handler);
    } else if (mediaQuery.addListener) {
        mediaQuery.addListener(handler);
        return () => mediaQuery.removeListener(handler);
    }

    return () => {};
}
