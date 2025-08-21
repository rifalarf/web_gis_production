// resources/js/composables/useLayout.ts
import { ref } from 'vue'

export type AppLayoutKind = 'header' | 'sidebar'
const KEY = 'layout'

/* read immediately (SSR-safe) */
const stored = typeof window !== 'undefined'
  ? (localStorage.getItem(KEY) as AppLayoutKind | null)
  : null

export const layout = ref<AppLayoutKind>(stored ?? 'header')

function persist(v: AppLayoutKind) {
  if (typeof window === 'undefined') return
  localStorage.setItem(KEY, v)
  /* optional cookie so SSR keeps the same chrome */
  document.cookie = `${KEY}=${v};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax`
}

export function updateLayout(v: AppLayoutKind) {
  layout.value = v
  persist(v)
}
