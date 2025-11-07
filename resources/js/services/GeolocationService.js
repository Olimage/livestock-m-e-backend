class GeolocationService {
    constructor() {
        this.watchId = null;
        this.listeners = new Set();
        this.currentPosition = null;
        this.error = null;
    }

    // Start watching location changes
    startTracking(options = {}) {
        if (!navigator.geolocation) {
            this.error = new Error('Geolocation is not supported by this browser.');
            this.notifyListeners();
            return false;
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        try {
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    };
                    this.error = null;
                    this.notifyListeners();
                },
                (error) => {
                    this.error = error;
                    this.notifyListeners();
                },
                { ...defaultOptions, ...options }
            );
            return true;
        } catch (error) {
            this.error = error;
            this.notifyListeners();
            return false;
        }
    }

    // Stop watching location changes
    stopTracking() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            return true;
        }
        return false;
    }

    // Get current position once
    async getCurrentPosition(options = {}) {
        if (!navigator.geolocation) {
            throw new Error('Geolocation is not supported by this browser.');
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    });
                },
                reject,
                { ...defaultOptions, ...options }
            );
        });
    }

    // Subscribe to location updates
    subscribe(callback) {
        this.listeners.add(callback);
        // Immediately notify with current state
        if (this.currentPosition || this.error) {
            callback({
                position: this.currentPosition,
                error: this.error
            });
        }
        return () => this.unsubscribe(callback);
    }

    // Unsubscribe from location updates
    unsubscribe(callback) {
        this.listeners.delete(callback);
    }

    // Notify all listeners of changes
    notifyListeners() {
        this.listeners.forEach(callback => {
            callback({
                position: this.currentPosition,
                error: this.error
            });
        });
    }
}

// Create singleton instance
const geolocationService = new GeolocationService();
export default geolocationService;