import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren, useEffect, useMemo, useState } from 'react';
import Toasts from '@/components/toasts';
import { Toaster } from 'sonner';

export default function AppSidebarLayout({ children, breadcrumbs = [] }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const [themeMode, setThemeMode] = useState<'light' | 'dark'>(() =>
        document.documentElement.classList.contains('dark') ? 'dark' : 'light'
    )

    useEffect(() => {
        const update = () => setThemeMode(document.documentElement.classList.contains('dark') ? 'dark' : 'light')
        const observer = new MutationObserver(() => update())
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
        return () => observer.disconnect()
    }, [])

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <Toasts />
                <Toaster position="top-center" richColors closeButton theme={themeMode} />
                {children}
            </AppContent>
        </AppShell>
    );
}
