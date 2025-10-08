import * as LucideIcons from 'lucide-react';
import { LucideIcon } from 'lucide-react';

/**
 * Resuelve un icono de Lucide desde su nombre como string
 * @param iconName Nombre del icono (ej: 'Package', 'Users', 'Settings')
 * @returns Componente de icono de Lucide o null si no existe
 */
export function resolveIcon(iconName: string | LucideIcon | null | undefined): LucideIcon | null {
    if (!iconName) {
        return null;
    }

    // Si ya es un componente de icono, retornarlo
    if (typeof iconName !== 'string') {
        return iconName;
    }

    // Buscar el icono en el objeto de iconos de Lucide
    const Icon = (LucideIcons as any)[iconName];

    if (Icon && typeof Icon === 'function') {
        return Icon as LucideIcon;
    }

    // Fallback: retornar null si no se encuentra
    return null;
}
