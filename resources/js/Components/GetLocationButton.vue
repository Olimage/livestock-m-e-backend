<script setup>
import { ref } from 'vue'

const latitude = ref(null)
const longitude = ref(null)
const loading = ref(false)
const error = ref(null)

const emit = defineEmits(['updated'])

const getLocation = () => {
  error.value = null
  loading.value = true
  if (!navigator.geolocation) {
    error.value = 'Geolocation not supported.'
    loading.value = false
    return
  }
  navigator.geolocation.getCurrentPosition((pos) => {
    latitude.value = pos.coords.latitude.toFixed(7)
    longitude.value = pos.coords.longitude.toFixed(7)
    emit('updated', { latitude: latitude.value, longitude: longitude.value })
    loading.value = false
  }, (e) => {
    error.value = e.message || 'Unable to get location.'
    loading.value = false
  }, { enableHighAccuracy: true, timeout: 10000 })
}
</script>

<template>
  <div class="location-capture">
    <div class="d-flex gap-2 align-items-center mb-2">
      <button type="button" class="btn btn-sm btn-outline-success" @click="getLocation" :disabled="loading">
        <span v-if="!loading"><i class="bi bi-geo-alt"></i> Get Location</span>
        <span v-else>Getting...</span>
      </button>
      <div class="small text-muted" v-if="latitude && longitude">
        Lat: <strong>{{ latitude }}</strong>, Lng: <strong>{{ longitude }}</strong>
      </div>
      <div v-if="error" class="small text-danger">{{ error }}</div>
    </div>
    <input v-if="latitude" type="hidden" :value="latitude" name="latitude" />
    <input v-if="longitude" type="hidden" :value="longitude" name="longitude" />
  </div>
</template>

<style scoped>
.location-capture button { white-space: nowrap; }
</style>
