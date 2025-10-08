import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | string | null;
    isActive?: boolean;
    permission?: string | null;
    order?: number;
    active?: boolean;
}

export interface PackageMenus {
    platform: NavItem[];
    admin: NavItem[];
    operation: NavItem[];
}

export interface PackageTheme {
    name: string;
    hasCustomTheme: boolean;
    mode: 'light' | 'dark' | 'auto';
    colors: {
        light: Record<string, string>;
        dark: Record<string, string>;
    };
}

export interface PackageSettings {
    name: string;
    schema: any;
    settings: Record<string, any>;
    hasSettings: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    packages?: {
        menus: PackageMenus;
    };
    themes?: Record<string, PackageTheme>;
    packageSettings?: Record<string, PackageSettings>;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
