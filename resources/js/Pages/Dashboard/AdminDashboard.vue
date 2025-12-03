<script setup>
import { Head, Link } from '@inertiajs/vue3'

import BeLayout from '../../Layouts/BeLayout.vue'
import DashboardStatistics from '../../Components/DashboardStatistics.vue'
import { useGeolocation } from '../../composables/useGeolocation';

import LocationTracker from '../../Components/LocationTracker.vue';

import LiveLocationMap  from '@/Components/LiveLocationMap.vue';

const props = defineProps({
    stats: {
        type: Array,
        default: () => []
    }
})

const { 
    position,
    error,
    isTracking,
    startTracking,
    stopTracking
} = useGeolocation();

</script>

<template>
  <BeLayout>
     <Head title="Dashboard " />

    <div class="row">
      <div class="col-lg-12">

        <h5 class="mt-4 fw-400">Admin Dashboard</h5>
        <hr>
      </div>

    </div>

    <div class="clearfix"></div>


    <DashboardStatistics  
      :stats="stats" 
      title="Admin Overview Statistics"
      subtitle="Real-time overview of system data"
      :show-new-button="true"
      new-button-route="baseline-new"
      new-button-text="New Input"
      :auto-refresh="true"
      :refresh-url="route('api.dashboard.stats')"
    />


        <!-- <div>
        <! -- Show current position - ->
        <div v-if="position">
            Latitude: {{ position.latitude }}<br>
            Longitude: {{ position.longitude }}<br>
            Accuracy: {{ position.accuracy }}m
        </div>

        <!- - Error handling - ->
        <div v-if="error" class="text-danger">
            {{ error.message }}
        </div>

        <!- - Controls - ->
        <button @click="startTracking" :disabled="isTracking">
            Start Tracking
        </button>
        <button @click="stopTracking" :disabled="!isTracking">
            Stop Tracking
        </button>
    </div> -->


    <!-- <div class="row">
      <div class="col-12">
        <LocationTracker />
        <LiveLocationMap  />
      </div>
    </div> -->


  </BeLayout>
</template>