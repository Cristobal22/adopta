<template>
  <header class="header">
    <Link href="/" class="logo">
      <span class="logo-icon">🐾</span>
      <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
    </Link>
    <nav class="nav">
      <Link href="/" class="nav-link" :class="{ active: isActive('/') }">Inicio</Link>
      <Link href="/pets" class="nav-link" :class="{ active: isActive('/pets') }">Adoptar</Link>
      <Link href="/bazar" class="nav-link" :class="{ active: isActive('/bazar') }">Bazar Animal</Link>
      <Link href="/nosotros" class="nav-link" :class="{ active: isActive('/nosotros') }">Nosotros</Link>
      <template v-if="user">
        <Link href="/dashboard" class="btn btn-secondary btn-sm">Dashboard</Link>
      </template>
      <template v-else>
        <Link href="/login" class="btn btn-secondary btn-sm">Ingresar</Link>
      </template>
    </nav>
  </header>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

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
</style>
