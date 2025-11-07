import { ref, onMounted, onUnmounted } from 'vue';

export default function useLocationTracker() {
    const currentLocation = ref(null);
    const locationHistory = ref([]);
    const error = ref(null);
    const isTracking = ref(false);
    const watchId = ref(null);

    const startTracking = () => {
        if (!navigator.geolocation) {
            error.value = 'Geolocation is not supported by this browser.';
            return false;
        }

        try {
            isTracking.value = true;
            watchId.value = navigator.geolocation.watchPosition(
                (position) => {
                    const newLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        speed: position.coords.speed,
                        timestamp: new Date().toISOString()
                    };
                    
                    currentLocation.value = newLocation;
                    locationHistory.value.push(newLocation);
                    error.value = null;
                },
                (err) => {
                    error.value = err.message;
                    isTracking.value = false;
                },
                {
                    enableHighAccuracy: true, // Use GPS if available
                    timeout: 5000,
                    maximumAge: 0
                }
            );
            return true;
        } catch (e) {
            error.value = e.message;
            isTracking.value = false;
            return false;
        }
    };

    const stopTracking = () => {
        if (watchId.value) {
            navigator.geolocation.clearWatch(watchId.value);
            watchId.value = null;
            isTracking.value = false;
            return true;
        }
        return false;
    };

    // Get distance between two points in meters
    const calculateDistance = (lat1, lon1, lat2, lon2) => {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    };

    // Calculate total distance traveled
    const getTotalDistance = () => {
        if (locationHistory.value.length < 2) return 0;
        
        let total = 0;
        for (let i = 1; i < locationHistory.value.length; i++) {
            const prev = locationHistory.value[i - 1];
            const curr = locationHistory.value[i];
            total += calculateDistance(
                prev.latitude, prev.longitude,
                curr.latitude, curr.longitude
            );
        }
        return total;
    };

    // Clear tracking history
    const clearHistory = () => {
        locationHistory.value = [];
    };

    // Cleanup on component unmount
    onUnmounted(() => {
        if (isTracking.value) {
            stopTracking();
        }
    });

    return {
        currentLocation,
        locationHistory,
        error,
        isTracking,
        startTracking,
        stopTracking,
        getTotalDistance,
        clearHistory
    };
}