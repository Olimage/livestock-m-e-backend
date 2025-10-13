<template>
  <div>
    <!-- Overlay for mobile -->
    <div 
      class="offcanvas-overlay" 
      :class="{ 'show': showOffcanvas }" 
      @click="closeOffcanvas"
    ></div>

    <!-- Sidebar / Off-canvas -->
    <div :class="[
      'sidebar',
      { 'sidebar-collapsed': collapsed },
      { 'offcanvas-show': showOffcanvas }
    ]">
      <div class="sidebar-sticky">
        <!-- Sidebar header -->
        <div class="sidebar-header p-3 border-bottom">
          <h6 class="mb-0">Navigation</h6>
          <div class="d-flex gap-2">
            <!-- Desktop collapse button -->
            <button class="btn btn-sm btn-outline-secondary tog-btn d-none d-lg-block" @click="toggleCollapse">
              <i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
            </button>
            <!-- Mobile close button -->
            <button class="btn btn-sm btn-outline-secondary tog-btn d-lg-none" @click="closeOffcanvas">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <!-- Navigation items -->
        <nav class="sidebar-nav">
          <ul class="nav flex-column">
            <li class="nav-item" v-for="item in navItems" :key="item.name">
              <Link
                :href="item.path"
                class="nav-link"
                :class="{ active: isActive(item.path) }"
                @click="onNavClick"
              >
                <i :class="item.icon"></i>
                <span class="nav-text">{{ item.name }}</span>
              </Link>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'NavSidebar',
  components: {
    Link
  },
  props: {
    showOffcanvas: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close-offcanvas'],
  data() {
    return {
      collapsed: false,
      navItems: [
        { name: 'Dashboard', path: '/baseline/', icon: 'bi bi-speedometer2' },
        { name: 'New Input', path: '/baseline/new', icon: 'bi bi-plus-square' },
        { name: 'Saved Data', path: '/baseline/saved-data', icon: 'bi bi-floppy' },
      ]
    }
  },
  computed: {
    currentUrl() {
      return this.$page?.url || '/'
    }
  },
  watch: {
    showOffcanvas(newVal) {
      if (newVal) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    }
  },
  methods: {
    isActive(path) {
      return this.currentUrl === path
    },
    toggleCollapse() {
      this.collapsed = !this.collapsed
    },
    closeOffcanvas() {
      this.$emit('close-offcanvas')
    },
    onNavClick() {
      // Close off-canvas on mobile when navigation item is clicked
      if (window.innerWidth < 992) {
        this.closeOffcanvas()
      }
    }
  },
  beforeUnmount() {
    // Clean up body overflow style
    document.body.style.overflow = ''
  }
}
</script>

<style scoped>
/* Overlay for mobile */
.offcanvas-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1025;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.offcanvas-overlay.show {
  opacity: 1;
  visibility: visible;
}

/* Sidebar base styles */
.sidebar {
  width: 150px;
  min-height: calc(100vh - 56px);
  background: rgb(10, 78, 18);
  color: #fff;
  font-weight: 600;
  position: fixed;
  top: 56px;
  left: 0;
  transition: all 0.3s ease;
  z-index: 1026;
  padding-right: 0 !important;
  padding-left: 0 !important;
}

/* Desktop collapsed state */
.sidebar-collapsed {
  width: 60px;
}

/* Mobile styles */
@media (max-width: 991.98px) {
  .sidebar {
    transform: translateX(-100%);
    width: 250px;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
  }

  .sidebar.offcanvas-show {
    transform: translateX(0);
  }

  .sidebar-collapsed {
    width: 250px;
  }
}

.sidebar-sticky {
  position: sticky;
  top: 0;
  height: calc(100vh - 56px);
  overflow-x: hidden;
  overflow-y: auto;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgb(10, 78, 18);
}

.sidebar-header h6 {
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-nav {
  padding: 1rem 0;
}

.nav-link {
  color: #ffffff;
  padding: 0.75rem 1rem;
  border-left: 3px solid transparent;
  transition: all 0.3s;
  display: block;
}

.nav-link:hover {
  color: #fff;
  background: rgb(12, 95, 23);
  border-left-color: rgb(0, 248, 29);
}

.nav-link.active {
  color: #fff;
  background: rgb(12, 95, 23);
  border-left-color: rgb(0, 248, 29);
}

.nav-link i {
  margin-right: 0.5rem;
  width: 20px;
  text-align: center;
}

.sidebar-collapsed .nav-text {
  display: none;
}

.sidebar-collapsed .nav-link i {
  margin-right: 0;
}

.sidebar-collapsed .sidebar-header h6 {
  display: none;
}

/* Mobile - always show text */
@media (max-width: 991.98px) {
  .sidebar-collapsed .nav-text {
    display: inline;
  }

  .sidebar-collapsed .nav-link i {
    margin-right: 0.5rem;
  }

  .sidebar-collapsed .sidebar-header h6 {
    display: block;
  }
}

.tog-btn {
  border-color: #fff;
  color: #fff;
}
</style>