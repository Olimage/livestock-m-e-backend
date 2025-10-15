<script setup>
import { usePage } from '@inertiajs/vue3'
import { watch, onMounted, onUnmounted } from 'vue'
import { useToast } from '@/composables/useToast'
import ToastNotification from '@/Components/ToastNotification.vue'

const page = usePage()
const toast = useToast()

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toast.success(flash.success)
  }
  if (flash?.error) {
    toast.error(flash.error)
  }
  if (flash?.warning) {
    toast.warning(flash.warning)
  }
  if (flash?.info) {
    toast.info(flash.info)
  }
}, { deep: true, immediate: true })

// Watch for validation errors
watch(() => page.props.errors, (errors) => {
  if (errors && Object.keys(errors).length > 0) {
    const firstError = Object.values(errors)[0]
    const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
    toast.error(errorMessage)
  }
}, { deep: true })


// Network status monitoring
let offlineToastId = null

const updateNetworkStatus = () => {
  if (!navigator.onLine) {
    // Went offline
    if (!offlineToastId) {
      offlineToastId = toast.error('No internet connection. Please check your network.', 0)
    }
  } else {
    // Came back online
    if (offlineToastId) {
      toast.remove(offlineToastId)
      offlineToastId = null
      toast.success('Internet connection restored')
    }
  }
}

onMounted(() => {
  window.addEventListener('online', updateNetworkStatus)
  window.addEventListener('offline', updateNetworkStatus)
  
  // Check initial network status
  if (!navigator.onLine) {
    offlineToastId = toast.error('No internet connection. Please check your network.', 0)
  }
})

onUnmounted(() => {
  window.removeEventListener('online', updateNetworkStatus)
  window.removeEventListener('offline', updateNetworkStatus)
  
  if (offlineToastId) {
    toast.remove(offlineToastId)
  }
})
</script>


<template>
    <ToastNotification />
  </template>