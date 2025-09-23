import { toast } from 'sonner'
import { usePage } from '@inertiajs/react'

export function useAbout() {
  const page = usePage()
  const version = (page.props as any)?.version || '0.1.0-dev'
  const app = (page.props as any)?.app || {}
  const description = app?.description || ''

  const showAbout = () => {
    toast(
      (
        <div className="flex flex-col gap-1">
          <div className="text-sm font-medium">LikePlatform React Starter</div>
          <div className="text-xs text-muted-foreground">v{version}</div>
          <div className="text-xs text-muted-foreground">{description}</div>
        </div>
      ),
      { duration: 4000 }
    )
  }

  return { showAbout, version, description }
}
