<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import TopBar from './TopBar.vue'
import NavSidebar from './NavSidebar.vue'
import Footer from './FootBe.vue'
import Includes from './Includes.vue'
import SocketListener from '@/Components/SocketListener.vue'
import Toaster from '@/Components/Toaster.vue'
import { usePage } from '@inertiajs/vue3'

const showOffcanvas = ref(false)

const toggleSidebar = () => {
  showOffcanvas.value = !showOffcanvas.value
}

const closeOffcanvas = () => {
  showOffcanvas.value = false
}

const page = usePage()
const currentUserId = page.props.auth?.user?.id || page.props.user?.id || null
</script>

<template>
  <div>
    <Includes />
    <Head title="Baseline" />

    <TopBar @toggle-sidebar="toggleSidebar" />

    <NavSidebar 

      :nav-items="$page.props.navigation"
      
      :showOffcanvas="showOffcanvas" 
      @close-offcanvas="closeOffcanvas"
    />

    <main class="main-content">
      <!-- This is where Inertia renders the actual page content -->
      <slot />
      <!-- Global websocket listener + toasts -->
      <!-- <SocketListener :userId="currentUserId" /> -->
      <Toaster />
      <!-- <Footer /> -->
    </main>
  </div>
</template>

<style scoped>
.main-content {
  margin-top: 56px;
  margin-left: 16em;
  min-height: calc(100vh - 56px);
  padding: 20px;
  padding-bottom: 60px;
  transition: margin-left 0.3s ease;
}

/* Adjust main content when sidebar is collapsed */
@media (min-width: 992px) {
  .main-content {
     margin-left: 16em;
  }
}

/* Full width on mobile */
@media (max-width: 991.98px) {
  .main-content {
    margin-left: 0;
  }
}
</style>