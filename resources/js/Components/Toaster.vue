<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const toasts = ref([])
let uid = 0

const addToast = (detail) => {
  const id = ++uid
  const toast = {
    id,
    message: detail.message || '',
    payload: detail.payload || null,
    variant: detail.variant || 'info',
    timeout: detail.timeout ?? 5000
  }
  toasts.value.push(toast)
  // Auto remove
  setTimeout(() => removeToast(id), toast.timeout)
}

const removeToast = (id) => {
  const i = toasts.value.findIndex(t => t.id === id)
  if (i !== -1) toasts.value.splice(i, 1)
}

const onNotify = (e) => {
  addToast(e.detail || {})
}

onMounted(() => {
  window.addEventListener('app:notify', onNotify)
})

onBeforeUnmount(() => {
  window.removeEventListener('app:notify', onNotify)
})
</script>

<template>
  <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div v-for="t in toasts" :key="t.id" class="toast show mb-2 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">{{ t.variant === 'success' ? 'Success' : (t.variant === 'danger' ? 'Error' : 'Notice') }}</strong>
        <small class="text-muted">now</small>
        <button type="button" class="btn-close ms-2 mb-1" @click="removeToast(t.id)"></button>
      </div>
      <div class="toast-body">
        <div v-if="t.payload">{{ t.message }} — <small>{{ JSON.stringify(t.payload) }}</small></div>
        <div v-else>{{ t.message }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.toast { min-width: 220px; }
</style>
