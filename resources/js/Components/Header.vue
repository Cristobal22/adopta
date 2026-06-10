<template>
  <header class="header">
    <Link href="/" class="logo" @click="isMenuOpen = false">
      <span class="logo-icon">🐾</span>
      <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
    </Link>
    
    <!-- Botón Hamburguesa Móvil -->
    <button class="menu-toggle" @click="isMenuOpen = !isMenuOpen" :aria-expanded="isMenuOpen" aria-label="Abrir menú de navegación">
      <span class="bar" :class="{ 'bar-top-active': isMenuOpen }"></span>
      <span class="bar" :class="{ 'bar-middle-active': isMenuOpen }"></span>
      <span class="bar" :class="{ 'bar-bottom-active': isMenuOpen }"></span>
    </button>
    
    <nav class="nav" :class="{ 'nav-open': isMenuOpen }">
      <Link href="/" class="nav-link" :class="{ active: isActive('/') }" @click="isMenuOpen = false">Inicio</Link>
      <Link href="/pets" class="nav-link" :class="{ active: isActive('/pets') }" @click="isMenuOpen = false">Adoptar</Link>
      <Link href="/bazar" class="nav-link" :class="{ active: isActive('/bazar') }" @click="isMenuOpen = false">Bazar Animal</Link>
      <Link href="/nosotros" class="nav-link" :class="{ active: isActive('/nosotros') }" @click="isMenuOpen = false">Nosotros</Link>
      <template v-if="user">
        <Link href="/dashboard" class="btn btn-secondary btn-sm" @click="isMenuOpen = false">Dashboard</Link>
      </template>
      <template v-else>
        <Link href="/login" class="btn btn-secondary btn-sm" @click="isMenuOpen = false">Ingresar</Link>
      </template>
    </nav>
  </header>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const isMenuOpen = ref(false)

const isActive = (path) => {
  if (path === '/') {
    return page.url === '/'
  }
  return page.url.startsWith(path)
}
</script>

<style scoped>
.header {
  position: relative;
  z-index: 100;
  width: 100%;
  max-width: 1200px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  margin: 0 auto;
  box-sizing: border-box;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
}

.logo-icon {
  font-size: 1.75rem;
  animation: wag 2s ease-in-out infinite;
}

.logo-text {
  font-family: var(--font-title);
  font-weight: 800;
  font-size: 1.5rem;
  letter-spacing: -0.5px;
  color: var(--color-text-main);
}

.logo-dot {
  color: var(--color-primary);
}

.nav {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-link {
  color: var(--color-text-muted);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  transition: color 0.3s ease;
}

.nav-link:hover, .nav-link.active {
  color: var(--color-text-main);
}

@keyframes wag {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(15deg); }
}

.btn {
  font-family: var(--font-body);
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-block;
  text-align: center;
  text-decoration: none;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.07);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}

/* Hamburguesa móvil */
.menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  height: 18px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 105;
}

.menu-toggle .bar {
  width: 100%;
  height: 2px;
  background-color: var(--color-text-main);
  border-radius: 4px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-toggle .bar-top-active {
  transform: translateY(8px) rotate(45deg);
}

.menu-toggle .bar-middle-active {
  opacity: 0;
  transform: scale(0);
}

.menu-toggle .bar-bottom-active {
  transform: translateY(-8px) rotate(-45deg);
}

@media (max-width: 768px) {
  .menu-toggle {
    display: flex;
  }

  .nav {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: var(--color-bg);
    border-bottom: 1px solid rgba(43, 37, 33, 0.08);
    box-shadow: 0 15px 30px rgba(43, 37, 33, 0.08);
    flex-direction: column;
    align-items: stretch;
    gap: 0;
    padding: 0 2rem;
    max-height: 0;
    opacity: 0;
    visibility: hidden;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                padding 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.3s ease,
                visibility 0.3s ease;
    z-index: 100;
  }

  .nav.nav-open {
    max-height: 400px;
    padding: 1.5rem 2rem 2.5rem 2rem;
    opacity: 1;
    visibility: visible;
  }

  .nav-link {
    display: block;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(43, 37, 33, 0.05);
    width: 100%;
    font-size: 1.05rem;
  }

  .nav-link:last-of-type {
    border-bottom: none;
  }

  .btn {
    margin-top: 1.25rem;
    width: 100%;
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
  }
}
</style>
