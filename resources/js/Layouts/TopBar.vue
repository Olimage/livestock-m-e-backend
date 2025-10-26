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
          <button class="btn btn-link text-white dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-4"></i>
            <span class="user-name d-none d-md-inline">{{ userName }}</span>
            <span class="badge bg-danger" v-if="notificationCount > 0">{{ notificationCount }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">
              <div class="d-flex align-items-center">
                <i class="bi bi-person-circle fs-3 me-2"></i>
                <div>
                  <div class="fw-bold">{{ userName }}</div>
                  <small class="text-muted">{{ userEmail }}</small>
                </div>
              </div>
            </li>
            <li><hr class="dropdown-divider"></li>
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
  computed: {
    // Get user from Inertia shared props
    user() {
      return this.$page.props.auth?.user || this.$page.props.user || {}
    },
    userName() {
      return this.user.name || 'Guest'
    },
    userEmail() {
      return this.user.email || ''
    },
    notificationCount() {
      // You can replace this with actual notification count from props
      return this.$page.props.notificationCount || 0
    }
  },
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
  padding: 0.25rem 0.5rem;
}

.btn-link:hover {
  color: #fff !important;
  opacity: 0.8;
}

.bg-dark {
  background-color: rgb(11 109 23) !important;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 500;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-header {
  padding: 0.75rem 1rem;
  background-color: #f8f9fa;
}

.dropdown-header .fw-bold {
  font-size: 0.95rem;
}

.dropdown-header small {
  font-size: 0.8rem;
}

.badge {
  font-size: 0.7rem;
  padding: 0.25em 0.5em;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .user-name {
    display: none !important;
  }
}
</style>