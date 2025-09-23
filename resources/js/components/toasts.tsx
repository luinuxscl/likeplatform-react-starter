import { useEffect, useMemo, useRef, useState } from 'react'
import { usePage } from '@inertiajs/react'
import { X } from 'lucide-react'

// Simple toast system driven by Inertia flash props (success/error)
// Uses Tailwind classes consistent with the starter kit and auto-dismiss behavior.

type Toast = {
  id: string
  type: 'success' | 'error'
  message: string
}

export default function Toasts() {
  const page = usePage()
  const flash = (page.props as any)?.flash || {}
  const errors = (page.props as any)?.errors || {}
  const [toasts, setToasts] = useState<Toast[]>([])
  const lastShown = useRef<string>('')

  const incoming: Toast[] = useMemo(() => {
    const arr: Toast[] = []
    if (flash.success) arr.push({ id: `success-${flash.success}`, type: 'success', message: flash.success })
    if (flash.error) arr.push({ id: `error-${flash.error}`, type: 'error', message: flash.error })
    // Validation errors: if there is any key in errors, show a single toast
    const hasErrors = errors && typeof errors === 'object' && Object.keys(errors).length > 0
    if (hasErrors) {
      arr.push({
        id: 'error-validation',
        type: 'error',
        message: 'Hay errores de validación. Corrige los campos e intenta nuevamente.',
      })
    }
    return arr
  }, [flash?.success, flash?.error, JSON.stringify(errors)])

  useEffect(() => {
    // Avoid re-adding same toast rapidly on partial reload
    incoming.forEach((t) => {
      const key = `${t.type}:${t.message}`
      if (lastShown.current !== key) {
        setToasts((prev) => {
          if (prev.find((p) => p.message === t.message && p.type === t.type)) return prev
          return [...prev, t]
        })
        lastShown.current = key
      }
    })
  }, [incoming])

  useEffect(() => {
    const timers = toasts.map((t) =>
      setTimeout(() => {
        setToasts((prev) => prev.filter((x) => x.id !== t.id))
      }, 3500)
    )
    return () => timers.forEach(clearTimeout)
  }, [toasts])

  if (toasts.length === 0) return null

  return (
    <div className="pointer-events-none fixed right-4 top-4 z-50 flex w-[360px] max-w-[94vw] flex-col gap-2 sm:right-6 sm:top-6">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={[
            'pointer-events-auto relative w-full overflow-hidden rounded-md border p-3 shadow-md transition-all',
            t.type === 'success'
              ? 'border-emerald-300/70 bg-emerald-50 text-emerald-900 dark:border-emerald-700/60 dark:bg-emerald-900/40 dark:text-emerald-50'
              : 'border-red-300/70 bg-red-50 text-red-900 dark:border-red-700/60 dark:bg-red-900/40 dark:text-red-50',
          ].join(' ')}
        >
          <button
            onClick={() => setToasts((prev) => prev.filter((x) => x.id !== t.id))}
            className="absolute right-2 top-2 inline-flex size-6 items-center justify-center rounded hover:bg-black/5 dark:hover:bg-white/10"
            aria-label="Close"
          >
            <X className="size-4" />
          </button>
          <div className="pr-8 text-sm leading-5">{t.message}</div>
        </div>
      ))}
    </div>
  )
}
