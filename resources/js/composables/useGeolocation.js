import { ref, onUnmounted } from 'vue';
import geolocationService from '../services/GeolocationService';

export function useGeolocation(options = {}) {
    const position = ref(null);
    const error = ref(null);
    const isTracking = ref(false);

    // Handle updates from the service
    const unsubscribe = geolocationService.subscribe(({ position: newPosition, error: newError }) => {
        position.value = newPosition;
        error.value = newError;
    });

    // Start tracking location
    const startTracking = () => {
        const started = geolocationService.startTracking(options);
        isTracking.value = started;
        return started;
    };

    // Stop tracking location
    const stopTracking = () => {
        const stopped = geolocationService.stopTracking();
        isTracking.value = !stopped;
        return stopped;
    };

    // Get current position once
    const getCurrentPosition = async () => {
        try {
            position.value = await geolocationService.getCurrentPosition(options);
            error.value = null;
            return position.value;
        } catch (err) {
            error.value = err;
            throw err;
        }
    };

    // Cleanup on component unmount
    onUnmounted(() => {
        unsubscribe();
        if (isTracking.value) {
            stopTracking();
        }
    });

    return {
        position,
        error,
        isTracking,
        startTracking,
        stopTracking,
        getCurrentPosition
    };
}