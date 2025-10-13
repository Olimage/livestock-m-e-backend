<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import TopBar from '../Pages/Baseline/components/user_layout/TopBar.vue'
import NavSidebar from '../Pages/Baseline/components/user_layout/NavSidebar.vue'
import Footer from '../Pages/Baseline/components/user_layout/FootBe.vue'

const showOffcanvas = ref(false)

const toggleSidebar = () => {
  showOffcanvas.value = !showOffcanvas.value
}

const closeOffcanvas = () => {
  showOffcanvas.value = false
}
</script>

<template>
  <div>
    <Head title="Baseline" />

    <TopBar @toggle-sidebar="toggleSidebar" />

    <NavSidebar 
      :showOffcanvas="showOffcanvas" 
      @close-offcanvas="closeOffcanvas"
    />

    <main class="main-content">
      <!-- This is where Inertia renders the actual page content -->
      <slot />
      <!-- <Footer /> -->
    </main>
  </div>
</template>

<style scoped>
.main-content {
  margin-top: 56px;
  margin-left: 150px;
  min-height: calc(100vh - 56px);
  padding: 20px;
  padding-bottom: 60px;
  transition: margin-left 0.3s ease;
}

/* Adjust main content when sidebar is collapsed */
@media (min-width: 992px) {
  .main-content {
    margin-left: 150px;
  }
}

/* Full width on mobile */
@media (max-width: 991.98px) {
  .main-content {
    margin-left: 0;
  }
}
</style>