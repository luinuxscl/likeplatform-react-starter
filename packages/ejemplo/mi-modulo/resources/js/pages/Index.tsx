import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Package, ListChecks, Activity } from 'lucide-react';

interface Props {
    title: string;
    description: string;
    stats: {
        total_items: number;
        active_items: number;
        pending_items: number;
    };
}

export default function Index({ title, description, stats }: Props) {
    return (
        <AppLayout>
            <Head title={title} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                    <p className="text-muted-foreground">{description}</p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Items</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_items}</div>
                            <p className="text-xs text-muted-foreground">Items registrados en el sistema</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Items Activos</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.active_items}</div>
                            <p className="text-xs text-muted-foreground">Items en estado activo</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Items Pendientes</CardTitle>
                            <ListChecks className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pending_items}</div>
                            <p className="text-xs text-muted-foreground">Items pendientes de revisión</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Bienvenido a Mi Módulo</CardTitle>
                        <CardDescription>
                            Este es un módulo de ejemplo que demuestra cómo crear packages personalizados
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <p className="text-sm text-muted-foreground">
                                Este módulo se ha integrado automáticamente con el sistema de menús del starterkit.
                                Puedes ver los items del menú en el sidebar bajo la sección "Operación".
                            </p>
                            <div className="rounded-lg border bg-muted/50 p-4">
                                <h3 className="font-semibold mb-2">Características incluidas:</h3>
                                <ul className="list-disc list-inside space-y-1 text-sm text-muted-foreground">
                                    <li>Auto-discovery de menús</li>
                                    <li>Registro automático de rutas</li>
                                    <li>Gestión de permisos con Spatie</li>
                                    <li>Componentes React integrados</li>
                                    <li>Migraciones y seeders</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
