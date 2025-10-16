import { Bell, Check, CheckCheck, Trash2, X } from 'lucide-react'
import { useState } from 'react'
import { useNotifications } from '@/hooks/use-notifications'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { cn } from '@/lib/utils'
import { formatDistanceToNow } from '@/lib/date-utils'
import { router } from '@inertiajs/react'

export function NotificationCenter() {
  const {
    notifications,
    unreadCount,
    loading,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearRead,
  } = useNotifications()
  const [open, setOpen] = useState(false)

  const handleNotificationClick = (notification: (typeof notifications)[0]) => {
    if (!notification.read_at) {
      markAsRead(notification.id)
    }
    if (notification.data.action_url) {
      setOpen(false)
      router.visit(notification.data.action_url)
    }
  }

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'success':
        return '✓'
      case 'error':
        return '✕'
      case 'warning':
        return '⚠'
      default:
        return 'ℹ'
    }
  }

  const getNotificationColor = (type: string) => {
    switch (type) {
      case 'success':
        return 'text-emerald-600 dark:text-emerald-400'
      case 'error':
        return 'text-red-600 dark:text-red-400'
      case 'warning':
        return 'text-amber-600 dark:text-amber-400'
      default:
        return 'text-blue-600 dark:text-blue-400'
    }
  }

  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="icon" className="relative">
          <Bell className="size-5" />
          {unreadCount > 0 && (
            <span className="absolute right-1 top-1 flex size-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">
              {unreadCount > 9 ? '9+' : unreadCount}
            </span>
          )}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[380px] max-w-[calc(100vw-2rem)]">
        <div className="flex items-center justify-between px-3 py-2">
          <h3 className="text-sm font-semibold">Notificaciones</h3>
          {notifications.length > 0 && (
            <div className="flex gap-1">
              {unreadCount > 0 && (
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => markAllAsRead()}
                  className="h-7 px-2 text-xs"
                >
                  <CheckCheck className="mr-1 size-3" />
                  Marcar todas
                </Button>
              )}
              <Button
                variant="ghost"
                size="sm"
                onClick={() => clearRead()}
                className="h-7 px-2 text-xs"
              >
                <Trash2 className="mr-1 size-3" />
                Limpiar
              </Button>
            </div>
          )}
        </div>
        <DropdownMenuSeparator />
        <div className="max-h-[400px] overflow-y-auto">
          {loading && notifications.length === 0 ? (
            <div className="px-3 py-8 text-center text-sm text-muted-foreground">Cargando...</div>
          ) : notifications.length === 0 ? (
            <div className="px-3 py-8 text-center text-sm text-muted-foreground">
              No hay notificaciones
            </div>
          ) : (
            notifications.map((notification) => (
              <DropdownMenuItem
                key={notification.id}
                className={cn(
                  'group relative cursor-pointer px-3 py-3 focus:bg-accent',
                  !notification.read_at && 'bg-blue-50/50 dark:bg-blue-950/20'
                )}
                onClick={() => handleNotificationClick(notification)}
              >
                <div className="flex w-full gap-3">
                  <div
                    className={cn(
                      'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-lg',
                      getNotificationColor(notification.data.type)
                    )}
                  >
                    {getNotificationIcon(notification.data.type)}
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-2">
                      <p className="text-sm font-medium leading-tight">{notification.data.title}</p>
                      {!notification.read_at && (
                        <div className="mt-1 size-2 shrink-0 rounded-full bg-blue-600" />
                      )}
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground line-clamp-2">
                      {notification.data.message}
                    </p>
                    <div className="mt-1.5 flex items-center justify-between gap-2">
                      <p className="text-xs text-muted-foreground">
                        {formatDistanceToNow(notification.created_at)}
                      </p>
                      <div className="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        {!notification.read_at && (
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={(e) => {
                              e.stopPropagation()
                              markAsRead(notification.id)
                            }}
                            className="h-6 px-2 text-xs"
                          >
                            <Check className="size-3" />
                          </Button>
                        )}
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={(e) => {
                            e.stopPropagation()
                            deleteNotification(notification.id)
                          }}
                          className="h-6 px-2 text-xs"
                        >
                          <X className="size-3" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </DropdownMenuItem>
            ))
          )}
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
