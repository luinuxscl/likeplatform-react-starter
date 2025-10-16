<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Obtener todas las notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'has_more' => $notifications->hasMorePages(),
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function clearRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->json(['success' => true]);
    }
}
