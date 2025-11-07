<script setup>
import { ref, computed, watchEffect } from 'vue';
import useLocationTracker from '../composables/useLocationTracker';

const {
    currentLocation,
    locationHistory,
    error,
    isTracking,
    startTracking,
    stopTracking,
    getTotalDistance,
    clearHistory
} = useLocationTracker();

const formattedDistance = computed(() => {
    const meters = getTotalDistance();
    return meters >= 1000 
        ? `${(meters/1000).toFixed(2)} km`
        : `${Math.round(meters)} m`;
});

const formattedSpeed = computed(() => {
    if (!currentLocation.value?.speed) return 'N/A';
    const kmh = (currentLocation.value.speed * 3.6).toFixed(1);
    return `${kmh} km/h`;
});

// Format coordinates to 6 decimal places
const formatCoord = (num) => num?.toFixed(6) ?? 'N/A';
</script>

<template>
    <div class="location-tracker card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Live Location Tracker</h5>
            <div class="btn-group">
                <button 
                    @click="startTracking" 
                    class="btn btn-sm" 
                    :class="isTracking ? 'btn-success' : 'btn-outline-success'"
                    :disabled="isTracking">
                    <i class="bi bi-play-fill"></i> Start
                </button>
                <button 
                    @click="stopTracking" 
                    class="btn btn-sm btn-outline-danger"
                    :disabled="!isTracking">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>
                <button 
                    @click="clearHistory" 
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="locationHistory.length === 0">
                    <i class="bi bi-trash"></i> Clear
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Error Display -->
            <div v-if="error" class="alert alert-danger">
                {{ error }}
            </div>

            <!-- Current Location -->
            <div class="current-location mb-3">
                <h6>Current Location:</h6>
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Latitude</small>
                            <div>{{ formatCoord(currentLocation?.latitude) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Longitude</small>
                            <div>{{ formatCoord(currentLocation?.longitude) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Speed</small>
                            <div>{{ formattedSpeed }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Accuracy</small>
                            <div>{{ currentLocation?.accuracy?.toFixed(1) ?? 'N/A' }} m</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Stats -->
            <div class="tracking-stats mb-3">
                <h6>Tracking Statistics:</h6>
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Total Distance</small>
                            <div>{{ formattedDistance }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Points Recorded</small>
                            <div>{{ locationHistory.length }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Status</small>
                            <div>
                                <span 
                                    class="badge"
                                    :class="isTracking ? 'bg-success' : 'bg-secondary'"
                                >
                                    {{ isTracking ? 'Tracking' : 'Stopped' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location History -->
            <div v-if="locationHistory.length > 0" class="location-history">
                <h6>Location History:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Speed</th>
                                <th>Accuracy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(loc, index) in locationHistory.slice().reverse()" :key="index">
                                <td>{{ new Date(loc.timestamp).toLocaleTimeString() }}</td>
                                <td>{{ formatCoord(loc.latitude) }}</td>
                                <td>{{ formatCoord(loc.longitude) }}</td>
                                <td>{{ loc.speed ? (loc.speed * 3.6).toFixed(1) + ' km/h' : 'N/A' }}</td>
                                <td>{{ loc.accuracy.toFixed(1) }} m</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.location-tracker {
    margin-bottom: 1rem;
}

.table-responsive {
    max-height: 300px;
    overflow-y: auto;
}

.table {
    font-size: 0.875rem;
}

.badge {
    font-weight: normal;
}
</style>