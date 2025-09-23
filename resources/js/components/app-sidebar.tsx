import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Shield } from 'lucide-react';
import admin from '@/routes/admin';
import AppLogo from './app-logo';
import { useI18n } from '@/lib/i18n/I18nProvider';

export function AppSidebar() {
    const { t } = useI18n();
    const page = usePage();

    const mainNavItems: NavItem[] = [
        {
            title: t('Dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Mostrar links de Administración solo si el usuario tiene rol admin
    const roles: string[] | undefined = (page.props as any)?.auth?.roles;
    if (Array.isArray(roles) && roles.includes('admin')) {
        mainNavItems.push(
            {
                title: t('Usuarios'),
                href: admin.users.index(),
                icon: Shield,
            },
            {
                title: t('Roles'),
                href: admin.roles.index(),
                icon: Shield,
            },
            {
                title: t('Permisos'),
                href: admin.permissions.index(),
                icon: Shield,
            },
        );
    }

    const footerNavItems: NavItem[] = [
        {
            title: t('Repository'),
            href: 'https://github.com/laravel/react-starter-kit',
            icon: Folder,
        },
        {
            title: t('Documentation'),
            href: 'https://laravel.com/docs/starter-kits#react',
            icon: BookOpen,
        },
    ];
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
