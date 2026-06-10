<template>
  <div class="backoffice-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <div class="dashboard-layout">
      <!-- Main Content -->
      <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
          <div class="logo">
            <span class="logo-icon">🐾</span>
            <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
            <span class="badge-role">Bóveda de Contratos</span>
          </div>
          <div class="topbar-actions">
            <Link href="/backoffice/audit-logs" class="btn btn-secondary btn-sm" style="margin-right: 0.5rem;">Ver Auditoría</Link>
            <Link href="/dashboard" class="btn btn-secondary btn-sm">Volver al Dashboard</Link>
          </div>
        </header>

        <!-- Section Header -->
        <div class="section-header">
          <h1>Bóveda de Contratos de Adopción</h1>
          <p class="subtitle">Búsqueda avanzada, descarga de firmas certificadas y auditoría de cumplimiento de cláusulas de tenencia responsable.</p>
        </div>

        <!-- Alerts panel if clause alerts exist -->
        <div class="card alert-vault-card" v-if="clauseAlerts.length > 0">
          <h3 class="alert-title">🚨 Cláusulas de Esterilización Vencidas</h3>
          <p class="card-desc">Adoptantes con más de 6 meses de custodia sin haber reportado certificado veterinario de castración/esterilización obligatorio.</p>
          
          <div class="alerts-table-wrapper">
            <table class="vault-table warning-table">
              <thead>
                <tr>
                  <th>Caso / Mascota</th>
                  <th>Adoptante</th>
                  <th>Contacto</th>
                  <th>Meses Transcurridos</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="alert in clauseAlerts" :key="alert.adoption_id">
                  <td><strong>{{ alert.pet_name }}</strong> (Adopción #{{ alert.adoption_id }})</td>
                  <td>{{ alert.adopter_name }}</td>
                  <td>{{ alert.adopter_email }}</td>
                  <td><span class="badge-danger-sm">{{ alert.months_elapsed }} meses</span></td>
                  <td class="text-warning">⚠ Incumplimiento</td>
                  <td>
                    <button class="btn btn-secondary btn-sm" @click="notifyAdopter(alert)">
                      ✉ Notificar Cobro
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Search Bar and List Card -->
        <div class="card">
          <div class="vault-search-header">
            <h3>📂 Historial de Contratos Firmados</h3>
            
            <div class="search-form-group">
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Buscar por mascota, microchip, adoptante..." 
                @input="runSearch" 
                class="search-input-box" 
              />
            </div>
          </div>

          <!-- Contracts Table -->
          <div class="table-wrapper" v-if="adoptions.length > 0">
            <table class="vault-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Mascota</th>
                  <th>Microchip</th>
                  <th>Adoptante</th>
                  <th>Tipo Firma</th>
                  <th>Fecha de Firma</th>
                  <th>Documento</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="adoption in adoptions" :key="adoption.id">
                  <td>#{{ adoption.id }}</td>
                  <td><strong>{{ adoption.pet.name }}</strong></td>
                  <td><span class="code-text">{{ adoption.pet.microchip_code || 'Sin microchip' }}</span></td>
                  <td>
                    <div class="adopter-row-cell">
                      <strong>{{ adoption.adopter.name }}</strong>
                      <span class="text-muted">{{ adoption.adopter.email }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="signature-tag" :class="adoption.signature_type">
                      {{ adoption.signature_type === 'digital_canvas' ? '🖌️ Canvas' : '📁 PDF Físico' }}
                    </span>
                  </td>
                  <td>{{ formatDate(adoption.updated_at) }}</td>
                  <td>
                    <a :href="'/adoptions/' + adoption.id + '/download-signed'" class="btn btn-secondary btn-sm">
                      📥 Descargar
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="empty-state-sm" v-else>
            No se encontraron contratos de adopción en la bóveda que coincidan con la búsqueda.
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

export default {
  name: 'Vault',
  components: {
    Link,
  },
  props: {
    adoptions: Array,
    clauseAlerts: Array,
    filters: Object,
  },
  setup(props) {
    const searchQuery = ref(props.filters.search || '')

    const runSearch = () => {
      router.get('/backoffice/contracts-vault', {
        search: searchQuery.value
      }, {
        preserveState: true,
        replace: true,
      })
    }

    const notifyAdopter = (alertData) => {
      alert(`Recordatorio de cláusula de esterilización enviado a ${alertData.adopter_name} (${alertData.adopter_email}) para el caso de ${alertData.pet_name}.`);
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }

    return {
      searchQuery,
      runSearch,
      notifyAdopter,
      formatDate,
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

.alert-vault-card {
  border-color: rgba(239, 68, 68, 0.3);
  background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.05), transparent);
}

.alert-title {
  color: #f87171;
  font-family: var(--font-title);
  font-size: 1.25rem;
  margin: 0;
  border-left: 3px solid #ef4444;
  padding-left: 0.75rem;
}

/* Vault Search Header */
.vault-search-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1.25rem;
  margin-bottom: 0.5rem;
}

.vault-search-header h3 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  border-left: 3px solid var(--color-primary);
  padding-left: 0.75rem;
}

.search-form-group {
  flex: 0 0 320px;
}

@media (max-width: 576px) {
  .search-form-group {
    flex: 1 1 100%;
  }
}

.search-input-box {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  outline: none;
  box-sizing: border-box;
  width: 100%;
}

.search-input-box:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

/* Vault Tables */
.table-wrapper, .alerts-table-wrapper {
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

.code-text {
  font-family: monospace;
  background: rgba(255, 255, 255, 0.05);
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.85rem;
}

.adopter-row-cell {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.text-muted {
  color: var(--color-text-muted);
  font-size: 0.8rem;
}

.signature-tag {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}

.signature-tag.digital_canvas {
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
}

.signature-tag.uploaded_pdf {
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
}

.badge-danger-sm {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}

.text-warning {
  color: var(--color-warning);
  font-weight: 600;
}

/* Empty state */
.empty-state-sm {
  text-align: center;
  padding: 2rem;
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
