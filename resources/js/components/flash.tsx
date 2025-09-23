import { Alert, AlertDescription } from '@/components/ui/alert'
import { usePage } from '@inertiajs/react'

export default function Flash() {
    const page = usePage()
    const success = (page.props as any)?.flash?.success
    const error = (page.props as any)?.flash?.error

    if (!success && !error) return null

    return (
        <div className="mx-4 mb-3 space-y-2">
            {success && (
                <Alert variant="default">
                    <AlertDescription>{success}</AlertDescription>
                </Alert>
            )}
            {error && (
                <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}
        </div>
    )
}
