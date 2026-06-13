<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">

        <!-- Flash Alert -->
        <div class="alert alert-success" v-if="flash.success">
          {{ flash.success }}
        </div>

        <!-- Section Header -->
        <div class="section-header">
          <div>
            <h1>Municipalidad de <span class="highlight-text">{{ communeName }}</span></h1>
            <p class="subtitle">Panel de colaboración para operativos veterinarios, fiscalización de tenencia responsable y monitoreo comunal.</p>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="card stat-card border-blue">
            <span class="stat-icon">🐕</span>
            <div class="stat-info">
              <h2>{{ petsCount }} Mascotas</h2>
              <p>Registradas en la Comuna</p>
            </div>
          </div>

          <div class="card stat-card border-purple">
            <span class="stat-icon">🤝</span>
            <div class="stat-info">
              <h2>{{ adoptionsCount }} Adopciones</h2>
              <p>Formalizadas en la Bóveda</p>
            </div>
          </div>

          <div class="card stat-card border-red" :class="{ 'has-alerts': abuseAlertsCount > 0 }">
            <span class="stat-icon">🚨</span>
            <div class="stat-info">
              <h2>{{ abuseAlertsCount }} Alertas</h2>
              <p>Fiscalización / SOS Activos</p>
            </div>
            <Link href="/municipality/audit-abuse" class="btn btn-danger btn-sm" v-if="abuseAlertsCount > 0">
              Inspeccionar
            </Link>
          </div>
        </div>

        <!-- Split Grid (Map & Form) -->
        <div class="split-grid">
          <!-- Left Column: Map & Form -->
          <div class="grid-column">
            <!-- Map Card -->
            <div class="card">
              <h3>🗺️ Mapa de Abandono y Reportes Locales</h3>
              <p class="card-desc">Monitoreo en tiempo real de mascotas extraviadas o reportadas en la comuna para coordinar operativos de rescate.</p>
              <div id="muni-map" class="map-container"></div>
            </div>

            <!-- Create Operative Form -->
            <div class="card">
              <h3>🗓️ Programar Operativo Veterinario</h3>
              <p class="card-desc">Publica operativos gratuitos de chipeo obligatorio, vacunación antirrábica o campañas de esterilización para los vecinos.</p>
              
              <form @submit.prevent="submitOperative" class="muni-form">
                <div class="form-group">
                  <label for="title">Título del Operativo</label>
                  <input type="text" id="title" v-model="form.title" placeholder="Ej. Operativo de Esterilización y Microchips Gratis" required />
                  <span class="error-text" v-if="form.errors.title">{{ form.errors.title }}</span>
                </div>

                <div class="form-group">
                  <label for="description">Descripción / Requisitos de Admisión</label>
                  <textarea id="description" v-model="form.description" rows="3" placeholder="Ej. Traer toalla, ayuno de 8 horas, exclusivo para residentes con Registro Social de Hogares..."></textarea>
                  <span class="error-text" v-if="form.errors.description">{{ form.errors.description }}</span>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="date">Fecha y Hora</label>
                    <input type="datetime-local" id="date" v-model="form.date" required />
                    <span class="error-text" v-if="form.errors.date">{{ form.errors.date }}</span>
                  </div>

                  <div class="form-group">
                    <label for="capacity">Cupos Totales</label>
                    <input type="number" id="capacity" v-model.number="form.capacity" min="1" placeholder="Ej. 50" required />
                    <span class="error-text" v-if="form.errors.capacity">{{ form.errors.capacity }}</span>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                  {{ form.processing ? 'Programando...' : '✓ Programar Operativo Comunal' }}
                </button>
              </form>
            </div>
          </div>

          <!-- Right Column: Lists -->
          <div class="grid-column">
            <!-- Programmed Operatives List -->
            <div class="card">
              <h3>📢 Campañas Municipales Programadas</h3>
              <p class="card-desc">Operativos oficiales organizados por la municipalidad para este mes.</p>

              <div class="operatives-list" v-if="operatives.length > 0">
                <div class="operative-item" v-for="op in operatives" :key="op.id">
                  <div class="op-header">
                    <h4>{{ op.title }}</h4>
                    <span class="op-status text-uppercase">{{ op.status }}</span>
                  </div>
                  <p class="op-desc">{{ op.description }}</p>
                  <div class="op-footer">
                    <span>📅 {{ formatDateTime(op.date) }}</span>
                    <span>👥 Cupos: <strong>{{ op.capacity }}</strong></span>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                No hay operativos programados aún. Usa el formulario para publicar el primero.
              </div>
            </div>

            <!-- Local Adoptions Table -->
            <div class="card">
              <h3>📂 Historial de Adopciones en la Comuna</h3>
              <p class="card-desc">Control local de adopciones en la comuna para auditorías de tenencia responsable.</p>

              <div class="table-wrapper" v-if="adoptions.length > 0">
                <table class="muni-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Mascota</th>
                      <th>Adoptante</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="adoption in adoptions" :key="adoption.id">
                      <td>#{{ adoption.id }}</td>
                      <td><strong>{{ adoption.pet.name }}</strong></td>
                      <td>{{ adoption.adopter.name }}</td>
                      <td>
                        <span class="status-badge" :class="'badge-' + adoption.status">
                          {{ formatAdoptionStatus(adoption.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="empty-state-sm" v-else>
                No hay adopciones registradas en esta comuna todavía.
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
import { ref, onMounted, computed } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Dashboard',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    commune: String,
    adoptionsCount: Number,
    petsCount: Number,
    abuseAlertsCount: Number,
    operatives: Array,
    pets: Array,
    adoptions: Array,
  },
  setup(props) {
    const page = usePage()
    const flash = computed(() => page.props.flash || {})

    const form = useForm({
      title: '',
      description: '',
      date: '',
      capacity: '',
    })

    const communeName = computed(() => {
      if (!props.commune) return 'Colaboradora'
      return props.commune.split(',')[0].trim()
    })

    const logout = () => {
      router.post('/logout')
    }

    const submitOperative = () => {
      form.post('/municipality/operatives', {
        onSuccess: () => {
          form.reset()
        }
      })
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    }

    const formatAdoptionStatus = (status) => {
      const map = {
        solicitado: 'Postulación',
        en_proceso: 'En Trámite',
        aprobado: 'Aprobado',
        rechazado: 'Rechazado',
        finalizado: 'Finalizado ✓',
      }
      return map[status] || status
    }

    onMounted(() => {
      // Inicializar Mapa centrado en la comuna
      const cityCoords = {
        'Valparaíso': [-33.0472, -71.6127],
        'Concepción': [-36.8201, -73.0444],
        'Santiago': [-33.4489, -70.6693],
        'Providencia': [-33.4312, -70.6122],
        'Las Condes': [-33.4125, -70.5663],
        'La Serena': [-29.9027, -71.2520],
        'Antofagasta': [-23.6509, -70.3975],
        'Temuco': [-38.7397, -72.5901],
        'Punta Arenas': [-53.1638, -70.9171]
      }

      const rawCity = communeName.value
      const centerCoords = cityCoords[rawCity] || [-33.4489, -70.6693] // Santiago por defecto

      setTimeout(() => {
        if (!document.getElementById('muni-map')) return

        const map = L.map('muni-map').setView(centerCoords, 12)

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
          attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
          maxZoom: 20
        }).addTo(map)

        // Dibujar mascotas comunales
        props.pets.forEach(pet => {
          const lat = parseFloat(pet.latitude)
          const lng = parseFloat(pet.longitude)
          if (!isNaN(lat) && !isNaN(lng)) {
            const markerColor = pet.status === 'rescatado' ? '#f43f5e' : (pet.status === 'en_adopcion' ? '#8b5cf6' : '#10b981')
            const circle = L.circleMarker([lat, lng], {
              radius: 7,
              fillColor: markerColor,
              color: '#ffffff',
              weight: 1.5,
              opacity: 1,
              fillOpacity: 0.8
            }).addTo(map)

            circle.bindPopup(`<strong>${pet.name}</strong><br>Estado: <span style="color:${markerColor}">${pet.status}</span>`)
          }
        })
      }, 400)
    })

    return {
      flash,
      form,
      communeName,
      logout,
      submitOperative,
      formatDateTime,
      formatAdoptionStatus,
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
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fbbf24;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
}

.section-header {
  margin-bottom: 2.5rem;
}

.section-header h1 {
  font-family: var(--font-title);
  font-size: 2rem;
  font-weight: 800;
  margin: 0 0 0.5rem 0;
}

.highlight-text {
  color: var(--color-secondary);
}

.subtitle {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  margin: 0;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  flex-direction: row;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  position: relative;
}

.stat-icon {
  font-size: 2.25rem;
  background: rgba(255, 255, 255, 0.03);
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
}

.stat-info h2 {
  font-family: var(--font-title);
  font-size: 1.5rem;
  font-weight: 800;
  margin: 0 0 0.15rem 0;
}

.stat-info p {
  color: var(--color-text-muted);
  font-size: 0.85rem;
  margin: 0;
}

.border-blue { border-color: rgba(14, 165, 233, 0.25); }
.border-purple { border-color: rgba(139, 92, 246, 0.25); }
.border-red { border-color: rgba(244, 63, 94, 0.25); }
.border-red.has-alerts {
  background: radial-gradient(circle at top right, rgba(244, 63, 94, 0.08), transparent);
  border-color: rgba(244, 63, 94, 0.5);
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.1); }
  70% { box-shadow: 0 0 0 10px rgba(244, 63, 94, 0); }
  100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
}

.card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.card h3 {
  font-family: var(--font-title);
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0;
  border-left: 3px solid var(--color-secondary);
  padding-left: 0.75rem;
}

.card-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: -0.25rem 0 0 0;
}

/* Form and Inputs */
.muni-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
}

.form-group input, .form-group textarea, .form-group select {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.9rem;
  outline: none;
  box-sizing: border-box;
}

.form-group input:focus, .form-group textarea:focus {
  border-color: var(--color-secondary);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Lists and Tables */
.split-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .split-grid {
    grid-template-columns: 1fr;
  }
}

.grid-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.map-container {
  height: 280px;
  width: 100%;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: #111;
}

.operatives-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.operative-item {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.04);
  padding: 1rem;
  border-radius: 12px;
}

.op-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.op-header h4 {
  margin: 0;
  font-size: 0.95rem;
}

.op-status {
  font-size: 0.7rem;
  background: rgba(16, 185, 129, 0.15);
  color: var(--color-success);
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  font-weight: 700;
}

.op-desc {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin: 0 0 0.5rem 0;
}

.op-footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.table-wrapper {
  overflow-x: auto;
}

.muni-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.85rem;
}

.muni-table th, .muni-table td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.muni-table th {
  color: var(--color-text-muted);
  font-weight: 700;
  text-transform: uppercase;
}

.empty-state-sm {
  text-align: center;
  padding: 2rem;
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

/* Alerts */
.alert {
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.85rem;
  margin-bottom: 1.5rem;
}

.alert-success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  color: var(--color-success);
}

/* Buttons */
.btn {
  font-weight: 600;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: inline-block;
  text-align: center;
  text-decoration: none;
  font-size: 0.85rem;
}

.btn-primary {
  background: var(--color-secondary);
  color: white;
}

.btn-primary:hover {
  background: #0d9488;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-danger {
  background: var(--color-accent);
  color: white;
}

.btn-block {
  width: 100%;
}

.btn-sm {
  font-size: 0.75rem;
  padding: 0.35rem 0.75rem;
}
</style>
