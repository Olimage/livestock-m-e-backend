<script setup>
import { Head, Link } from '@inertiajs/vue3'

import BeLayout from '../../Layouts/BeLayout.vue'
import Statistics from '../../Components/AdminDashboardStatistics.vue'
import { useGeolocation } from '../../composables/useGeolocation';

import LocationTracker from '../../Components/LocationTracker.vue';

import LiveLocationMap  from '@/Components/LiveLocationMap.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            recordsSaved: 0,
            totalUsers: 0,
            dataPendingSync: 0
        })
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


    <Statistics  :stats="stats" />


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