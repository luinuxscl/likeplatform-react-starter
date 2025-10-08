import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import type { ThemeMode, PackageTheme, ThemeContextValue } from '@/types/theme';
import {
    detectActivePackage,
    applyPackageTheme,
    applyThemeMode,
    getPreferredThemeMode,
    watchSystemThemePreference,
} from '@/lib/theme-utils';

const ThemeContext = createContext<ThemeContextValue | undefined>(undefined);

interface ThemeProviderProps {
    children: ReactNode;
}

export function ThemeProvider({ children }: ThemeProviderProps) {
    const page = usePage();
    const themes = (page.props as any)?.themes || {};

    // Estado del theme
    const [mode, setModeState] = useState<ThemeMode>(() => getPreferredThemeMode());
    const [currentPackage, setCurrentPackage] = useState<string | null>(null);

    // Detectar package activo desde la URL
    useEffect(() => {
        const packageName = detectActivePackage(window.location.pathname);
        setCurrentPackage(packageName);
        applyPackageTheme(packageName);
    }, [page.url]);

    // Aplicar modo de theme
    useEffect(() => {
        applyThemeMode(mode);
    }, [mode]);

    // Escuchar cambios en preferencia del sistema (solo si el usuario no ha seleccionado manualmente)
    useEffect(() => {
        const hasManualPreference = localStorage.getItem('theme-mode') !== null;
        if (hasManualPreference) return;

        const unwatch = watchSystemThemePreference((systemMode) => {
            setModeState(systemMode);
        });

        return unwatch;
    }, []);

    // Función para cambiar el modo
    const setMode = (newMode: ThemeMode) => {
        setModeState(newMode);
        applyThemeMode(newMode);
    };

    // Obtener theme actual
    const currentTheme = currentPackage ? themes[currentPackage] : themes['starterkit'];

    const value: ThemeContextValue = {
        currentPackage,
        currentTheme: currentTheme || null,
        mode,
        setMode,
        themes,
    };

    return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
}

/**
 * Hook para usar el theme context
 */
export function useTheme(): ThemeContextValue {
    const context = useContext(ThemeContext);

    if (context === undefined) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }

    return context;
}
