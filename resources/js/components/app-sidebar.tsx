import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, LayoutDashboard, Users, BadgeCheck, KeyRound, Settings2 } from 'lucide-react';
import admin from '@/routes/admin';
import AppLogo from './app-logo';
import { useI18n } from '@/lib/i18n/I18nProvider';

export function AppSidebar() {
    const { t } = useI18n();
    const page = usePage();

    const platformItems: NavItem[] = [
        {
            title: t('Dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Mostrar links de Administración solo si el usuario tiene rol admin
    const roles: string[] | undefined = (page.props as any)?.auth?.roles;
    const adminItems: NavItem[] = [];
    if (Array.isArray(roles) && roles.includes('admin')) {
        const perms: string[] | undefined = (page.props as any)?.auth?.permissions;
        adminItems.push(
            {
                title: t('Dashboard admin'),
                href: '/admin/dashboard',
                icon: LayoutDashboard,
            },
            {
                title: t('Usuarios'),
                href: admin.users.index(),
                icon: Users,
            },
            {
                title: t('Roles'),
                href: admin.roles.index(),
                icon: BadgeCheck,
            },
            {
                title: t('Permisos'),
                href: admin.permissions.index(),
                icon: KeyRound,
            },
        );
        if (Array.isArray(perms) && perms.includes('options.view')) {
            adminItems.push({
                title: t('Opciones'),
                href: '/admin/options',
                icon: Settings2,
            });
        }
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
                <NavMain items={platformItems} label={t('Plataforma')} />
                {adminItems.length > 0 && (
                    <NavMain items={adminItems} label={t('Administración')} />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
