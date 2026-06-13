<template>
  <header class="topbar">
    <div class="logo">
      <span class="logo-icon">{{ icon }}</span>
      <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
      <span v-if="displayBadge" class="badge-role">{{ displayBadge }}</span>
    </div>
    <div class="user-menu-container">
      <slot name="actions">
        <div class="user-menu">
          <template v-if="user">
            <span class="username" v-if="['admin', 'fundacion', 'rescatista'].includes(user.role)">Panel de Gestión</span>
            <span class="username" v-else>¡Hola, {{ user.name }}!</span>
            <Link :href="backUrl" class="btn btn-secondary btn-sm">{{ backText }}</Link>
          </template>
          <template v-else>
            <Link href="/login" class="btn btn-secondary btn-sm">Ingresar</Link>
          </template>
        </div>
      </slot>
    </div>
  </header>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    default: '🐾'
  },
  badgeText: {
    type: String,
    default: ''
  },
  backUrl: {
    type: String,
    default: '/dashboard'
  },
  backText: {
    type: String,
    default: 'Volver al Dashboard'
  }
})

const page = usePage()
const user = computed(() => page.props.auth.user)

const displayBadge = computed(() => {
  if (props.badgeText) return props.badgeText
  
  if (!user.value) {
    return 'Catálogo Público'
  }
  
  if (['adoptante', 'transito', 'donante'].includes(user.value.role)) {
    return 'Catálogo'
  }
  
  return 'Backoffice'
})
</script>

<style scoped>
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0 2rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  margin-bottom: 2rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-family: var(--font-title);
  font-weight: 800;
  font-size: 1.25rem;
}

.logo-dot {
  color: var(--color-primary);
}

.badge-role {
  background: rgba(13, 165, 233, 0.15);
  border: 1px solid rgba(13, 165, 233, 0.3);
  color: var(--color-secondary);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
}

.user-menu-container {
  display: flex;
  align-items: center;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.username {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* Responsiveness for small screens */
@media (max-width: 640px) {
  .topbar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 1rem !important;
    padding: 1rem 0 !important;
  }
  
  .user-menu-container {
    width: 100% !important;
  }

  .user-menu {
    justify-content: space-between !important;
    width: 100% !important;
  }

  .username {
    font-size: 0.85rem !important;
  }
}

@media (max-width: 480px) {
  .username {
    display: none !important; /* Hide long welcome message on extremely small viewports */
  }
  
  .user-menu {
    justify-content: flex-end !important; /* Align button to the right if username is hidden */
  }
}
</style>
