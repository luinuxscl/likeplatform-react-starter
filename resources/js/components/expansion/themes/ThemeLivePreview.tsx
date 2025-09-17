import React from 'react'

export default function ThemeLivePreview() {
  return (
    <div className="space-y-3">
      <h3 className="text-sm font-semibold">Live preview</h3>
      <div className="flex flex-wrap items-center gap-3">
        <button
          type="button"
          className="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium"
          style={{ backgroundColor: 'var(--primary)', color: 'var(--primary-foreground)' }}
        >
          Primary Button
        </button>
        <button
          type="button"
          className="inline-flex items-center rounded-md border px-3 py-2 text-sm"
          style={{ borderColor: 'var(--ring)', color: 'var(--foreground)' }}
        >
          Outline Button
        </button>
        <span className="inline-flex h-6 items-center rounded-md border px-2 text-xs" style={{ borderColor: 'var(--border)' }}>
          Badge
        </span>
      </div>
    </div>
  )
}
