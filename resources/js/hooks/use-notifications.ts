import { useCallback, useEffect, useState } from 'react'
import { type Notification, type NotificationResponse } from '@/types'
import axios from 'axios'

export function useNotifications() {
  const [notifications, setNotifications] = useState<Notification[]>([])
  const [unreadCount, setUnreadCount] = useState(0)
  const [hasMore, setHasMore] = useState(false)
  const [loading, setLoading] = useState(false)

  const fetchNotifications = useCallback(async () => {
    setLoading(true)
    try {
      const { data } = await axios.get<NotificationResponse>('/api/notifications')
      setNotifications(data.notifications)
      setUnreadCount(data.unread_count)
      setHasMore(data.has_more)
    } catch (error) {
      console.error('Error fetching notifications:', error)
    } finally {
      setLoading(false)
    }
  }, [])

  const markAsRead = useCallback(async (id: string) => {
    try {
      await axios.post(`/api/notifications/${id}/read`)
      setNotifications((prev) =>
        prev.map((n) => (n.id === id ? { ...n, read_at: new Date().toISOString() } : n))
      )
      setUnreadCount((prev) => Math.max(0, prev - 1))
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }, [])

  const markAllAsRead = useCallback(async () => {
    try {
      await axios.post('/api/notifications/read-all')
      setNotifications((prev) => prev.map((n) => ({ ...n, read_at: new Date().toISOString() })))
      setUnreadCount(0)
    } catch (error) {
      console.error('Error marking all notifications as read:', error)
    }
  }, [])

  const deleteNotification = useCallback(async (id: string) => {
    try {
      await axios.delete(`/api/notifications/${id}`)
      setNotifications((prev) => prev.filter((n) => n.id !== id))
      const notification = notifications.find((n) => n.id === id)
      if (notification && !notification.read_at) {
        setUnreadCount((prev) => Math.max(0, prev - 1))
      }
    } catch (error) {
      console.error('Error deleting notification:', error)
    }
  }, [notifications])

  const clearRead = useCallback(async () => {
    try {
      await axios.delete('/api/notifications/clear/read')
      setNotifications((prev) => prev.filter((n) => !n.read_at))
    } catch (error) {
      console.error('Error clearing read notifications:', error)
    }
  }, [])

  useEffect(() => {
    fetchNotifications()
  }, [fetchNotifications])

  return {
    notifications,
    unreadCount,
    hasMore,
    loading,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearRead,
  }
}
