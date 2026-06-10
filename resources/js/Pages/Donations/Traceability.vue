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
            <span class="badge-role">Donaciones</span>
          </div>
          <Link href="/dashboard" class="btn btn-secondary btn-sm">Volver al Dashboard</Link>
        </header>

        <!-- Section Header -->
        <div class="section-header">
          <h1>Trazabilidad de Insumos & Transparencia Financiera</h1>
          <p class="subtitle">Rastrea el destino exacto de cada donación. Financiación comunitaria de kits de bienvenida y rescates médicos.</p>
        </div>

        <!-- Success Alert -->
        <div class="alert alert-success" v-if="flash.success">
          {{ flash.success }}
        </div>

        <!-- Overview Dashboard Cards -->
        <div class="donations-dashboard-grid">
          <div class="card stat-card border-purple">
            <span class="stat-icon">💰</span>
            <div class="stat-info">
              <h2>${{ formatMoney(totalRaised) }} CLP</h2>
              <p>Total de Fondos Recaudados</p>
            </div>
          </div>

          <div class="card stat-card border-blue">
            <span class="stat-icon">🎁</span>
            <div class="stat-info">
              <h2>{{ kitsApadrinadosCount }} Kits</h2>
              <p>Kits de Bienvenida Apadrinados</p>
            </div>
          </div>
        </div>

        <!-- Distribution & Donation Forms -->
        <div class="donations-columns-grid">
          <!-- Left Column: Distribution & Forms -->
          <div class="donations-column">
            <!-- Distribution (Destino de los fondos) -->
            <div class="card">
              <h3>🗺️ Destino y Distribución de Fondos</h3>
              <p class="card-desc">Garantizamos auditoría e imputación exacta para que las empresas y padrinos vean su impacto.</p>
              
              <div class="distribution-bars">
                <div class="dist-bar-item">
                  <div class="dist-header">
                    <span>🥩 Alimentos y Nutrición (60%)</span>
                    <strong>${{ formatMoney(distribution.alimentos) }} CLP</strong>
                  </div>
                  <div class="progress-bar-bg">
                    <div class="progress-bar-fill bg-success" style="width: 60%"></div>
                  </div>
                </div>

                <div class="dist-bar-item">
                  <div class="dist-header">
                    <span>🏥 Gastos Veterinarios e Insumos (25%)</span>
                    <strong>${{ formatMoney(distribution.veterinario) }} CLP</strong>
                  </div>
                  <div class="progress-bar-bg">
                    <div class="progress-bar-fill bg-warning" style="width: 25%"></div>
                  </div>
                </div>

                <div class="dist-bar-item">
                  <div class="dist-header">
                    <span>🚗 Logística, Rescates y Traslados (15%)</span>
                    <strong>${{ formatMoney(distribution.logistica) }} CLP</strong>
                  </div>
                  <div class="progress-bar-bg">
                    <div class="progress-bar-fill bg-blue" style="width: 15%"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Make Donation Form -->
            <div class="card">
              <h3>💖 Apadrinar o Donar Dinero</h3>
              <p class="card-desc">Simula tu aporte a través de la pasarela segura de MercadoPago. Sumas puntos de Huellas por tu apoyo financiero.</p>

              <!-- Tabs: Direct Donation or Kit Sponsorship -->
              <div class="tabs-nav" style="margin-bottom: 1.5rem;">
                <button class="tab-btn" :class="{ active: donationType === 'direct' }" @click="donationType = 'direct'">
                  💰 Donación Directa
                </button>
                <button class="tab-btn" :class="{ active: donationType === 'kit' }" @click="donationType = 'kit'">
                  🎁 Apadrinar Kit de Bienvenida
                </button>
              </div>

              <!-- Tab 1: Direct -->
              <div v-show="donationType === 'direct'">
                <form @submit.prevent="submitDonation(null)" class="donation-form">
                  <div class="form-group">
                    <label for="direct-amount">Monto a donar (CLP)</label>
                    <input 
                      type="number" 
                      id="direct-amount" 
                      v-model.number="form.amount" 
                      min="1000" 
                      placeholder="Monto mínimo $1.000 CLP..." 
                      required 
                    />
                    <small class="helper-text">Recibes 1 punto del Club de Huellas por cada $100 CLP donados (máx 200 pts).</small>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                    {{ form.processing ? 'Procesando checkout...' : 'Donar con MercadoPago' }}
                  </button>
                </form>
              </div>

              <!-- Tab 2: Kit -->
              <div v-show="donationType === 'kit'">
                <p class="kit-desc-p">Patrocina el primer kit de un perro o gato recién adoptado (plato, collar, manta con olor familiar y alimento para 15 días). Costo fijo de **$15.000 CLP**.</p>
                <form @submit.prevent="submitDonation('kit_bienvenida')" class="donation-form">
                  <div class="form-group">
                    <label>Kit de Bienvenida Standard</label>
                    <input type="text" value="Kit de Iniciación y Transición Canina/Felina" disabled />
                  </div>

                  <button type="submit" class="btn btn-success btn-block" :disabled="form.processing">
                    {{ form.processing ? 'Procesando checkout...' : 'Apadrinar Kit ($15.000 CLP) con MercadoPago' }}
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Right Column: Donations History Log -->
          <div class="donations-column">
            <!-- My Donations -->
            <div class="card">
              <h3>📅 Mis Aportes</h3>
              <p class="card-desc">Historial de tus donaciones y transferencias de kits.</p>

              <div class="my-donations-list" v-if="myDonations.length > 0">
                <div class="donation-log-item" v-for="d in myDonations" :key="d.id">
                  <div class="don-log-header">
                    <strong>${{ formatMoney(d.amount) }} CLP</strong>
                    <span class="badge-success-sm">{{ d.status }}</span>
                  </div>
                  <div class="don-log-footer">
                    <span>{{ d.kit_id ? '🎁 Kit de Bienvenida' : '💰 Donación Libre' }}</span>
                    <span>{{ formatDate(d.created_at) }}</span>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                Aún no has registrado donaciones en la plataforma.
              </div>
            </div>

            <!-- Public Audit Logs (Transparencia Financiera) -->
            <div class="card">
              <h3>🔍 Libro de Transparencia Colectiva</h3>
              <p class="card-desc">Registro auditado en tiempo real de todos los ingresos de la red.</p>

              <div class="public-donations-list" v-if="donations.length > 0">
                <div class="public-don-row" v-for="d in donations" :key="d.id">
                  <div class="pub-avatar">👤</div>
                  <div class="pub-info">
                    <strong>{{ d.user.name }}</strong>
                    <span class="pub-desc">{{ d.kit_id ? 'Apadrinó un Kit de Bienvenida' : 'Donó al fondo de bienestar animal' }}</span>
                    <small class="pub-date">{{ formatDate(d.created_at) }}</small>
                  </div>
                  <div class="pub-amount">
                    +${{ formatMoney(d.amount) }}
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                No hay transacciones registradas en el libro público.
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

export default {
  name: 'Traceability',
  components: {
    Link,
  },
  props: {
    donations: Array,
    totalRaised: [Number, String],
    kitsApadrinadosCount: Number,
    distribution: Object,
    myDonations: Array,
  },
  setup() {
    const donationType = ref('direct')
    const page = usePage()
    const flash = computed(() => page.props.flash || {})

    const form = useForm({
      amount: '',
      kit_id: null,
    })

    const submitDonation = (kitId) => {
      if (kitId) {
        form.amount = 15000
        form.kit_id = kitId
      } else {
        form.kit_id = null
      }

      form.post('/donations/checkout', {
        onSuccess: () => {
          form.reset()
        }
      })
    }

    const formatMoney = (val) => {
      if (val === undefined || val === null) return '0'
      return parseFloat(val).toLocaleString('es-CL')
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }

    return {
      donationType,
      flash,
      form,
      submitDonation,
      formatMoney,
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
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
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

/* Stat Cards */
.donations-dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2.5rem;
}

@media (max-width: 768px) {
  .donations-dashboard-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  flex-direction: row;
  align-items: center;
  gap: 1.5rem;
  padding: 1.75rem 2.25rem;
}

.stat-icon {
  font-size: 2.5rem;
  background: rgba(255, 255, 255, 0.03);
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
}

.stat-info h2 {
  font-family: var(--font-title);
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0 0 0.25rem 0;
}

.stat-info p {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  margin: 0;
  font-weight: 500;
}

.border-purple { border-color: rgba(139, 92, 246, 0.3); }
.border-blue { border-color: rgba(14, 165, 233, 0.3); }

/* Grid Columns */
.donations-columns-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .donations-columns-grid {
    grid-template-columns: 1fr;
  }
}

.donations-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
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
}

.card h3 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  border-left: 3px solid var(--color-primary);
  padding-left: 0.75rem;
}

.card-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: -0.5rem 0 0 0;
}

/* Distribution bar styles */
.distribution-bars {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-top: 0.5rem;
}

.dist-bar-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.dist-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  font-weight: 600;
}

.progress-bar-bg {
  width: 100%;
  height: 10px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 99px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  border-radius: 99px;
}

.bg-success { background-color: var(--color-success); }
.bg-warning { background-color: var(--color-warning); }
.bg-blue { background-color: var(--color-secondary); }

/* Tabs */
.tabs-nav {
  display: flex;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.3rem;
  gap: 0.5rem;
}

.tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-family: var(--font-body);
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.6rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: var(--color-primary);
  color: white;
  box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
}

.kit-desc-p {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0 0 0.5rem 0;
}

/* Forms */
.donation-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-main);
}

.form-group input {
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

.form-group input:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.helper-text {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

/* Log items */
.my-donations-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.donation-log-item {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.04);
  padding: 0.9rem 1.1rem;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.don-log-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.don-log-header strong {
  font-size: 1rem;
  color: var(--color-secondary);
}

.don-log-footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.badge-success-sm {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.15rem 0.4rem;
  border-radius: 6px;
}

/* Public Transparency List */
.public-donations-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 380px;
  overflow-y: auto;
}

.public-don-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.8rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.pub-avatar {
  font-size: 1.25rem;
  background: rgba(255, 255, 255, 0.03);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pub-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  flex-grow: 1;
}

.pub-info strong {
  font-size: 0.9rem;
  color: var(--color-text-main);
}

.pub-desc {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.pub-date {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.pub-amount {
  font-family: var(--font-title);
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--color-success);
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

/* Alerts */
.alert {
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
  border: 1px solid transparent;
}

.alert-success {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.2);
  color: var(--color-success);
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.75rem 1.6rem;
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

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-success {
  background: var(--color-success);
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

.btn-block {
  width: 100%;
  box-sizing: border-box;
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}
</style>
