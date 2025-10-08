/**
 * Re-export del hook useTheme desde ThemeContext
 * 
 * Esto permite importar el hook directamente:
 * import { useTheme } from '@/hooks/useTheme'
 * 
 * En lugar de:
 * import { useTheme } from '@/contexts/ThemeContext'
 */

export { useTheme } from '@/contexts/ThemeContext';
