import * as React from "react"
import { cn } from "@/lib/utils"

export type BadgeProps = React.HTMLAttributes<HTMLSpanElement> & {
  variant?: "default" | "secondary" | "destructive" | "outline"
}

export function Badge({ className, variant = "default", ...props }: BadgeProps) {
  return (
    <span
      data-slot="badge"
      className={cn(
        "inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium",
        // base theme tokens
        "border-border text-foreground",
        variant === "default" && "bg-primary text-primary-foreground border-transparent",
        variant === "secondary" && "bg-muted text-muted-foreground",
        variant === "destructive" && "bg-destructive text-destructive-foreground border-transparent",
        variant === "outline" && "bg-card",
        className,
      )}
      {...props}
    />
  )
}
