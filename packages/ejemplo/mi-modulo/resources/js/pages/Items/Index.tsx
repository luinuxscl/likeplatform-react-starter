import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

interface Item {
    id: number;
    name: string;
    description: string | null;
    status: 'active' | 'inactive' | 'pending';
    created_at: string;
    updated_at: string;
}

interface Props {
    items: {
        data: Item[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function ItemsIndex({ items }: Props) {
    const getStatusBadge = (status: string) => {
        const styles = {
            active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
            pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        };

        return (
            <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${styles[status as keyof typeof styles]}`}>
                {status}
            </span>
        );
    };

    return (
        <AppLayout>
            <Head title="Gestión de Items" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Gestión de Items</h1>
                        <p className="text-muted-foreground">Administra los items del módulo</p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Nuevo Item
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Items</CardTitle>
                        <CardDescription>
                            Total de {items.total} items registrados
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {items.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-muted-foreground">No hay items registrados</p>
                                <Button className="mt-4" variant="outline">
                                    <Plus className="mr-2 h-4 w-4" />
                                    Crear primer item
                                </Button>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {items.data.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center justify-between rounded-lg border p-4 hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="space-y-1">
                                            <h3 className="font-semibold">{item.name}</h3>
                                            {item.description && (
                                                <p className="text-sm text-muted-foreground">{item.description}</p>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-4">
                                            {getStatusBadge(item.status)}
                                            <Button variant="outline" size="sm">
                                                Ver detalles
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
