<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">

        <!-- Section Header -->
        <div class="section-header">
          <h1>Radar Pet-Friendly & Paseos en Manada</h1>
          <p class="subtitle">Descubre lugares recomendados por la comunidad o coordina salidas grupales para socializar perros compatibles.</p>
        </div>

        <!-- Success Alert -->
        <div class="alert alert-success" v-if="flash.success">
          {{ flash.success }}
        </div>

        <div class="radar-grid">
          <!-- Left Column: Map & Forms -->
          <div class="radar-column">
            <!-- Map Card -->
            <div class="card map-card-full">
              <div class="map-header">
                <h3>🗺️ Mapa Colaborativo de la Comunidad</h3>
                <span class="map-hint">Haz clic en el mapa para capturar coordenadas lat/lng</span>
              </div>
              <div id="radar-map" class="radar-map-container"></div>
            </div>

            <!-- Form Tab Selector -->
            <div class="card form-card">
              <div class="tabs-nav">
                <button class="tab-btn" :class="{ active: activeForm === 'spot' }" @click="activeForm = 'spot'">
                  📍 Agregar Lugar Pet-Friendly
                </button>
                <button class="tab-btn" :class="{ active: activeForm === 'walk' }" @click="activeForm = 'walk'">
                  🦮 Coordinar Paseo en Manada
                </button>
              </div>

              <!-- Form 1: Add Spot -->
              <div v-show="activeForm === 'spot'" class="tab-form-content">
                <form @submit.prevent="submitSpot" class="radar-form">
                  <div class="form-group">
                    <label for="spot-name">Nombre del Lugar</label>
                    <input type="text" id="spot-name" v-model="spotForm.name" placeholder="Ej. Parque de Mascotas, Cafetería Canela..." required />
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="spot-type">Tipo de Establecimiento</label>
                      <select id="spot-type" v-model="spotForm.type" required>
                        <option value="parque">🌳 Parque / Espacio Público</option>
                        <option value="restaurant">🍔 Cafetería / Restaurant</option>
                        <option value="hotel">🏨 Hotel / Hospedaje</option>
                        <option value="veterinaria">🏥 Veterinaria / Clínica</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="spot-address">Dirección</label>
                      <input type="text" id="spot-address" v-model="spotForm.address" placeholder="Calle, número..." />
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="spot-lat">Latitud</label>
                      <input type="number" id="spot-lat" step="any" v-model.number="spotForm.latitude" required />
                    </div>
                    <div class="form-group">
                      <label for="spot-lng">Longitud</label>
                      <input type="number" id="spot-lng" step="any" v-model.number="spotForm.longitude" required />
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="spot-desc">Descripción / Reseña rápida</label>
                    <textarea id="spot-desc" v-model="spotForm.description" rows="3" placeholder="Cuéntale a la comunidad qué servicios especiales ofrecen para mascotas..."></textarea>
                  </div>

                  <p style="font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.4; margin-top: 0.5rem; margin-bottom: 0.25rem;">
                    ℹ️ <strong>Aviso:</strong> Todos los lugares sugeridos son revisados por la comunidad. Intentos de extorsión comercial o publicación de información falsa serán sancionados con la eliminación permanente de la cuenta.
                  </p>

                  <button type="submit" class="btn btn-primary btn-block" :disabled="spotForm.processing">
                    {{ spotForm.processing ? 'Registrando...' : '✓ Publicar Lugar en el Radar' }}
                  </button>
                </form>
              </div>

              <!-- Form 2: Coordinate Walk -->
              <div v-show="activeForm === 'walk'" class="tab-form-content">
                <form @submit.prevent="submitWalk" class="radar-form">
                  <div class="form-group">
                    <label for="walk-title">Título del Encuentro</label>
                    <input type="text" id="walk-title" v-model="walkForm.title" placeholder="Ej. Paseo de Cachorros Golden, Caminata Matutina..." required />
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="walk-neighborhood">Barrio / Comuna</label>
                      <input type="text" id="walk-neighborhood" v-model="walkForm.neighborhood" placeholder="Ej. Providencia, Las Condes..." required />
                    </div>

                    <div class="form-group">
                      <label for="walk-date">Fecha y Hora</label>
                      <input type="datetime-local" id="walk-date" v-model="walkForm.walk_date" required />
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group">
                      <label for="walk-lat">Latitud del punto de encuentro</label>
                      <input type="number" id="walk-lat" step="any" v-model.number="walkForm.latitude" required />
                    </div>
                    <div class="form-group">
                      <label for="walk-lng">Longitud del punto de encuentro</label>
                      <input type="number" id="walk-lng" step="any" v-model.number="walkForm.longitude" required />
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="walk-desc">Punto de encuentro e instrucciones de seguridad</label>
                    <textarea id="walk-desc" v-model="walkForm.description" rows="3" placeholder="Ej. Nos juntaremos al costado del anfiteatro. Traer correa de 3 metros y agua. Apto para perros sociables..."></textarea>
                  </div>

                  <div class="form-group check-group" style="display: flex; flex-direction: row; gap: 0.5rem; align-items: flex-start; margin-top: 0.5rem; margin-bottom: 0.25rem;">
                    <input type="checkbox" id="walk-agreement" v-model="walkForm.agreement" style="width: auto; margin-top: 0.2rem;" required />
                    <label for="walk-agreement" style="font-size: 0.8rem; font-weight: 500; cursor: pointer; color: var(--color-text-main);">
                      Declaro que mi mascota cuenta con vacunas al día (Óctuple/Triple Felina y Antirrábica) y no presenta conductas reactivas. Libero de responsabilidad a Adopta por cualquier incidente.
                    </label>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block" :disabled="walkForm.processing">
                    {{ walkForm.processing ? 'Coordinando...' : '✓ Coordinar Evento (+15 Huellas)' }}
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Right Column: List of walks and places -->
          <div class="radar-column">
            <!-- Walks Card -->
            <div class="card walks-card">
              <h3>🦮 Próximos Paseos en Manada</h3>
              <p class="card-desc">Socializa a tu mascota en un entorno seguro coordinado con vecinos.</p>

              <div class="walks-list" v-if="walks.length > 0">
                <div class="walk-item-card" v-for="walk in walks" :key="walk.id">
                  <div class="walk-header">
                    <h4>{{ walk.title }}</h4>
                    <span class="walk-neighborhood-badge">{{ walk.neighborhood }}</span>
                  </div>
                  <p class="walk-desc">"{{ walk.description }}"</p>
                  
                  <div class="walk-meta">
                    <span class="meta-date">📅 {{ formatDateTime(walk.walk_date) }}</span>
                    <button class="btn btn-secondary btn-sm" @click="focusOnCoordinates(walk.latitude, walk.longitude)">
                      📍 Ver Lugar
                    </button>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                No hay paseos comunitarios programados en los próximos días. ¡Organiza el primero!
              </div>
            </div>

            <!-- Pet friendly spots quick index -->
            <div class="card spots-card">
              <h3>⭐ Lugares Destacados</h3>
              <p class="card-desc">Listado rápido de comercios y parques con sello Pet-Friendly.</p>

              <div class="spots-list" v-if="spots.length > 0">
                <div class="spot-item-row" v-for="spot in spots" :key="spot.id" @click="focusOnCoordinates(spot.latitude, spot.longitude)">
                  <div class="spot-icon-wrapper" :class="'type-' + spot.type">
                    {{ getSpotIcon(spot.type) }}
                  </div>
                  <div class="spot-row-info">
                    <h5>{{ spot.name }}</h5>
                    <span class="spot-row-addr">{{ spot.address || 'Ubicación en mapa' }}</span>
                  </div>
                </div>
              </div>
              <div class="empty-state-sm" v-else>
                Aún no hay comercios o parques recomendados en la red.
              </div>
            </div>
          </div>
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Radar',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    spots: Array,
    walks: Array,
  },
  setup(props) {
    const activeForm = ref('spot')
    const page = usePage()
    const flash = computed(() => page.props.flash || {})
    
    // Formularios
    const spotForm = useForm({
      name: '',
      type: 'parque',
      latitude: '',
      longitude: '',
      description: '',
      address: '',
    })

    const walkForm = useForm({
      title: '',
      neighborhood: '',
      walk_date: '',
      latitude: '',
      longitude: '',
      description: '',
      agreement: false,
    })

    let mapInstance = null

    onMounted(() => {
      // Coordenadas default de Santiago de Chile o primera spot
      const defLat = props.spots.length > 0 ? parseFloat(props.spots[0].latitude) : -33.4489
      const defLng = props.spots.length > 0 ? parseFloat(props.spots[0].longitude) : -70.6693

      setTimeout(() => {
        if (!document.getElementById('radar-map')) return

        // Iniciar mapa Leaflet
        mapInstance = L.map('radar-map').setView([defLat, defLng], 12)

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
          attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
          subdomains: 'abcd',
          maxZoom: 20
        }).addTo(mapInstance)

        // Intentar centrar el mapa en la geolocalización real del usuario (soporte para todo Chile)
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            (position) => {
              const uLat = position.coords.latitude
              const uLng = position.coords.longitude
              // Centrar el mapa con un zoom más cerrado en su ubicación actual
              mapInstance.setView([uLat, uLng], 13, { animate: true })
            },
            (error) => {
              console.log('User geolocation error or denied:', error.message)
            },
            {
              enableHighAccuracy: false,
              timeout: 5000,
              maximumAge: 60000
            }
          )
        }

        // Registrar click en el mapa para capturar coordenadas
        mapInstance.on('click', (e) => {
          const lat = parseFloat(e.latlng.lat.toFixed(6))
          const lng = parseFloat(e.latlng.lng.toFixed(6))

          if (activeForm.value === 'spot') {
            spotForm.latitude = lat
            spotForm.longitude = lng
          } else {
            walkForm.latitude = lat
            walkForm.longitude = lng
          }
        })

        // Dibujar spots pet friendly
        props.spots.forEach(spot => {
          const sLat = parseFloat(spot.latitude)
          const sLng = parseFloat(spot.longitude)
          if (isNaN(sLat) || isNaN(sLng)) return

          const colors = {
            parque: '#10b981',      // Verde
            restaurant: '#f59e0b',  // Naranja
            hotel: '#0ea5e9',       // Azul
            veterinaria: '#ef4444',  // Rojo
          }
          const icon = getSpotIcon(spot.type)

          const circle = L.circleMarker([sLat, sLng], {
            radius: 9,
            fillColor: colors[spot.type] || '#8b5cf6',
            color: '#ffffff',
            weight: 1.5,
            opacity: 1,
            fillOpacity: 0.8
          }).addTo(mapInstance)

          circle.bindPopup(`
            <div style="color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; line-height: 1.4;">
              <strong style="font-size: 14px;">${icon} ${spot.name}</strong><br/>
              <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">${spot.type}</span><br/>
              <span style="display: block; margin-top: 5px;">${spot.description || ''}</span>
              <small style="color: #94a3b8; display: block; margin-top: 5px;">📍 ${spot.address || ''}</small>
            </div>
          `)
        })

        // Dibujar paseos en manada coordinados
        props.walks.forEach(walk => {
          const wLat = parseFloat(walk.latitude)
          const wLng = parseFloat(walk.longitude)
          if (isNaN(wLat) || isNaN(wLng)) return

          const circle = L.circleMarker([wLat, wLng], {
            radius: 12,
            fillColor: '#8b5cf6', // Púrpura principal
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.7,
            dashArray: '3, 3'
          }).addTo(mapInstance)

          circle.bindPopup(`
            <div style="color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; line-height: 1.4; width: 180px;">
              <strong style="font-size: 14px;">🦮 ${walk.title}</strong><br/>
              <span style="color: #8b5cf6; font-weight: 700;">Paseo en Manada (${walk.neighborhood})</span><br/>
              <span style="display: block; margin-top: 5px;">${walk.description || ''}</span>
              <small style="color: #64748b; display: block; margin-top: 5px;">📅 ${formatDateTime(walk.walk_date)}</small>
            </div>
          `)
        })
      }, 400)
    })

    const focusOnCoordinates = (lat, lng) => {
      if (mapInstance) {
        mapInstance.setView([parseFloat(lat), parseFloat(lng)], 14, { animate: true })
      }
    }

    const submitSpot = () => {
      spotForm.post('/community/spots', {
        onSuccess: () => {
          spotForm.reset()
          // Recargar página para re-dibujar marcadores
          window.location.reload()
        }
      })
    }

    const submitWalk = () => {
      walkForm.post('/community/walks', {
        onSuccess: () => {
          walkForm.reset()
          window.location.reload()
        }
      })
    }

    const getSpotIcon = (type) => {
      const icons = {
        parque: '🌳',
        restaurant: '☕',
        hotel: '🏨',
        veterinaria: '🏥',
      }
      return icons[type] || '📍'
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

    return {
      activeForm,
      flash,
      spotForm,
      walkForm,
      submitSpot,
      submitWalk,
      getSpotIcon,
      formatDateTime,
      focusOnCoordinates,
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
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
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

/* Radar Grid */
.radar-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 2rem;
}

@media (max-width: 968px) {
  .radar-grid {
    grid-template-columns: 1fr;
  }
}

.radar-column {
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

/* Leaflet Map */
.map-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.map-hint {
  font-size: 0.8rem;
  color: var(--color-secondary);
  font-weight: 600;
}

.radar-map-container {
  height: 380px;
  width: 100%;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: #111;
}

/* Tabs & Forms */
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

.radar-form {
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

.form-group input, 
.form-group select, 
.form-group textarea {
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

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.form-group select option {
  background-color: var(--color-bg);
  color: white;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 480px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

/* Walks List */
.walks-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.walk-item-card {
  background: rgba(255, 255, 255, 0.015);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.walk-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
}

.walk-header h4 {
  font-family: var(--font-title);
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-main);
}

.walk-neighborhood-badge {
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  white-space: nowrap;
}

.walk-desc {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

.walk-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 0.75rem;
}

.meta-date {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* Spots List */
.spots-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 350px;
  overflow-y: auto;
}

.spot-item-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.03);
  cursor: pointer;
  transition: all 0.2s ease;
}

.spot-item-row:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  transform: translateX(3px);
}

.spot-icon-wrapper {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.spot-icon-wrapper.type-parque { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); }
.spot-icon-wrapper.type-restaurant { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); }
.spot-icon-wrapper.type-hotel { background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); }
.spot-icon-wrapper.type-veterinaria { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); }

.spot-row-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.spot-row-info h5 {
  font-family: var(--font-title);
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-main);
}

.spot-row-addr {
  font-size: 0.75rem;
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
</style>
