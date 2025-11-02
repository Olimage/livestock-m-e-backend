<script setup>
import { Head, Link } from '@inertiajs/vue3'

import BeLayout from '../../Layouts/BeLayout.vue'
import Statistics from '../../Components/AdminDashboardStatistics.vue'

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
</script>

<template>
  <BeLayout>
     <Head title="FMLD :: Dahboard" />

    <div class="row">
      <div class="col-lg-12">

        <h5 class="mt-4 fw-400">Admin Dashboard</h5>
        <hr>
      </div>

    </div>

    <div class="clearfix"></div>


    <Statistics  :stats="stats" />
  </BeLayout>
</template>