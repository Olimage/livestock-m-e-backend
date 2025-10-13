<script setup>

// import { useRoute, onBeforeRouteUpdate } from 'vue-router'
// import { useHead } from '@vueuse/head'
import { watchEffect } from 'vue'

// const route = useRoute()

// watchEffect(() => {
//   const meta = route.meta
//   // useHead({
//   //   title: meta.title || 'Kedool',
//   //   meta: (meta.metaTags || []).map(tag => ({
//   //     ...tag
//   //   }))
//   // })
// })

import Header from './Header.vue'
import Footer from './Footer.vue'
</script>
<template>
  <main>
    <Header />
    <router-view />
    <Footer />
  </main>
</template>