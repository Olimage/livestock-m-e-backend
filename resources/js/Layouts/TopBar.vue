<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <!-- Mobile menu toggle button -->
      <button 
        class="btn btn-link text-white d-lg-none me-2" 
        type="button" 
        @click="toggleSidebar"
      >
        <i class="bi bi-list fs-4"></i>
      </button>

      <!-- Brand -->
      <Link class="navbar-brand" href="/baseline/">
        FMLD Enumeration
      </Link>

      <!-- Right side items -->
      <div class="d-flex align-items-center">
        <!-- User profile -->
        <div class="dropdown">
          <button class="btn btn-link text-white dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
            <span class="badge bg-danger">3</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'TopBar',
  components: {
    Link
  },
  emits: ['toggle-sidebar'],
  methods: {
    toggleSidebar() {
      this.$emit('toggle-sidebar')
    }
  }
}
</script>

<style scoped>
.navbar {
  z-index: 1030;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar-brand {
  font-weight: 600;
  text-decoration: none;
  color: #fff;
}

.navbar-brand:hover {
  color: #fff;
}

.dropdown-toggle::after {
  display: none;
}

.btn-link {
  text-decoration: none;
  padding: 0;
}

.btn-link:hover {
  color: #fff !important;
  opacity: 0.8;
}

.bg-dark {
    background-color: rgb(11 109 23) !important;
}
</style>