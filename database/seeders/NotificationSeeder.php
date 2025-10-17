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
            // Notificación de bienvenida (con email)
            $user->notify(new GeneralNotification(
                title: '¡Bienvenido!',
                message: 'Gracias por unirte a nuestra plataforma. Estamos emocionados de tenerte aquí.',
                type: 'success',
                actionUrl: '/dashboard',
                actionText: 'Ir al Dashboard',
                sendEmail: true // Envía email
            ));

            // Notificación informativa (solo BD)
            $user->notify(new GeneralNotification(
                title: 'Nueva funcionalidad',
                message: 'Hemos agregado un sistema de notificaciones para mantenerte informado.',
                type: 'info',
                sendEmail: false
            ));

            // Notificación de advertencia (con email)
            $user->notify(new GeneralNotification(
                title: 'Actualización pendiente',
                message: 'Por favor, actualiza tu información de perfil para mejorar tu experiencia.',
                type: 'warning',
                actionUrl: '/settings/profile',
                actionText: 'Actualizar perfil',
                sendEmail: true // Envía email
            ));
        }

        $this->command->info('Notificaciones de ejemplo creadas exitosamente.');
    }
}
