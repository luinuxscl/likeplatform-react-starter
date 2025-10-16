<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Crea usuarios primero.');
            return;
        }

        foreach ($users as $user) {
            // Notificación de bienvenida
            $user->notify(new GeneralNotification(
                title: '¡Bienvenido!',
                message: 'Gracias por unirte a nuestra plataforma. Estamos emocionados de tenerte aquí.',
                type: 'success',
                actionUrl: '/dashboard',
                actionText: 'Ir al Dashboard'
            ));

            // Notificación informativa
            $user->notify(new GeneralNotification(
                title: 'Nueva funcionalidad',
                message: 'Hemos agregado un sistema de notificaciones para mantenerte informado.',
                type: 'info'
            ));

            // Notificación de advertencia
            $user->notify(new GeneralNotification(
                title: 'Actualización pendiente',
                message: 'Por favor, actualiza tu información de perfil para mejorar tu experiencia.',
                type: 'warning',
                actionUrl: '/settings/profile',
                actionText: 'Actualizar perfil'
            ));
        }

        $this->command->info('Notificaciones de ejemplo creadas exitosamente.');
    }
}
