<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">
      <!-- Section Header -->
      <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h1>Registro de Auditoría del Sistema</h1>
          <p class="subtitle">Inspección de acciones administrativas, transacciones de donación, firmas de contratos y modificaciones de datos.</p>
        </div>
        <div>
          <Link href="/backoffice/contracts-vault" class="btn btn-secondary btn-sm">Bóveda de Contratos</Link>
        </div>
      </div>

        <!-- Filter Panel Card -->
        <div class="card filter-card">
          <div class="filter-grid">
            <div class="form-group">
              <label>Buscar</label>
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Buscar por usuario, IP o acción..." 
                @input="applyFilters"
                class="search-input-box" 
              />
            </div>

            <div class="form-group">
              <label>Acción</label>
              <select v-model="selectedAction" @change="applyFilters" class="select-box">
                <option value="">Todas las acciones</option>
                <option v-for="act in actions" :key="act" :value="act">{{ formatActionName(act) }}</option>
              </select>
            </div>

            <div class="form-group reset-group">
              <button class="btn btn-secondary" @click="resetFilters">Limpiar Filtros</button>
            </div>
          </div>
        </div>

        <!-- Logs Table Card -->
        <div class="card">
          <div class="table-wrapper" v-if="logs.data.length > 0">
            <table class="vault-table">
              <thead>
                <tr>
                  <th>Fecha y Hora</th>
                  <th>Usuario</th>
                  <th>Acción</th>
                  <th>IP</th>
                  <th>Detalles</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="log in logs.data" :key="log.id">
                  <tr>
                    <td>{{ formatDateTime(log.created_at) }}</td>
                    <td>
                      <div class="user-info-cell" v-if="log.user">
                        <strong>{{ log.user.name }}</strong>
                        <span class="text-muted">{{ log.user.email }} ({{ log.user.role }})</span>
                      </div>
                      <span class="text-muted" v-else>Sistema / Anónimo</span>
                    </td>
                    <td>
                      <span class="action-tag" :class="getActionBadgeClass(log.action)">
                        {{ formatActionName(log.action) }}
                      </span>
                    </td>
                    <td><span class="ip-text">{{ log.ip_address || 'Local' }}</span></td>
                    <td>
                      <button 
                        class="btn btn-secondary btn-sm" 
                        @click="toggleDetails(log.id)"
                        v-if="log.old_values || log.new_values"
                      >
                        {{ expandedLogId === log.id ? 'Hide ✕' : 'Inspect 👁️' }}
                      </button>
                      <span class="text-muted" v-else>Sin datos modificados</span>
                    </td>
                  </tr>

                  <!-- Extended Inspector Row -->
                  <tr v-if="expandedLogId === log.id" class="inspector-row">
                    <td colspan="5">
                      <div class="inspector-content">
                        <div class="inspector-section" v-if="log.old_values">
                          <h5>Valores Anteriores (JSON)</h5>
                          <pre class="json-box"><code>{{ JSON.stringify(log.old_values, null, 2) }}</code></pre>
                        </div>
                        <div class="inspector-section" v-if="log.new_values">
                          <h5>Valores Nuevos (JSON)</h5>
                          <pre class="json-box"><code>{{ JSON.stringify(log.new_values, null, 2) }}</code></pre>
                        </div>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          
          <div class="empty-state-sm" v-else>
            No se encontraron logs de auditoría que coincidan con la búsqueda.
          </div>

          <!-- Pagination Component -->
          <div class="pagination" v-if="logs.links.length > 3">
            <template v-for="(link, key) in logs.links" :key="key">
              <div 
                v-if="link.url === null" 
                class="pagination-item disabled" 
                v-html="link.label"
              ></div>
              <Link 
                v-else 
                class="pagination-item" 
                :class="{ active: link.active }" 
                :href="link.url" 
                v-html="link.label"
              ></Link>
            </template>
          </div>
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'AuditLogs',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    logs: Object,
    actions: Array,
    filters: Object,
  },
  setup(props) {
    const searchQuery = ref(props.filters.search || '')
    const selectedAction = ref(props.filters.action_filter || '')
    const expandedLogId = ref(null)

    const applyFilters = () => {
      router.get('/backoffice/audit-logs', {
        search: searchQuery.value,
        action_filter: selectedAction.value,
      }, {
        preserveState: true,
        replace: true,
      })
    }

    const resetFilters = () => {
      searchQuery.value = ''
      selectedAction.value = ''
      applyFilters()
    }

    const toggleDetails = (logId) => {
      if (expandedLogId.value === logId) {
        expandedLogId.value = null
      } else {
        expandedLogId.value = logId
      }
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      })
    }

    const formatActionName = (action) => {
      if (!action) return ''
      return action
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    }

    const getActionBadgeClass = (action) => {
      const a = action.toLowerCase()
      if (a.includes('create') || a.includes('make_donation') || a.includes('success')) return 'badge-create'
      if (a.includes('update') || a.includes('sign_adoption') || a.includes('upload')) return 'badge-update'
      if (a.includes('delete') || a.includes('reject') || a.includes('alert_triggered')) return 'badge-danger'
      return 'badge-info'
    }

    return {
      searchQuery,
      selectedAction,
      expandedLogId,
      applyFilters,
      resetFilters,
      toggleDetails,
      formatDateTime,
      formatActionName,
      getActionBadgeClass,
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

/* Background Blobs */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.1;
  z-index: 0;
  pointer-events: none;
}

.blob-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -100px;
  left: -100px;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -100px;
  right: -100px;
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

/* Section Header */
.section-header {
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

/* Cards */
.card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 2.25rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-bottom: 2rem;
}

/* Filter Card */
.filter-card {
  padding: 1.5rem 2rem;
}

.filter-grid {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1fr;
  gap: 1.5rem;
  align-items: flex-end;
}

@media (max-width: 768px) {
  .filter-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.search-input-box, .select-box {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.9rem;
  outline: none;
  box-sizing: border-box;
  width: 100%;
}

.select-box option {
  background-color: var(--color-bg);
  color: white;
}

.search-input-box:focus, .select-box:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.reset-group {
  text-align: right;
}

.reset-group .btn {
  width: 100%;
}

/* Tables */
.table-wrapper {
  overflow-x: auto;
  width: 100%;
}

.vault-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.9rem;
}

.vault-table th, .vault-table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.vault-table th {
  font-family: var(--font-title);
  font-weight: 700;
  color: var(--color-text-muted);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.vault-table td {
  color: var(--color-text-main);
  vertical-align: middle;
}

.vault-table tbody tr:hover td {
  background: rgba(255, 255, 255, 0.01);
}

.user-info-cell {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.ip-text {
  font-family: monospace;
  background: rgba(255, 255, 255, 0.04);
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.85rem;
}

/* Badges */
.action-tag {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}

.badge-create {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
}

.badge-update {
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
}

.badge-danger {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
}

.badge-info {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--color-text-muted);
}

/* Inspector row */
.inspector-row td {
  background: rgba(0, 0, 0, 0.25);
  padding: 1.5rem 2rem;
}

.inspector-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 768px) {
  .inspector-content {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.inspector-section h5 {
  margin: 0 0 0.75rem 0;
  font-family: var(--font-title);
  font-size: 0.85rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.json-box {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 1rem;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.8rem;
  color: #a7f3d0;
  overflow-x: auto;
  margin: 0;
  max-height: 250px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.4rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}

.pagination-item {
  display: inline-block;
  padding: 0.5rem 0.9rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  text-decoration: none;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-item:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.1);
}

.pagination-item.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
  box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
}

.pagination-item.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Empty State */
.empty-state-sm {
  text-align: center;
  padding: 2.5rem;
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-block;
  text-align: center;
  text-decoration: none;
  white-space: nowrap;
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
  padding: 0.4rem 0.8rem;
}
</style>
