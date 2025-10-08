import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, Info, LayoutGrid, LayoutDashboard, Users, BadgeCheck, KeyRound, Settings2, ClipboardList, ActivitySquare, KeySquare, Package } from 'lucide-react';
import admin from '@/routes/admin';
import AppLogo from './app-logo';
import { AboutDialog } from '@/components/about-dialog';
import { useState } from 'react';
import { useI18n } from '@/lib/i18n/I18nProvider';

export function AppSidebar() {
    const { t } = useI18n();
    const page = usePage();
    const [aboutOpen, setAboutOpen] = useState(false);

    // Items base de plataforma
    const basePlatformItems: NavItem[] = [
        {
            title: t('Dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Items de packages desde Inertia props
    const packageMenus = (page.props as any)?.packages?.menus || { platform: [], admin: [], operation: [] };

    // Combinar items base con items de packages
    const platformItems: NavItem[] = [...basePlatformItems, ...packageMenus.platform];

    // Items de operación (solo desde packages)
    const operationItems: NavItem[] = packageMenus.operation || [];

    // Items expuestos por paquetes/extensiones vía Inertia::share (legacy)
    const extensionItems: NavItem[] = Array.isArray((page.props as any)?.extensions?.nav)
        ? ((page.props as any).extensions.nav as Array<{ title: string; href: string; icon?: any }>)
        : [];

    // Mostrar links de Administración solo si el usuario tiene rol admin
    const roles: string[] | undefined = (page.props as any)?.auth?.roles;
    const baseAdminItems: NavItem[] = [];
    if (Array.isArray(roles) && roles.includes('admin')) {
        const perms: string[] | undefined = (page.props as any)?.auth?.permissions;
        baseAdminItems.push(
            {
                title: t('Dashboard'),
                href: '/admin/dashboard',
                icon: LayoutDashboard,
            },
            {
                title: t('Users'),
                href: admin.users.index(),
                icon: Users,
            },
            {
                title: t('Roles'),
                href: admin.roles.index(),
                icon: BadgeCheck,
            },
            {
                title: t('Permissions'),
                href: admin.permissions.index(),
                icon: KeyRound,
            },
        );
        if (Array.isArray(perms) && perms.includes('options.view')) {
            baseAdminItems.push({
                title: t('Options'),
                href: '/admin/options',
                icon: Settings2,
            });
        }

        baseAdminItems.push(
            {
                title: t('Package Settings'),
                href: '/admin/package-settings',
                icon: Package,
            },
            {
                title: t('Auditoría - Registros'),
                href: '/admin/audit/logs',
                icon: ClipboardList,
            },
            {
                title: t('Auditoría - Sesiones'),
                href: '/admin/audit/sessions',
                icon: ActivitySquare,
            },
            {
                title: t('API Keys'),
                href: '/admin/api-keys',
                icon: KeySquare,
            },
        );
    }

    // Combinar items de admin base con items de packages
    const adminItems: NavItem[] = [...baseAdminItems, ...packageMenus.admin];

    const footerNavItems: NavItem[] = [
        {
            title: t('Repository'),
            href: 'https://github.com/luinuxscl/likeplatform-react-starter',
            icon: Folder,
        },
        {
            title: t('Changelog'),
            href: '/changelog',
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
                {operationItems.length > 0 && (
                    <NavMain items={operationItems} label={t('Operación')} />
                )}
                {extensionItems.length > 0 && (
                    <NavMain items={extensionItems} label={t('Extensiones')} />
                )}
                {adminItems.length > 0 && (
                    <NavMain items={adminItems} label={t('Administración')} />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <div className="px-2 pb-2">
                    <button onClick={() => setAboutOpen(true)} className="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm text-muted-foreground hover:bg-muted">
                        <Info className="h-4 w-4" />
                        <span>{t('Acerca de')}</span>
                    </button>
                </div>
                <NavUser />
                <AboutDialog open={aboutOpen} onOpenChange={setAboutOpen} />
            </SidebarFooter>
        </Sidebar>
    );
}
