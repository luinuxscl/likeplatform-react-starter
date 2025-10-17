import { useEffect } from 'react'
import { usePage } from '@inertiajs/react'
import { useNotifications } from './use-notifications'
import { toast } from '@/components/ui/use-toast'
import type { Notification } from '@/types'

export function useRealtimeNotifications() {
  const { user } = (usePage().props as any).auth
  const { fetchNotifications } = useNotifications()

  useEffect(() => {
    if (!user || !window.Echo) return

    const channel = window.Echo.private(`App.Models.User.${user.id}`)

    channel.notification((notification: Notification['data']) => {
      // Mostrar toast automático
      toast({
        title: notification.title,
        description: notification.message,
        variant: notification.type === 'error' ? 'destructive' : 'default',
      })

      // Actualizar lista de notificaciones
      fetchNotifications()
    })

    return () => {
      window.Echo.leave(`App.Models.User.${user.id}`)
    }
  }, [user?.id, fetchNotifications])
}
