import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Package, Settings as SettingsIcon } from 'lucide-react';
import { useState } from 'react';
import type { PackageSettings } from '@/types/settings';
import { SettingsForm } from '@/components/settings/SettingsForm';

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
                <Head title="Package Settings" />
                <div className="space-y-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Package Settings</h1>
                        <p className="text-muted-foreground">
                            Configure application-wide package settings
                        </p>
                    </div>

                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Package className="h-12 w-12 text-muted-foreground mb-4" />
                            <p className="text-lg font-medium">No configurable packages found</p>
                            <p className="text-sm text-muted-foreground">
                                Install packages with settings to configure them here
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Package Settings" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Package Settings</h1>
                    <p className="text-muted-foreground">
                        Configure application-wide package settings
                    </p>
                </div>

                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList>
                        {packageList.map((pkg) => (
                            <TabsTrigger key={pkg.name} value={pkg.name}>
                                <Package className="h-4 w-4 mr-2" />
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
                                    <CardDescription>
                                        Configure settings for {pkg.name} package
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <SettingsForm pkg={pkg} />
                                </CardContent>
                            </Card>
                        </TabsContent>
                    ))}
                </Tabs>
            </div>
        </AppLayout>
    );
}
