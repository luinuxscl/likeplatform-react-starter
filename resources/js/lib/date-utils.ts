export function formatDistanceToNow(date: string | Date): string {
  const now = new Date()
  const past = new Date(date)
  const seconds = Math.floor((now.getTime() - past.getTime()) / 1000)

  if (seconds < 60) return 'Ahora'
  if (seconds < 3600) return `Hace ${Math.floor(seconds / 60)} min`
  if (seconds < 86400) return `Hace ${Math.floor(seconds / 3600)} h`
  if (seconds < 604800) return `Hace ${Math.floor(seconds / 86400)} d`
  if (seconds < 2592000) return `Hace ${Math.floor(seconds / 604800)} sem`
  if (seconds < 31536000) return `Hace ${Math.floor(seconds / 2592000)} mes`
  return `Hace ${Math.floor(seconds / 31536000)} año`
}
