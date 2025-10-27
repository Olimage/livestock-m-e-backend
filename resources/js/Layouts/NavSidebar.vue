<template>
  <div>
    <!-- Overlay for mobile -->
    <div class="offcanvas-overlay" :class="{ 'show': showOffcanvas }" @click="closeOffcanvas"></div>

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
            <template v-for="item in menuItems" :key="item.name">
              <!-- Items with submenus -->
              <li class="nav-item" v-if="item.submenu && item.submenu.length > 0">
                <a href="#" 
                   class="nav-link" 
                   :class="{ 
                     'active': isActiveParent(item),
                     'has-submenu': true 
                   }"
                   @click.prevent="toggleSubmenu(item.name)">
                  <i :class="item.icon"></i>
                  <span class="nav-text">{{ item.name }}</span>
                  <i class="bi submenu-arrow" 
                     :class="isSubmenuOpen(item.name) ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                </a>
                
                <!-- Submenu -->
                <transition name="submenu">
                  <ul class="submenu nav flex-column" v-show="isSubmenuOpen(item.name)">
                    <li class="nav-item" v-for="subItem in item.submenu" :key="subItem.name">
                      <Link :href="subItem.url || route(subItem.routeName)" 
                            class="nav-link submenu-link" 
                            :class="{ active: isActive(subItem.routeName) }"
                            @click="onNavClick">
                        <i :class="subItem.icon"></i>
                        <span class="nav-text">{{ subItem.name }}</span>
                      </Link>
                    </li>
                  </ul>
                </transition>
              </li>

              <!-- Regular items without submenus -->
              <li class="nav-item" v-else>
                <Link :href="item.url || route(item.routeName)" 
                      class="nav-link" 
                      :class="{ active: isActive(item.routeName) }"
                      @click="onNavClick">
                  <i :class="item.icon"></i>
                  <span class="nav-text">{{ item.name }}</span>
                </Link>
              </li>
            </template>
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
    },
    navItems: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close-offcanvas'],
  data() {
    return {
      collapsed: false,
      openSubmenus: []
    }
  },
  computed: {
    // Use props if provided, otherwise fall back to default menu
    menuItems() {
      return this.navItems.length > 0 ? this.navItems : this.defaultNavItems
    },
    // Default menu items (fallback)
    defaultNavItems() {
      return [
        { 
          name: 'Dashboard', 
          routeName: 'home', 
          icon: 'bi bi-speedometer2' 
        },
        { 
          name: 'New Input', 
          routeName: 'baseline-new', 
          icon: 'bi bi-plus-square' 
        },
        { 
          name: 'Saved Data', 
          routeName: 'baseline-saved-data', 
          icon: 'bi bi-floppy' 
        }
      ]
    },
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
    },
    collapsed(newVal) {
      // Close all submenus when sidebar collapses
      if (newVal) {
        this.openSubmenus = []
      }
    }
  },
  mounted() {
    // Auto-open submenu if current route is a submenu item
    this.menuItems.forEach(item => {
      if (item.submenu) {
        const hasActiveChild = item.submenu.some(subItem => 
          this.isActive(subItem.routeName)
        )
        if (hasActiveChild) {
          this.openSubmenus.push(item.name)
        }
      }
    })
  },
  methods: {
    isActive(routeName) {
      if (!routeName) return false
      return this.$page.props.routeName === routeName
    },
    
    isActiveParent(item) {
      if (!item.submenu) return false
      return item.submenu.some(subItem => this.isActive(subItem.routeName))
    },

    isSubmenuOpen(itemName) {
      return this.openSubmenus.includes(itemName)
    },

    toggleSubmenu(itemName) {
      // Don't toggle submenus when collapsed on desktop
      if (this.collapsed && window.innerWidth >= 992) {
        return
      }

      const index = this.openSubmenus.indexOf(itemName)
      if (index > -1) {
        this.openSubmenus.splice(index, 1)
      } else {
        this.openSubmenus.push(itemName)
      }
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
  width: 250px;
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
  display: flex;
  align-items: center;
  position: relative;
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
  flex-shrink: 0;
}

.nav-link .nav-text {
  flex: 1;
}

/* Submenu arrow */
.submenu-arrow {
  margin-left: auto;
  margin-right: 0;
  font-size: 0.75rem;
  transition: transform 0.3s ease;
}

/* Submenu styles */
.submenu {
  background: rgba(0, 0, 0, 0.2);
  padding: 0;
  margin: 0;
  list-style: none;
  overflow: hidden;
}

.submenu-link {
  padding-left: 2.5rem !important;
  font-size: 0.9rem;
  font-weight: 500;
}

.submenu-link i {
  font-size: 0.85rem;
}

/* Submenu transitions */
.submenu-enter-active,
.submenu-leave-active {
  transition: all 0.3s ease;
}

.submenu-enter-from,
.submenu-leave-to {
  max-height: 0;
  opacity: 0;
}

.submenu-enter-to,
.submenu-leave-from {
  max-height: 500px;
  opacity: 1;
}

/* Collapsed sidebar styles */
.sidebar-collapsed .nav-text {
  display: none;
}

.sidebar-collapsed .submenu-arrow {
  display: none;
}

.sidebar-collapsed .nav-link i {
  margin-right: 0;
}

.sidebar-collapsed .sidebar-header h6 {
  display: none;
}

.sidebar-collapsed .submenu {
  display: none;
}

/* Tooltip for collapsed items (optional enhancement) */
.sidebar-collapsed .nav-link {
  justify-content: center;
}

/* Mobile - always show text and submenus */
@media (max-width: 991.98px) {
  .sidebar-collapsed .nav-text {
    display: inline;
  }

  .sidebar-collapsed .submenu-arrow {
    display: inline;
  }

  .sidebar-collapsed .nav-link i {
    margin-right: 0.5rem;
  }

  .sidebar-collapsed .sidebar-header h6 {
    display: block;
  }

  .sidebar-collapsed .submenu {
    display: block;
  }

  .sidebar-collapsed .nav-link {
    justify-content: flex-start;
  }
}

.tog-btn {
  border-color: #fff;
  color: #fff;
}
</style>