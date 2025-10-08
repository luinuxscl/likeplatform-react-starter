/**
 * Theme types for package customization system
 */

export interface ThemeColors {
    background: string;
    foreground: string;
    card: string;
    'card-foreground': string;
    popover: string;
    'popover-foreground': string;
    primary: string;
    'primary-foreground': string;
    secondary: string;
    'secondary-foreground': string;
    muted: string;
    'muted-foreground': string;
    accent: string;
    'accent-foreground': string;
    destructive: string;
    'destructive-foreground': string;
    border: string;
    input: string;
    ring: string;
    [key: string]: string;
}

export interface Theme {
    name: string;
    colors: {
        light: ThemeColors;
        dark: ThemeColors;
    };
    typography?: {
        'font-family'?: string;
        'font-size-base'?: string;
        [key: string]: string | undefined;
    };
    spacing?: {
        radius?: string;
        [key: string]: string | undefined;
    };
}

export interface PackageTheme {
    name: string;
    hasCustomTheme: boolean;
    mode: 'light' | 'dark' | 'auto';
    colors: {
        light: ThemeColors;
        dark: ThemeColors;
    };
}

export type ThemeMode = 'light' | 'dark';

export interface ThemeContextValue {
    currentPackage: string | null;
    currentTheme: PackageTheme | null;
    mode: ThemeMode;
    setMode: (mode: ThemeMode) => void;
    themes: Record<string, PackageTheme>;
}
