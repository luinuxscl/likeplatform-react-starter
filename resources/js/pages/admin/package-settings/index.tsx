import { SettingsForm } from '@/components/settings/SettingsForm';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import type { PackageSettings } from '@/types/settings';
import { Head } from '@inertiajs/react';
import { Package, Settings as SettingsIcon } from 'lucide-react';
import { useState } from 'react';

interface PackagesSettingsPageProps {
    packages: Record<string, PackageSettings>;
}

export default function PackagesSettingsPage({ packages }: PackagesSettingsPageProps) {
    const packageList = Object.values(packages);
    const [activeTab, setActiveTab] = useState(packageList[0]?.name || '');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Package Settings', href: '/admin/package-settings' },
    ];

    if (packageList.length === 0) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Packages" />
                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-6">
                    <div>
                        <h1 className="text-2xl font-semibold">Configuración de paquetes</h1>
                        <p className="text-sm text-muted-foreground">Administra paquetes, su vigencia y la relación con organizaciones.</p>
                    </div>

                    <div className="">
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center py-12">
                                <Package className="mb-4 h-12 w-12 text-muted-foreground" />
                                <p className="text-lg font-medium">No configurable packages found</p>
                                <p className="text-sm text-muted-foreground">Install packages with settings to configure them here</p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Package Settings" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-6">
                <div className="space-y-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Package Settings</h1>
                        <p className="text-muted-foreground">Configure application-wide package settings</p>
                    </div>

                    <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                        <TabsList>
                            {packageList.map((pkg) => (
                                <TabsTrigger key={pkg.name} value={pkg.name}>
                                    <Package className="mr-2 h-4 w-4" />
                                    {pkg.name}
                                </TabsTrigger>
                            ))}
                        </TabsList>

                        {packageList.map((pkg) => (
                            <TabsContent key={pkg.name} value={pkg.name} className="space-y-4">
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <SettingsIcon className="h-5 w-5" />
                                            {pkg.name} Configuration
                                        </CardTitle>
                                        <CardDescription>Configure settings for {pkg.name} package</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <SettingsForm pkg={pkg} />
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        ))}
                    </Tabs>
                </div>
            </div>
        </AppLayout>
    );
}
