import { toast as sonnerToast } from 'sonner'

export type ToastVariant = 'default' | 'destructive'

type ToastOptions = {
  title: string
  description?: string
  variant?: ToastVariant
}

export function toast({ title, description, variant = 'default' }: ToastOptions) {
  if (variant === 'destructive') {
    sonnerToast.error(title, {
      description,
    })
    return
  }

  sonnerToast.success(title, {
    description,
  })
}
