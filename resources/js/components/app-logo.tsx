import AppLogoIcon from './app-logo-icon';
import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const page = usePage();
    const app = (page.props as any)?.app || {};
    const appName: string = app.name ?? 'Laravel Starter Kit';
    const logoUrl: string | undefined = app.logo_url ?? undefined;

    return (
        <>
            {logoUrl ? (
                <img
                    src={logoUrl}
                    alt={appName}
                    className="aspect-square size-8 rounded-md object-cover"
                />
            ) : (
                <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                </div>
            )}
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">{appName}</span>
            </div>
        </>
    );
}
