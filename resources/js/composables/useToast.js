// useToast.js
import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const show = (message, type = 'info', duration = 5000) => {
    const id = toastId++
    const toast = { id, message, type, duration, visible: true }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => remove(id), duration)
    }
    return id
  }

  const remove = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const success = (message, duration) => show(message, 'success', duration)
  const error = (message, duration) => show(message, 'error', duration)
  const warning = (message, duration) => show(message, 'warning', duration)
  const info = (message, duration) => show(message, 'info', duration)

  return { toasts, show, remove, success, error, warning, info }
}
