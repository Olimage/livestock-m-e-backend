# Real-time Geolocation Service

This service provides real-time device location tracking using the browser's Geolocation API.

## Features

- Real-time location tracking with high accuracy
- One-time location queries
- Error handling for permission denials and unavailable service
- Support for multiple simultaneous subscribers
- Configurable tracking options (accuracy, timeout, etc.)

## Usage

### In Vue Components

```vue
<script setup>
import { useGeolocation } from '../composables/useGeolocation';

const { 
    position,    // Reactive ref containing current position
    error,      // Reactive ref containing any errors
    isTracking, // Reactive ref indicating if tracking is active
    startTracking,
    stopTracking,
    getCurrentPosition
} = useGeolocation({
    enableHighAccuracy: true,  // Optional config
    timeout: 5000,
    maximumAge: 0
});

// Start tracking location changes
const start = () => {
    startTracking();
};

// Stop tracking
const stop = () => {
    stopTracking();
};

// Get position once
const getPosition = async () => {
    try {
        const pos = await getCurrentPosition();
        console.log('Current position:', pos);
    } catch (err) {
        console.error('Error getting position:', err);
    }
};
</script>

<template>
    <div>
        <div v-if="position">
            Latitude: {{ position.latitude }}<br>
            Longitude: {{ position.longitude }}<br>
            Accuracy: {{ position.accuracy }}m<br>
            Updated: {{ new Date(position.timestamp).toLocaleString() }}
        </div>
        
        <div v-if="error" class="error">
            {{ error.message }}
        </div>

        <button @click="start" :disabled="isTracking">
            Start Tracking
        </button>
        <button @click="stop" :disabled="!isTracking">
            Stop Tracking
        </button>
        <button @click="getPosition">
            Get Current Position
        </button>
    </div>
</template>
```

### Direct Service Usage

```javascript
import geolocationService from '../services/GeolocationService';

// Subscribe to updates
const unsubscribe = geolocationService.subscribe(({ position, error }) => {
    if (position) {
        console.log('Position updated:', position);
    }
    if (error) {
        console.error('Error:', error);
    }
});

// Start tracking
geolocationService.startTracking({
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 0
});

// Stop tracking
geolocationService.stopTracking();

// Cleanup
unsubscribe();

// Get position once
geolocationService.getCurrentPosition()
    .then(position => console.log('Current position:', position))
    .catch(error => console.error('Error:', error));
```

## Configuration Options

The tracking and position functions accept these options:

- `enableHighAccuracy`: Boolean - Use GPS if available (default: true)
- `timeout`: Number - How long to wait for position (default: 5000ms)
- `maximumAge`: Number - Accept cached positions this old (default: 0)

## Error Handling

The service handles these common scenarios:

- Geolocation not supported by browser
- User denies permission
- Position unavailable
- Timeout exceeded
- GPS/location hardware errors

## Browser Support

Requires a browser that supports the Geolocation API. Check support with:

```javascript
if ('geolocation' in navigator) {
    // Geolocation is available
}
```