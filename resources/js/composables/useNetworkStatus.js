// composables/useNetworkStatus.js
import { ref, onMounted, onUnmounted } from 'vue'
import { useToast } from './useToast'

export function useNetworkStatus() {
    const isOnline = ref(navigator.onLine)
    const toast = useToast()
    let toastId = null

    const updateNetworkStatus = () => {
        const wasOnline = isOnline.value
        isOnline.value = navigator.onLine
        
        // Show toast only when status changes
        if (!wasOnline && isOnline.value) {
            // Came back online
            if (toastId) {
                toast.remove(toastId)
                toastId = null
            }
            toast.success('Internet connection restored')
        } else if (wasOnline && !isOnline.value) {
            // Went offline
            toastId = toast.error('No internet connection. Please check your network.', 0) // 0 = no auto-remove
        }
    }

    onMounted(() => {
        window.addEventListener('online', updateNetworkStatus)
        window.addEventListener('offline', updateNetworkStatus)
    })

    onUnmounted(() => {
        window.removeEventListener('online', updateNetworkStatus)
        window.removeEventListener('offline', updateNetworkStatus)
        
        // Clean up any existing offline toast
        if (toastId) {
            toast.remove(toastId)
        }
    })

    return {
        isOnline
    }
}