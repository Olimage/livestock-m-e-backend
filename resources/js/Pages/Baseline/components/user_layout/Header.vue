<template>
  <nav class="navbar navbar-expand-lg navbar-light sticky-top transition-navbar" :class="[
    isScrolled ? 'navbar-scrolled-glass' : 'navbar-top-transparent',
    showNavbar ? 'navbar-show' : 'navbar-hide'
  ]">
    <div class="container">
      <router-link class="navbar-brand logo-link" to="/">
        <img :src="logoSrc" alt="Logo" height="40" />
      </router-link>

      <button class="navbar-toggler" type="button" @click="toggleMobileMenu" :aria-expanded="mobileMenuOpen"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

         <!-- Desktop Menu -->
      <div class="collapse navbar-collapse d-none d-lg-block mont-font fw-400">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link class="nav-link" to="/" exact >Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about">About</router-link>
          </li>  
          <li class="nav-item">
            <router-link class="nav-link" to="/services">Services</router-link>
          </li>
          <li class="nav-item">
              <router-link class="nav-link" to="/career" >Career</router-link>
            </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/contact">Contact</router-link>
          </li>  
        </ul>
      </div>


      <div class="collapse navbar-collapse mobile-menu-glass" :class="{ 'show': mobileMenuOpen }" id="navbarToggler">
        <button class="close-menu-btn d-lg-none" @click="closeMobileMenu" aria-label="Close menu">
          <i class="bi bi-x-lg"></i>
        </button>

        <!-- Mobile Menu Logo -->
        <router-link class="navbar-brand mobile-menu-logo d-lg-none logo-link" to="/" @click="closeMobileMenu">
          <img :src="logoSrc" alt="Logo" height="50" />
        </router-link>

        <div class="mobile-menu-scroll-container mont-font fw-400">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <router-link class="nav-link" to="/" exact  @click="closeMobileMenu">Home</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/about" @click="closeMobileMenu">About</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/services" @click="closeMobileMenu">Services</router-link>
            </li>
         
            <li class="nav-item">
              <router-link class="nav-link" to="/blog" @click="closeMobileMenu">Career</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/contact" @click="closeMobileMenu">Contact</router-link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Base navbar styles */
.navbar {
  transition: all 0.3s ease;
}

.transition-navbar {
  transition: transform 0.3s ease-in-out,
    background-color 0.3s ease,
    backdrop-filter 0.3s ease;
}

.navbar-hide {
  transform: translateY(-100%);
}

.navbar-show {
  transform: translateY(0);
}

/* Logo link styling - remove underline */
.logo-link {
  text-decoration: none !important;
}

/* Mobile menu logo */
.mobile-menu-logo {
  display: block;
  margin-bottom: 2rem;
  text-align: center;
  text-decoration: none !important;
}

/* Top of page - completely transparent */
.navbar-top-transparent {
  background-color: transparent !important;
  backdrop-filter: none !important;
  box-shadow: none !important;
}

/* Scrolled state - glass effect */
.navbar-scrolled-glass {
  background-color: rgba(255, 255, 255) !important;
  /* background-color: rgba(255, 255, 255, 0.507) !important; */
  box-shadow: 6px 14px 20px 0px rgba(0, 0, 0, 0.08);
}

.navbar-scrolled-glass::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: -1;
}

/* Mobile menu styles */
@media (max-width: 991.98px) {




  .mobile-menu-glass {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.67) !important;
    z-index: 1050;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    transform: translateX(100%);
    opacity: 0;
  }

  .mobile-menu-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: -1;
  }

  .mobile-menu-glass.show {
    transform: translateX(0);
    opacity: 1;
  }

  .mobile-menu-scroll-container {
    flex: 1;
    overflow-y: auto;
    width: 100%;
    padding: 1rem 0;
  }

  .close-menu-btn {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    color: white;
    font-size: 1.8rem;
    background: transparent;
    border: none;
    cursor: pointer;
    z-index: 1051;
    transition: transform 0.2s ease;
  }

  .close-menu-btn:hover {
    transform: scale(1.1);
  }

  .close-menu-btn:focus {
    outline: none;
  }

  .navbar-nav {
    width: 100%;
    text-align: center;
  }

  .nav-item {
    /* margin: 1.5rem 0; */
    transition: transform 0.2s ease;
  }

  .nav-item:hover {
    transform: translateX(5px);
  }

  .nav-link {
    font-size: 1.4rem;
    color: white !important;
    padding: 1rem !important;
  }


}

/* Desktop styles */
@media (min-width: 992px) {

 
  
  .nav-link {
    position: relative;
    color: inherit;
    transition: color 0.3s ease;
  }

  /* Maintain transparent/glass states for desktop */
  .navbar-top-transparent .nav-link {
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  }

  .navbar-scrolled-glass .nav-link {
    /* color: #333 !important; */
  }

  /* Desktop hover effect */
  .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--pri-orange);
    transition: width 0.3s ease;
  }

  .nav-link:hover::after {
    width: 100%;
  }



  /* Hide mobile menu elements on desktop */
  .mobile-menu-logo,
  .mobile-menu-scroll-container {
    display: none;
  }
}

/* Ensure navbar stays above other content */
.navbar {
  z-index: 1030;
}
</style>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const isScrolled = ref(false)
const showNavbar = ref(true)
const mobileMenuOpen = ref(false)
let lastScrollTop = 0

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
  if (mobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
  document.body.style.overflow = ''
}

const handleScroll = () => {
  const currentScroll = window.scrollY
  isScrolled.value = currentScroll > 10
  showNavbar.value = currentScroll < lastScrollTop || currentScroll <= 0
  lastScrollTop = currentScroll <= 0 ? 0 : currentScroll
}

const logoSrc = computed(() =>
  isScrolled.value ? '/images/logo.svg' : '/images/logo-light.svg'
)

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
  document.body.style.overflow = ''
})
</script>