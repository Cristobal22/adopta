<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">

        <!-- Section Header -->
        <div class="section-header">
          <h1>Logística y Colaboración Comunitaria</h1>
          <p class="subtitle">Conecta con traslados voluntarios de mascotas ("Uber Solidario") o intercambia insumos médicos y alimentos.</p>
        </div>

        <!-- Tabs selector -->
        <div class="tabs-nav">
          <button class="tab-btn" :class="{ active: currentTab === 'uber' }" @click="currentTab = 'uber'">
            🚗 Uber Solidario (Traslados)
          </button>
          <button class="tab-btn" :class="{ active: currentTab === 'resources' }" @click="currentTab = 'resources'">
            📦 Banco de Recursos
          </button>
        </div>

        <!-- TAB 1: UBER SOLIDARIO -->
        <div v-show="currentTab === 'uber'" class="logistics-grid">
          <!-- Full Width Summary Widget for Volunteers -->
          <div class="card volunteer-dashboard-widget grid-full-card">
            <h3>📊 Resumen de Oportunidades de Ayuda Activa</h3>
            <p class="card-desc">Eres parte vital del ecosistema de ayuda. Revisa las necesidades de traslado en tu zona.</p>
            <div class="vol-stats-grid">
              <div class="vol-stat-item">
                <span class="vol-stat-icon">🚗</span>
                <div class="vol-stat-info">
                  <span class="vol-stat-num">{{ availableTrips.length }}</span>
                  <span class="vol-stat-label">Traslados esperando chofer</span>
                </div>
              </div>
              <div class="vol-stat-item">
                <span class="vol-stat-icon">📦</span>
                <div class="vol-stat-info">
                  <span class="vol-stat-num">{{ resources.filter(r => r.status === 'disponible').length }}</span>
                  <span class="vol-stat-label">Insumos/Donaciones en banco</span>
                </div>
              </div>
              <div class="vol-stat-item">
                <span class="vol-stat-icon">🏆</span>
                <div class="vol-stat-info">
                  <span class="vol-stat-num">+50 Pts</span>
                  <span class="vol-stat-label">Por traslado completado</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Left Column: Available & Active Trips -->
          <div class="logistics-column">
            <!-- Active/My Trips -->
            <div class="card">
              <h3>Mis Traslados</h3>
              <p class="card-desc">Viajes solicitados por ti o donde te inscribiste como chofer voluntario.</p>

              <div class="trip-list" v-if="myTrips.length > 0">
                <div class="trip-card" v-for="trip in myTrips" :key="trip.id">
                  <div class="trip-header">
                    <span class="status-badge" :class="'badge-' + trip.status">{{ formatStatus(trip.status) }}</span>
                    <span class="trip-date">ID #{{ trip.id }}</span>
                  </div>
                  
                  <div class="trip-body">
                    <div class="trip-location">📍 <strong>Origen:</strong> {{ trip.origin }}</div>
                    <div class="trip-location">🏁 <strong>Destino:</strong> {{ trip.destination }}</div>
                    <div class="trip-pet" v-if="trip.pet">🐶 <strong>Mascota:</strong> {{ trip.pet.name }}</div>
                  </div>

                  <div class="trip-people">
                    <span>👤 Solicitado por: <strong>{{ trip.requester.name }}</strong></span>
                    <span v-if="trip.driver">🚗 Chofer: <strong>{{ trip.driver.name }}</strong></span>
                  </div>

                  <!-- Complete Action (Visible to driver if status is accepted) -->
                  <div class="trip-actions" v-if="trip.status === 'aceptado' && trip.driver_id === currentUserId">
                    <button class="btn btn-primary btn-sm btn-block" @click="completeTrip(trip)">
                      ✓ Marcar como Completado (+50 pts)
                    </button>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                No tienes traslados registrados en este momento.
              </div>
            </div>

            <!-- Available Requests (Trips waiting for a driver) -->
            <div class="card">
              <h3>Solicitudes de Traslado Disponibles</h3>
              <p class="card-desc">Ayuda a trasladar a una mascota o kits de insumos médicos. ¡Inscríbete como conductor voluntario!</p>

              <div class="trip-list" v-if="availableTrips.length > 0">
                <div class="trip-card trip-available-card" v-for="trip in availableTrips" :key="trip.id">
                  <div class="trip-card-badge-row">
                    <span class="urgency-badge badge-urgent">⚠️ Traslado Pendiente</span>
                    <span class="trip-id-tag">VIAJE #{{ trip.id }}</span>
                  </div>
                  
                  <div class="trip-body">
                    <div class="trip-route-visual">
                      <div class="route-point">
                        <span class="route-dot origin-dot">📍</span>
                        <div class="route-details">
                          <span class="route-label">Punto de Retiro:</span>
                          <span class="route-address">{{ trip.origin }}</span>
                        </div>
                      </div>
                      <div class="route-connector"></div>
                      <div class="route-point">
                        <span class="route-dot dest-dot">🏁</span>
                        <div class="route-details">
                          <span class="route-label">Destino de Entrega:</span>
                          <span class="route-address">{{ trip.destination }}</span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="trip-pet-info" v-if="trip.pet">
                      <span class="pet-emoji">🐾</span>
                      <div class="pet-detail-text">
                        <strong>Mascota:</strong> {{ trip.pet.name }} <span class="pet-spec text-capitalize">({{ trip.pet.species }} • {{ trip.pet.breed || 'Mestizo' }})</span>
                      </div>
                    </div>
                  </div>

                  <div class="trip-footer-row">
                    <span class="requester-name">👤 Por: {{ trip.requester.name }}</span>
                    <button class="btn btn-primary btn-sm accept-btn-premium" @click="acceptTrip(trip)">
                      🚗 Aceptar Traslado
                    </button>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                No hay solicitudes de traslados pendientes en tu zona.
              </div>
            </div>
          </div>

          <!-- Right Column: Request Trip Form -->
          <div class="logistics-column">
            <div class="card">
              <h3>Solicitar un Uber Solidario</h3>
              <p class="card-desc">Publica una necesidad de transporte para que un voluntario con vehículo te asista.</p>

              <form @submit.prevent="submitTripRequest" class="logistics-form">
                <div class="form-group">
                  <label for="pet_id">Mascota a trasladar (Opcional)</label>
                  <select id="pet_id" v-model="form.pet_id">
                    <option value="">Ninguna (Solo traslado de insumos/alimento)</option>
                    <option v-for="pet in pets" :key="pet.id" :value="pet.id">
                      {{ pet.name }} ({{ pet.species }}) - ID: {{ pet.id }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="origin">Dirección de Origen</label>
                  <input type="text" id="origin" v-model="form.origin" placeholder="Veterinaria, refugio o calle..." required />
                </div>

                <div class="form-group">
                  <label for="destination">Dirección de Destino</label>
                  <input type="text" id="destination" v-model="form.destination" placeholder="Hogar temporal, adoptante..." required />
                </div>

                <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                  {{ form.processing ? 'Publicando...' : 'Solicitar Chofer' }}
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- TAB 2: BANCO DE RECURSOS COMUNITARIO -->
        <div v-show="currentTab === 'resources'" class="resources-grid">
          <div class="card grid-full-card">
            <h3>Panel de Insumos Comunitarios</h3>
            <p class="card-desc">Donaciones publicadas por fundaciones y particulares. Puedes coordinar su retiro o entrega.</p>

            <!-- Warning Notice for Medical Resource Blackmarket Protection -->
            <div class="alert alert-warning" style="margin-top: 1rem; margin-bottom: 1.5rem;">
              <strong>⚠️ Regla del Banco de Recursos:</strong> Solo se permite el intercambio de insumos de asistencia básica (camas, platos, collares, juguetes) y alimento en empaques cerrados de fábrica. Queda estrictamente prohibida la publicación de medicamentos de prescripción (como Tramadol, antibióticos, etc.) para prevenir la automedicación y cumplir con las normativas de salud.
            </div>

            <div class="resource-catalog">
              <div class="resource-card" v-for="resource in resources" :key="resource.id">
                <div class="resource-header">
                  <span class="resource-type-tag" :class="resource.type">{{ formatResourceType(resource.type) }}</span>
                  <span class="resource-status-badge" :class="resource.status">{{ resource.status }}</span>
                </div>
                <h4>{{ resource.title }}</h4>
                <p class="resource-desc">{{ resource.description }}</p>
                <div class="resource-footer">
                  <span>👤 Donante: <strong>{{ resource.donor }}</strong></span>
                  <button class="btn btn-secondary btn-sm" v-if="resource.status === 'disponible'" @click="claimResource(resource)">
                    Reclamar Recurso
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Hub',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    availableTrips: Array,
    myTrips: Array,
    pets: Array,
    resources: Array,
  },
  setup() {
    const page = usePage()
    const currentTab = ref('uber')
    const currentUserId = ref(page.props.auth?.user?.id)

    const form = useForm({
      pet_id: '',
      origin: '',
      destination: '',
    })

    const submitTripRequest = () => {
      form.post('/logistics/uber', {
        onSuccess: () => {
          form.reset()
        }
      })
    }

    const acceptTrip = (trip) => {
      if (confirm('¿Estás seguro de que quieres comprometerte a realizar este traslado voluntario?')) {
        router.post(`/logistics/uber/${trip.id}/accept`)
      }
    }

    const completeTrip = (trip) => {
      if (confirm('¿Confirmas que el traslado ha sido finalizado exitosamente? Se sumarán 50 puntos a tu cuenta de voluntario.')) {
        router.post(`/logistics/uber/${trip.id}/complete`)
      }
    }

    const claimResource = (resource) => {
      alert(`Has solicitado el recurso "${resource.title}". Nos contactaremos con ${resource.donor} para coordinar el retiro.`);
    }

    const formatStatus = (statusVal) => {
      const map = {
        solicitado: 'Buscando Chofer',
        aceptado: 'En Camino',
        completado: 'Finalizado ✓',
        cancelado: 'Cancelado',
      }
      return map[statusVal] || statusVal
    }

    const formatResourceType = (type) => {
      const map = {
        alimento: 'Alimento',
        insumo_medico: 'Insumo Médico',
        juguete: 'Accesorios',
      }
      return map[type] || type
    }

    return {
      currentTab,
      currentUserId,
      form,
      submitTripRequest,
      acceptTrip,
      completeTrip,
      claimResource,
      formatStatus,
      formatResourceType,
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
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -200px;
  left: -200px;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -200px;
  right: -200px;
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

/* Tabs Navigation */
.tabs-nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 2.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1rem;
}

.tab-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-family: var(--font-body);
  font-size: 1.05rem;
  font-weight: 600;
  padding: 0.5rem 1rem;
  cursor: pointer;
  position: relative;
  transition: color 0.3s ease;
}

.tab-btn:hover {
  color: var(--color-text-main);
}

.tab-btn.active {
  color: var(--color-primary);
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -1.25rem;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--color-primary);
}

/* Grid Layouts */
.logistics-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .logistics-grid {
    grid-template-columns: 1fr;
  }
}

.logistics-column {
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

/* Forms */
.logistics-form {
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

.form-group input, .form-group select {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  transition: all 0.3s ease;
  outline: none;
  box-sizing: border-box;
}

.form-group input:focus, .form-group select:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.form-group select option {
  background-color: var(--color-bg);
  color: white;
}

/* Trip Cards list */
.trip-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.trip-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.trip-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.trip-date {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.trip-body {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.85rem;
}

.trip-location, .trip-pet {
  line-height: 1.4;
}

.trip-people {
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 0.75rem;
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.trip-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 0.75rem;
}

.requester-name {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

/* Badges */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-solicitado { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--color-text-muted); }
.badge-aceptado { background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); color: var(--color-secondary); }
.badge-completado { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--color-success); }

/* Empty state styling */
.empty-state-sm {
  text-align: center;
  padding: 2rem;
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

/* Resource catalog */
.resource-catalog {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-top: 0.5rem;
}

@media (max-width: 768px) {
  .resource-catalog {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .resource-catalog {
    grid-template-columns: 1fr;
  }
}

.resource-card {
  background: rgba(255, 255, 255, 0.015);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.resource-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.resource-type-tag {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.2rem 0.5rem;
  border-radius: 5px;
}

.resource-type-tag.alimento { background: rgba(249, 115, 22, 0.15); color: #fdba74; border: 1px solid rgba(249, 115, 22, 0.3); }
.resource-type-tag.insumo_medico { background: rgba(20, 184, 166, 0.15); color: #99f6e4; border: 1px solid rgba(20, 184, 166, 0.3); }

.resource-status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.resource-status-badge.disponible { color: var(--color-success); }
.resource-status-badge.reclamado { color: var(--color-text-muted); text-decoration: line-through; }

.resource-card h4 {
  font-family: var(--font-title);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.resource-desc {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.4;
  margin: 0;
  flex-grow: 1;
}

.resource-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.grid-full-card {
  grid-column: 1 / -1;
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

/* Volunteer Dashboard Summary Widget */
.volunteer-dashboard-widget {
  background: linear-gradient(135deg, rgba(255, 107, 74, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
  border: 1px solid rgba(255, 107, 74, 0.15) !important;
}

.vol-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-top: 1.25rem;
}

@media (max-width: 768px) {
  .vol-stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.vol-stat-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.vol-stat-icon {
  font-size: 1.75rem;
  background: rgba(255, 255, 255, 0.04);
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  flex-shrink: 0;
}

.vol-stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.vol-stat-num {
  font-family: var(--font-title);
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-primary);
}

.vol-stat-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* Trip Available Card Premium Styles */
.trip-available-card {
  border: 1px solid rgba(255, 107, 74, 0.1) !important;
  background: rgba(255, 255, 255, 0.02) !important;
}

.trip-card-badge-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.urgency-badge {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.25rem 0.6rem;
  border-radius: 99px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.badge-urgent {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #f59e0b;
}

.trip-id-tag {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--color-text-muted);
}

.trip-route-visual {
  display: flex;
  flex-direction: column;
  position: relative;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-left: 0.5rem;
}

.route-point {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.route-dot {
  font-size: 0.95rem;
  z-index: 2;
}

.route-connector {
  position: absolute;
  left: 14px;
  top: 18px;
  bottom: 14px;
  width: 2px;
  border-left: 2px dashed rgba(255, 255, 255, 0.1);
  z-index: 1;
}

.route-details {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.route-label {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.route-address {
  font-size: 0.85rem;
  color: var(--color-text-main);
  font-weight: 500;
}

.trip-pet-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.pet-emoji {
  font-size: 1.1rem;
}

.pet-detail-text {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.pet-detail-text strong {
  color: var(--color-text-main);
}

.pet-spec {
  font-size: 0.75rem;
}

.accept-btn-premium {
  background: linear-gradient(135deg, var(--color-primary) 0%, #a855f7 100%);
  color: white !important;
  box-shadow: 0 4px 10px rgba(255, 107, 74, 0.2);
  border: none;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}

.accept-btn-premium:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(255, 107, 74, 0.3);
}
</style>
