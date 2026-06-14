<template>
  <div class="qr-pass-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <main class="main-content">
      <!-- Success Flash Alert -->
      <div class="flash-alert success-alert" v-if="$page.props.flash.success">
        <div class="alert-content">
          <span class="alert-icon">✓</span>
          <div>
            <div>{{ $page.props.flash.success }}</div>
            <div v-if="$page.props.flash.checkout_url" style="margin-top: 0.75rem;">
              <a :href="$page.props.flash.checkout_url" target="_blank" class="btn btn-primary btn-sm" style="background: #0d9488; color: white;">
                💳 Completar Pago Seguro en Flow.cl
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="pass-card">
        <!-- Badge Status lost / adopted -->
        <div class="lost-banner" v-if="pet.status === 'rescatado'">
          🚨 MASCOTA REPORTADA COMO EXTRAVIADA / BUSCANDO HOGAR
        </div>

        <div class="pass-grid">
          <!-- Left Identity Info Column -->
          <div class="identity-section">
            <div class="avatar-box">
              <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="qr-avatar" />
              <span v-else class="avatar-emoji">{{ pet.species === 'perro' ? '🐶' : (pet.species === 'gato' ? '🐱' : '🐾') }}</span>
              <h2>{{ pet.name }}</h2>
              <span class="microchip-label" v-if="pet.microchip_code">
                📟 Chip: {{ pet.microchip_code }}
              </span>
            </div>

            <div class="vital-specs">
              <div class="spec-item">
                <span class="spec-title">Especie / Raza</span>
                <span class="spec-val text-capitalize">{{ pet.species }} - {{ pet.breed || 'Mestizo' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-title">Edad / Género</span>
                <span class="spec-val text-capitalize">{{ pet.age_text || 'Sin especificar' }} - {{ pet.gender }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-title">Estado de Tenencia</span>
                <span class="spec-val text-capitalize">{{ formatStatus(pet.status) }}</span>
              </div>
              <div class="spec-item" v-if="pet.sponsorships_count > 0">
                <span class="spec-title">Padrinos Apoyando</span>
                <span class="spec-val">💖 {{ pet.sponsorships_count }} padrinos activos</span>
              </div>
            </div>
          </div>

          <!-- Middle QR & Emergency Locator Column -->
          <div class="locator-section">
            <div class="qr-preview-box">
              <div class="qr-mock">
                <!-- Simple SVG QR Pattern Mockup -->
                <svg width="120" height="120" viewBox="0 0 100 100" class="qr-svg">
                  <path d="M0 0h30v30H0zm40 0h20v20H40zm30 0h30v30H70zM0 40h20v20H0zm30 30h40v30H30zM80 40h20v40H80zM0 80h20v20H0z" fill="var(--color-primary)" />
                  <path d="M10 10h10v10H10zm70 0h10v10H80zM10 80h10v10H10z" fill="#ffffff" />
                  <circle cx="50" cy="50" r="10" fill="var(--color-secondary)" />
                </svg>
                <span class="qr-hint">Placa Física Oficial</span>
              </div>
            </div>

            <!-- Emergency SOS Geolocation Trigger -->
            <div class="emergency-trigger-box">
              <h4>¿Encontraste a {{ pet.name }} en la calle?</h4>
              <p>Ayúdanos a localizarlo. Si es posible, ingresa tus datos de contacto y presiona el botón SOS para enviar las coordenadas GPS actuales.</p>
              
              <div class="form-group" style="margin-top: 0.75rem; text-align: left;">
                <label for="reporter_phone" style="font-size: 0.8rem; font-weight: 600; color: #fda4af;">Tu Teléfono (Opcional)</label>
                <input 
                  type="text" 
                  id="reporter_phone" 
                  v-model="reporterPhone" 
                  placeholder="Ej: +56 9 1234 5678"
                  style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(244, 63, 94, 0.2); border-radius: 8px; padding: 0.5rem; color: white; width: 100%; box-sizing: border-box; outline: none; margin-top: 0.25rem;"
                />
              </div>

              <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0.75rem; text-align: left;">
                <label for="reporter_message" style="font-size: 0.8rem; font-weight: 600; color: #fda4af;">Nota de ayuda / Dirección (Opcional)</label>
                <textarea 
                  id="reporter_message" 
                  v-model="reporterMessage" 
                  placeholder="Ej: Lo vi en el parque frente al almacén..."
                  rows="2"
                  style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(244, 63, 94, 0.2); border-radius: 8px; padding: 0.5rem; color: white; width: 100%; box-sizing: border-box; outline: none; resize: vertical; margin-top: 0.25rem;"
                ></textarea>
              </div>

              <div class="gps-status" v-if="gpsLoading">
                ⏳ Obteniendo ubicación GPS...
              </div>
              <div class="gps-error" v-if="gpsError">
                ✕ {{ gpsError }}
              </div>

              <button 
                class="btn btn-danger btn-block btn-sos" 
                @click="locateAndReport"
                :disabled="gpsLoading"
              >
                🚨 REPORTAR SOS (Enviar GPS y Datos)
              </button>
            </div>
          </div>
        </div>

        <!-- Medical/Ficha Section -->
        <div class="medical-section">
          <h3>📋 Ficha Médica y Cuidados Especiales</h3>
          
          <div class="medical-grid">
            <!-- Vacunas -->
            <div class="med-card">
              <h5>💉 Calendario de Vacunas</h5>
              <ul class="med-list">
                <li class="checked">✓ Antirrábica (Al día)</li>
                <li class="checked">✓ Óctuple / Triple Felina (Al día)</li>
                <li class="unchecked">✕ Desparasitación Interna (Vence pronto)</li>
              </ul>
            </div>

            <!-- Características -->
            <div class="med-card">
              <h5>🧠 Comportamiento y Convivencia</h5>
              <ul class="med-list">
                <li class="checked" v-if="pet.characteristics?.kids">✓ Apto para convivir con niños</li>
                <li class="checked" v-if="pet.characteristics?.dogs">✓ Sociable con otros perros</li>
                <li class="checked" v-if="pet.characteristics?.cats">✓ Amigable con gatos</li>
                <li class="unchecked" v-if="!pet.characteristics?.alone">✕ Requiere compañía (Ansiedad)</li>
                <li class="checked" v-else>✓ Puede pasar tiempo solo</li>
              </ul>
            </div>
          </div>

          <!-- Description -->
          <div class="description-box" v-if="pet.description">
            <h5>Sobre mi personalidad:</h5>
            <p>{{ pet.description }}</p>
          </div>
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
  name: 'QrPass',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    pet: Object,
  },
  setup(props) {
    const gpsLoading = ref(false)
    const gpsError = ref(null)
    const reporterPhone = ref('')
    const reporterMessage = ref('')

    const sendReport = (lat, lng) => {
      router.post(`/pets/${props.pet.id}/report-found`, {
        latitude: lat,
        longitude: lng,
        reporter_phone: reporterPhone.value,
        reporter_message: reporterMessage.value,
      }, {
        onFinish: () => {
          gpsLoading.value = false
        }
      })
    }

    const locateAndReport = () => {
      gpsLoading.value = true
      gpsError.value = null

      if (!navigator.geolocation) {
        gpsError.value = 'Tu navegador no soporta geolocalización. Enviando reporte básico...'
        sendReport(0, 0)
        return
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          // Coordenadas obtenidas
          const lat = position.coords.latitude
          const lng = position.coords.longitude
          sendReport(lat, lng)
        },
        (error) => {
          // GPS falló o fue denegado, enviamos de contingencia con coordenadas (0,0)
          gpsError.value = 'GPS no disponible o permiso denegado. Enviando reporte de contingencia...'
          sendReport(0, 0)
        },
        {
          enableHighAccuracy: true,
          timeout: 8000,
        }
      )
    }

    const formatStatus = (statusVal) => {
      const map = {
        rescatado: 'Rescatado (Buscando Hogar)',
        en_transito: 'En Hogar Temporal',
        en_adopcion: 'En Adopción Activa',
        adoptado: 'Adoptado Definitivamente',
      }
      return map[statusVal] || statusVal
    }

    return {
      gpsLoading,
      gpsError,
      reporterPhone,
      reporterMessage,
      locateAndReport,
      formatStatus,
    }
  }
}
</script>

<style scoped>
.qr-pass-container {
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  background-color: var(--color-bg);
  color: var(--color-text-main);
  font-family: var(--font-body);
}

/* Background Blobs */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.12;
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
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -150px;
  right: -100px;
}

/* Header */
.header {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1000px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  margin: 0 auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
  background: rgba(13, 148, 136, 0.15);
  border: 1px solid rgba(13, 148, 136, 0.3);
  color: var(--color-secondary);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
}

/* Main Content */
.main-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 850px;
  margin: 0 auto;
  padding: 3rem 1.5rem;
  box-sizing: border-box;
}

/* Flash alerts */
.flash-alert {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #a7f3d0;
  padding: 1.25rem 1.5rem;
  border-radius: 14px;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.alert-content {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.alert-icon {
  font-size: 1.25rem;
  font-weight: 700;
}

/* Pass Card Design */
.pass-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  box-sizing: border-box;
}

.lost-banner {
  background: linear-gradient(90deg, #f43f5e 0%, #fa5732 100%);
  color: white;
  text-align: center;
  padding: 1rem;
  font-family: var(--font-title);
  font-weight: 800;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
}

.pass-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  padding: 3rem;
  gap: 3rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

@media (max-width: 600px) {
  .pass-grid {
    grid-template-columns: 1fr;
    padding: 2rem;
    gap: 2rem;
  }
}

/* Identity Section */
.identity-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.avatar-box {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.75rem;
}

.qr-avatar {
  width: 90px;
  height: 90px;
  border-radius: 24px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.avatar-emoji {
  font-size: 4rem;
  background: rgba(255, 255, 255, 0.03);
  width: 90px;
  height: 90px;
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.avatar-box h2 {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
}

.microchip-label {
  font-family: monospace;
  font-size: 0.85rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  color: var(--color-primary);
  font-weight: 600;
}

.vital-specs {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.spec-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  border-left: 2px solid var(--color-primary);
  padding-left: 0.75rem;
}

.spec-title {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.spec-val {
  font-weight: 700;
  font-size: 1.05rem;
}

.text-capitalize {
  text-transform: capitalize;
}

/* Locator Section */
.locator-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  justify-content: center;
}

.qr-preview-box {
  display: flex;
  justify-content: center;
}

.qr-mock {
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.qr-svg {
  padding: 0.5rem;
  background: #ffffff;
  border-radius: 12px;
}

.qr-hint {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
}

.emergency-trigger-box {
  background: rgba(244, 63, 94, 0.06);
  border: 1px solid rgba(244, 63, 94, 0.15);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.emergency-trigger-box h4 {
  font-family: var(--font-title);
  color: var(--color-accent);
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.emergency-trigger-box p {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

.gps-status {
  font-size: 0.8rem;
  color: var(--color-primary);
  font-weight: 600;
}

.gps-error {
  font-size: 0.8rem;
  color: var(--color-accent);
  font-weight: 600;
}

/* Medical / Information Ficha */
.medical-section {
  padding: 3rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

@media (max-width: 600px) {
  .medical-section {
    padding: 2rem;
  }
}

.medical-section h3 {
  font-family: var(--font-title);
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0;
}

.medical-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 576px) {
  .medical-grid {
    grid-template-columns: 1fr;
  }
}

.med-card {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  padding: 1.5rem;
}

.med-card h5 {
  font-family: var(--font-title);
  font-size: 1rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  color: var(--color-text-main);
}

.med-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  font-size: 0.85rem;
}

.med-list li.checked {
  color: var(--color-secondary);
  font-weight: 600;
}

.med-list li.unchecked {
  color: var(--color-text-muted);
}

.description-box {
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 1.5rem;
}

.description-box h5 {
  font-family: var(--font-title);
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.description-box p {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
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

.btn-danger {
  background: var(--color-accent);
  color: white;
  box-shadow: 0 4px 14px rgba(244, 63, 94, 0.3);
}

.btn-danger:hover {
  background: #f43f5e;
  transform: translateY(-2px);
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}

.btn-block {
  width: 100%;
}
</style>
