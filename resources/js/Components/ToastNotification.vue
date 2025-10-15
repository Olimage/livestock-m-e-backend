<script setup>
import { useToast } from '@/composables/useToast'
import { computed } from 'vue'

const { toasts, remove } = useToast()

const getIcon = (type) => {
  const icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill'
  }
  return icons[type] || icons.info
}

const getColor = (type) => {
  const colors = {
    success: 'bg-success',
    error: 'bg-danger',
    warning: 'bg-warning',
    info: 'bg-info'
  }
  return colors[type] || colors.info
}
</script>

<template>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast show mb-2"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="toast-header" :class="getColor(toast.type)">
          <i :class="['bi', getIcon(toast.type), 'me-2', 'text-white']"></i>
          <strong class="me-auto text-white text-capitalize">{{ toast.type }}</strong>
          <button
            type="button"
            class="btn-close btn-close-white"
            @click="remove(toast.id)"
            aria-label="Close"
          ></button>
        </div>
        <div class="toast-body">
          {{ toast.message }}
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast {
  min-width: 300px;
  max-width: 400px;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.toast-header {
  color: white;
}

.toast-header.bg-success {
  background-color: #198754 !important;
}

.toast-header.bg-danger {
  background-color: #dc3545 !important;
}

.toast-header.bg-warning {
  background-color: #ffc107 !important;
  color: #000 !important;
}

.toast-header.bg-info {
  background-color: #0dcaf0 !important;
}

.toast-header .bi {
  font-size: 1.1rem;
}
</style>