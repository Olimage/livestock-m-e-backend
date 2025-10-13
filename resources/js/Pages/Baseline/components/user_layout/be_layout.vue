<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import TopBar from './TopBar.vue'
// import NavSidebar from './NavSidebar.vue'
import Footer from './FootBe.vue'

const showOffcanvas = ref(false)

const toggleSidebar = () => {
  showOffcanvas.value = !showOffcanvas.value
}

const closeOffcanvas = () => {
  showOffcanvas.value = false
}
</script>

<template>
  <div class="container">
    <Head title="Baseline" />

    <TopBar @toggle-sidebar="toggleSidebar" />

    <div class="container-fluid">
      <div class="row">
        <!-- <NavSidebar 
          :showOffcanvas="showOffcanvas" 
          @close-offcanvas="closeOffcanvas"
        /> -->
<h1>page</h1>
        <main class="main-content">
          <!-- This is where Inertia renders the actual page content -->
          <slot />
          <!-- <Footer /> -->
        </main>
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-content {
  margin-top: 56px;
  min-height: calc(100vh - 56px);
  padding-bottom: 60px;
  position: relative;
}
</style>
