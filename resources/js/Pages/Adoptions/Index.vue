<template>
  <div class="backoffice-container">
    <div class="bg-gradient-circle blob-1"></div>

    <div class="dashboard-layout">
      <!-- Main Content -->
      <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
          <div class="logo">
            <span class="logo-icon">🐾</span>
            <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
            <span class="badge-role">Adopciones</span>
          </div>
          <div class="user-menu">
            <Link href="/dashboard" class="btn btn-secondary btn-sm">Volver al Dashboard</Link>
          </div>
        </header>

        <!-- Section Header -->
        <div class="section-header">
          <div>
            <h1>Solicitudes de Adopción</h1>
            <p class="subtitle">Historial y seguimiento de trámites, evaluaciones de compatibilidad y contratos firmados.</p>
          </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
          <table class="data-table" v-if="adoptions.length > 0">
            <thead>
              <tr>
                <th>Mascota</th>
                <th>Adoptante</th>
                <th>Rescatista / ONG</th>
                <th>Compatibilidad</th>
                <th>Estado</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="adoption in adoptions" :key="adoption.id">
                <td>
                  <div class="pet-profile-cell">
                    <div class="pet-avatar-placeholder">
                      {{ adoption.pet.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                      <div class="pet-name">{{ adoption.pet.name }}</div>
                      <div class="pet-species text-capitalize">{{ adoption.pet.species }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="user-name">{{ adoption.adopter.name }}</div>
                  <div class="user-meta">{{ adoption.adopter.city }}</div>
                </td>
                <td>
                  <div class="user-name">{{ adoption.rescuer.name }}</div>
                </td>
                <td>
                  <div class="compatibility-container">
                    <span class="score-value" :class="getScoreClass(adoption.compatibility_score)">
                      {{ Math.round(adoption.compatibility_score) }}%
                    </span>
                    <div class="score-bar-bg">
                      <div class="score-bar-fill" :class="getScoreClass(adoption.compatibility_score)" :style="{ width: adoption.compatibility_score + '%' }"></div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="status-badge" :class="'badge-' + adoption.status">
                    {{ formatStatus(adoption.status) }}
                  </span>
                </td>
                <td class="text-right">
                  <Link :href="'/adoptions/' + adoption.id" class="btn btn-secondary btn-sm">
                    Ver Detalle
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="empty-state" v-else>
            <div class="empty-icon">🤝</div>
            <h3>Sin solicitudes registradas</h3>
            <p>No se encontraron postulaciones o trámites de adopción activos en este momento.</p>
            <div style="margin-top: 1.5rem;">
              <Link href="/pets" class="btn btn-primary">Ver Mascotas en Adopción</Link>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'Index',
  components: {
    Link,
  },
  props: {
    adoptions: Array,
  },
  setup() {
    const formatStatus = (statusVal) => {
      const map = {
        solicitado: 'Postulación',
        en_proceso: 'En Trámite',
        aprobado: 'Aprobado',
        rechazado: 'Rechazado',
        finalizado: 'Adoptado ✓',
      }
      return map[statusVal] || statusVal
    }

    const getScoreClass = (score) => {
      if (score >= 80) return 'score-high'
      if (score >= 60) return 'score-medium'
      return 'score-low'
    }

    return {
      formatStatus,
      getScoreClass,
    }
  }
}
</script>


<style scoped>
.backoffice-container {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  box-sizing: border-box;
}

/* Background Blob */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.1;
  z-index: 0;
  pointer-events: none;
}

.blob-1 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -200px;
  left: -200px;
}

/* Layout */
.dashboard-layout {
  position: relative;
  z-index: 10;
  display: flex;
  min-height: 100vh;
  flex-direction: column;
}

.main-content {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
}

/* Topbar */
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
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Section Header */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
}

.section-header h1 {
  font-family: var(--font-title);
  font-size: 2rem;
  font-weight: 800;
  margin: 0 0 0.5rem 0;
}

.subtitle {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  margin: 0;
}

/* Data Table */
.table-container {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 2rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.9rem;
}

.data-table th, .data-table td {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.data-table th {
  font-family: var(--font-body);
  font-weight: 700;
  color: var(--color-text-muted);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: rgba(255, 255, 255, 0.01);
}

.data-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.015);
}

.pet-profile-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pet-avatar-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}

.pet-name {
  font-weight: 600;
  color: var(--color-text-main);
  font-size: 0.95rem;
}

.pet-species {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.user-name {
  font-weight: 600;
  color: var(--color-text-main);
}

.user-meta {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.text-muted {
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.text-right {
  text-align: right;
}

/* Match Compatibility Score */
.compatibility-container {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100px;
}

.score-value {
  font-weight: 700;
  font-size: 0.9rem;
}

.score-bar-bg {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.score-bar-fill {
  height: 100%;
  border-radius: 2px;
}

.score-high { color: var(--color-success); background-color: var(--color-success); }
.score-medium { color: var(--color-warning); background-color: var(--color-warning); }
.score-low { color: var(--color-accent); background-color: var(--color-accent); }

/* Badges */
.status-badge {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-solicitado {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--color-text-muted);
}

.badge-en_proceso {
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
}

.badge-aprobado {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--color-warning);
}

.badge-finalizado {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
}

.badge-rechazado {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: var(--color-accent);
}

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: var(--color-text-muted);
  max-width: 400px;
  margin: 0 auto;
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.6rem 1.4rem;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-block;
  text-align: center;
  text-decoration: none;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover {
  background: var(--color-primary-hover);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}
</style>
