<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import useLocationTracker from '../composables/useLocationTracker';
import L from 'leaflet';

// Use existing composable
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

const mapEl = ref(null);
let map = null;
let positionLayer = null;

onMounted(() => {
  // Initialize the map when component mounts
  map = L.map(mapEl.value, { zoomControl: true }).setView([0, 0], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Use a circle marker for current position (avoids Leaflet's marker image config)
  positionLayer = L.circleMarker([0, 0], {
    radius: 8,
    color: '#1976d2',
    fillColor: '#1976d2',
    fillOpacity: 0.9
  }).addTo(map);
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});

// Update map when currentLocation changes
watch(currentLocation, (loc) => {
  if (!loc || !map) return;
  const lat = Number(loc.latitude);
  const lon = Number(loc.longitude);
  if (Number.isFinite(lat) && Number.isFinite(lon)) {
    positionLayer.setLatLng([lat, lon]);
    // optionally set accuracy circle
    if (loc.accuracy) {
      if (positionLayer.accuracyCircle) {
        positionLayer.accuracyCircle.setLatLng([lat, lon]).setRadius(loc.accuracy);
      } else {
        positionLayer.accuracyCircle = L.circle([lat, lon], { radius: loc.accuracy, color: '#1976d2', opacity: 0.2 }).addTo(map);
      }
    }

    // center map on position (only if zoom is low or first fix)
    if (!map._centeredOnce) {
      map.setView([lat, lon], 15);
      map._centeredOnce = true;
    } else {
      map.panTo([lat, lon], { animate: true });
    }
  }
});

const formattedDistance = () => {
  const meters = getTotalDistance();
  return meters >= 1000 ? `${(meters/1000).toFixed(2)} km` : `${Math.round(meters)} m`;
};

const formatCoord = (num) => num != null ? Number(num).toFixed(6) : 'N/A';
</script>

<template>
  <div class="live-location-map card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Live Location & Map</h5>
      <div class="btn-group">
        <button @click="startTracking" class="btn btn-sm btn-success" :disabled="isTracking">Start</button>
        <button @click="stopTracking" class="btn btn-sm btn-danger" :disabled="!isTracking">Stop</button>
        <button @click="clearHistory" class="btn btn-sm btn-outline-secondary" :disabled="locationHistory.length === 0">Clear</button>
      </div>
    </div>

    <div class="card-body p-2">
      <div class="row g-2">
        <div class="col-md-6">
          <div ref="mapEl" class="map-container"></div>
        </div>

        <div class="col-md-6">
          <div class="p-2 border rounded mb-2">
            <small class="text-muted">Latitude</small>
            <div>{{ formatCoord(currentLocation?.latitude) }}</div>
          </div>
          <div class="p-2 border rounded mb-2">
            <small class="text-muted">Longitude</small>
            <div>{{ formatCoord(currentLocation?.longitude) }}</div>
          </div>
          <div class="p-2 border rounded mb-2">
            <small class="text-muted">Speed</small>
            <div>{{ currentLocation?.speed ? (Number(currentLocation.speed) * 3.6).toFixed(1) + ' km/h' : 'N/A' }}</div>
          </div>
          <div class="p-2 border rounded mb-2">
            <small class="text-muted">Accuracy</small>
            <div>{{ currentLocation?.accuracy ? Number(currentLocation.accuracy).toFixed(1) + ' m' : 'N/A' }}</div>
          </div>

          <div class="p-2 border rounded">
            <small class="text-muted">Total Distance</small>
            <div>{{ formattedDistance() }}</div>
          </div>
        </div>
      </div>

      <div v-if="error" class="mt-2 alert alert-danger">{{ error }}</div>
    </div>
  </div>
</template>

<style scoped>
.map-container {
  width: 100%;
  height: 360px;
  border-radius: 4px;
  overflow: hidden;
}

.live-location-map .card-body {
  padding: 0.75rem;
}

.p-2 { padding: 0.5rem; }
</style>
