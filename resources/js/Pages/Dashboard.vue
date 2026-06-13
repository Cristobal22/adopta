<template>
  <div class="dashboard-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <main class="main-content" style="width: 100%; display: flex; justify-content: center; align-items: center; flex-grow: 1; margin-top: 2rem; margin-bottom: 2rem; box-sizing: border-box; padding: 0 1.5rem; position: relative; z-index: 10;">
      <div class="dashboard-card">
      <div class="welcome-box">
        <h1>Hola, <span class="highlight-text">{{ user.name }}</span>!</h1>
        <p class="role-desc">Tu rol en el ecosistema es: <strong class="role-name text-capitalize">{{ user.role === 'transito' ? 'hogar temporal (tránsito)' : user.role }}</strong></p>
        
        <!-- Status indicator for adopters and transit homes -->
        <div class="status-indicator" v-if="['adoptante', 'transito'].includes(user.role)">
          <span class="dot" :class="{ verified: user.status === 'verificado' }"></span>
          Perfil de {{ user.role === 'adoptante' ? 'adoptante' : 'hogar temporal' }} {{ user.status === 'verificado' ? 'Verificado' : 'Completo al 50%' }}
        </div>
      </div>

      <!-- PWA Install Promotion Banner -->
      <div class="pwa-install-banner" v-if="showPwaBanner">
        <div class="pwa-banner-content">
          <span class="pwa-icon-large">📲</span>
          <div class="pwa-banner-text">
            <h4>Instalar la App de Adopta</h4>
            <p>Accede de forma rápida, recibe alertas en tiempo real y colabora de forma ágil.</p>
          </div>
        </div>
        <button class="btn btn-primary btn-sm pwa-install-btn" @click="installPwa">Instalar App</button>
      </div>

      <!-- Action hub grid -->
      <div class="hub-grid">
        <!-- Adopter, Transit & Donor Hubs -->
        <template v-if="['adoptante', 'transito', 'donante'].includes(user.role)">
          <Link href="/adopter/profile" class="hub-item item-interactive" v-if="user.role !== 'donante'">
            <span class="hub-icon">📋</span>
            <div class="hub-info">
              <h4>Estilo de Vida</h4>
              <p>Llena o actualiza tus datos del hogar para el algoritmo de match.</p>
            </div>
          </Link>

          <Link href="/pets" class="hub-item item-interactive">
            <span class="hub-icon">🐶</span>
            <div class="hub-info">
              <h4>Mascotas en Adopción</h4>
              <p>Revisa la lista de animales disponibles y su compatibilidad.</p>
            </div>
          </Link>

          <Link href="/adoptions" class="hub-item item-interactive" v-if="user.role !== 'donante'">
            <span class="hub-icon">🤝</span>
            <div class="hub-info">
              <h4>Mis Solicitudes</h4>
              <p>Seguimiento del estado de tus solicitudes y firmas de contratos.</p>
            </div>
          </Link>

          <Link href="/logistics" class="hub-item item-interactive">
            <span class="hub-icon">🚗</span>
            <div class="hub-info">
              <h4>Uber Solidario & Recursos</h4>
              <p>Ayuda a trasladar insumos o solicita traslados voluntarios.</p>
            </div>
          </Link>

          <Link href="/bazar" class="hub-item item-interactive">
            <span class="hub-icon">🛍️</span>
            <div class="hub-info">
              <h4>Bazar & Club de Huellas</h4>
              <p>Canjea tus huellas por descuentos y apoya a emprendedores solidarios.</p>
            </div>
          </Link>

          <Link href="/community/radar" class="hub-item item-interactive">
            <span class="hub-icon">🗺️</span>
            <div class="hub-info">
              <h4>Radar & Paseos Comunitarios</h4>
              <p>Busca lugares pet-friendly y participa de paseos en manada coordinados.</p>
            </div>
          </Link>

          <Link href="/donations/traceability" class="hub-item item-interactive">
            <span class="hub-icon">💖</span>
            <div class="hub-info">
              <h4>Donaciones & Trazabilidad</h4>
              <p>Apoya con un kit de bienvenida o aporta fondos de forma transparente.</p>
            </div>
          </Link>
        </template>

        <!-- Administrative hubs -->
        <template v-else-if="['admin', 'fundacion', 'rescatista'].includes(user.role)">
          <Link href="/pets" class="hub-item item-interactive">
            <span class="hub-icon">⚙️</span>
            <div class="hub-info">
              <h4>Gestión de Mascotas</h4>
              <p>Agrega, edita y administra el historial clínico de las mascotas.</p>
            </div>
          </Link>

          <Link href="/adoptions" class="hub-item item-interactive">
            <span class="hub-icon">📂</span>
            <div class="hub-info">
              <h4>Solicitudes de Adopción</h4>
              <p>Evalúa postulaciones, revisa porcentajes de match y gestiona contratos.</p>
            </div>
          </Link>

          <Link href="/logistics" class="hub-item item-interactive">
            <span class="hub-icon">🚗</span>
            <div class="hub-info">
              <h4>Uber Solidario & Recursos</h4>
              <p>Gestiona solicitudes de traslado y catálogo de donaciones.</p>
            </div>
          </Link>

          <Link href="/bazar" class="hub-item item-interactive">
            <span class="hub-icon">🛍️</span>
            <div class="hub-info">
              <h4>Bazar & Club de Huellas</h4>
              <p>Vitrina comercial para emprendimientos locales y canje de premios.</p>
            </div>
          </Link>

          <Link href="/community/radar" class="hub-item item-interactive">
            <span class="hub-icon">🗺️</span>
            <div class="hub-info">
              <h4>Radar & Paseos Comunitarios</h4>
              <p>Busca lugares pet-friendly y participa de paseos en manada coordinados.</p>
            </div>
          </Link>

          <Link href="/backoffice/contracts-vault" class="hub-item item-interactive">
            <span class="hub-icon">📂</span>
            <div class="hub-info">
              <h4>Bóveda de Contratos</h4>
              <p>Auditoría legal de firmas, descarga de contratos y control de cláusulas.</p>
            </div>
          </Link>

          <Link href="/backoffice/audit-logs" class="hub-item item-interactive">
            <span class="hub-icon">🔒</span>
            <div class="hub-info">
              <h4>Bitácora de Auditoría</h4>
              <p>Inspección de acciones administrativas, logs de seguridad y modificaciones.</p>
            </div>
          </Link>

          <Link href="/backoffice/moderation" class="hub-item item-interactive">
            <span class="hub-icon">✨</span>
            <div class="hub-info">
              <h4>Moderación de Historias</h4>
              <p>Revisa y aprueba bitácoras de evolución públicas compartidas por adoptantes.</p>
            </div>
          </Link>

          <Link href="/donations/traceability" class="hub-item item-interactive">
            <span class="hub-icon">💖</span>
            <div class="hub-info">
              <h4>Donaciones & Trazabilidad</h4>
              <p>Monitoreo transparente de donaciones, reportes de impacto y apadrinamientos.</p>
            </div>
          </Link>
        </template>
      </div>

      <!-- Mis Mascotas Apadrinadas -->
      <div class="sponsored-section" v-if="['adoptante', 'transito', 'donante'].includes(user.role) && sponsoredPets.length > 0">
        <h3 class="sponsored-title">💖 Mis Mascotas Apadrinadas</h3>
        <p class="sponsored-desc">Sigue de cerca a las mascotas que estás apoyando financieramente.</p>
        
        <div class="sponsored-grid">
          <div class="sponsored-card" v-for="pet in sponsoredPets" :key="pet.id">
            <div class="sponsored-card-header">
              <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="sponsored-photo" />
              <span v-else class="sponsored-emoji">🐾</span>
              <div>
                <span class="sponsored-name">{{ pet.name }}</span>
                <span class="sponsored-status text-capitalize">{{ formatStatus(pet.status) }}</span>
              </div>
            </div>
            <div class="sponsored-actions" style="display: flex; gap: 0.4rem;">
              <Link :href="'/pets/' + pet.id + '/qr-pass'" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.6rem;">
                🎫 Pasaporte
              </Link>
              <Link :href="'/pets/' + pet.id + '/story'" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.6rem; color: var(--color-primary); border-color: rgba(255, 107, 74, 0.25);">
                📖 Historia
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Mis Logros y Metas Colectivas -->
      <div class="sponsored-section achievements-section">
        <h3 class="sponsored-title">🏆 Logros y Metas de la Red</h3>
        <p class="sponsored-desc">Consulta tus medallas virtuales obtenidas y el progreso de los hitos colectivos de la red.</p>
        
        <!-- Medallas del Usuario -->
        <div class="badges-row-container" style="margin-top: 0.75rem;">
          <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Mis Medallas ({{ badges ? badges.length : 0 }})</h4>
          <div v-if="badges && badges.length > 0" class="badges-grid" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <div class="badge-item-card" v-for="badge in badges" :key="badge.id" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); padding: 0.5rem 1rem; border-radius: 12px; display: flex; align-items: center; gap: 0.5rem;">
              <span class="badge-icon-item" style="font-size: 1.25rem;">{{ badge.badge_icon }}</span>
              <span class="badge-name-item" style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-main);">{{ badge.badge_name }}</span>
            </div>
          </div>
          <div v-else style="background: rgba(255, 255, 255, 0.01); border: 1px dashed rgba(255, 255, 255, 0.08); padding: 1rem; border-radius: 12px; text-align: center; color: var(--color-text-muted); font-size: 0.85rem;">
            Aún no has desbloqueado medallas. ¡Completa tu perfil o realiza acciones para ganar medallas!
          </div>
        </div>

        <!-- Hitos Globales -->
        <div class="global-milestones-container" style="margin-top: 1.25rem;">
          <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-text-main);">Progreso de Metas Globales</h4>
          <div v-if="globalMilestones && globalMilestones.length > 0" class="milestones-grid" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="milestone-progress-card" v-for="m in globalMilestones" :key="m.id" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); padding: 1rem; border-radius: 16px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--color-text-main);">{{ m.name }}</span>
                <span style="font-size: 0.8rem; font-weight: 700;" :style="{ color: m.is_reached ? '#10b981' : '#f59e0b' }">
                  {{ m.is_reached ? '¡Alcanzado! 🎉' : `${m.current_value} / ${m.threshold}` }}
                </span>
              </div>
              <div class="progress-bar-container" style="width: 100%; height: 6px; background: rgba(255, 255, 255, 0.05); border-radius: 99px; overflow: hidden;">
                <div class="progress-bar" :style="{ width: Math.min(100, (m.current_value / m.threshold) * 100) + '%' }" style="height: 100%; background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-secondary) 100%); border-radius: 99px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mapa de Rescates y Abandonos (Leaflet.js) -->
      <div class="map-card" v-if="['admin', 'fundacion', 'rescatista'].includes(user.role) && pets.length > 0">
        <h3 class="map-title">🗺️ Mapa de Rescates y Abandonos</h3>
        <p class="map-desc">Monitoreo geolocalizado en tiempo real para organizar operativos y campañas de esterilización focalizadas.</p>
        <div id="abandon-map" class="abandon-map-container"></div>
      </div>
      </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import Header from '../Components/Header.vue'
import Footer from '../Components/Footer.vue'

export default {
  name: 'Dashboard',
  components: {
    Header,
    Footer,
    Link,
  },
  props: {
    user: Object,
    pets: {
      type: Array,
      default: () => []
    },
    sponsoredPets: {
      type: Array,
      default: () => []
    },
    badges: {
      type: Array,
      default: () => []
    },
    globalMilestones: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    const logout = () => {
      router.post('/logout')
    }

    const showPwaBanner = ref(false)

    const installPwa = async () => {
      const promptEvent = window.deferredPrompt
      if (!promptEvent) return

      promptEvent.prompt()
      const { outcome } = await promptEvent.userChoice
      console.log(`PWA install prompt user choice outcome: ${outcome}`)

      window.deferredPrompt = null
      showPwaBanner.value = false
    }

    onMounted(() => {
      if (window.deferredPrompt) {
        showPwaBanner.value = true
      }

      window.addEventListener('pwa-install-ready', () => {
        showPwaBanner.value = true
      })

      window.addEventListener('appinstalled', () => {
        showPwaBanner.value = false
        window.deferredPrompt = null
        console.log('Adopta PWA installed successfully.')
      })
      if (['admin', 'fundacion', 'rescatista'].includes(props.user.role) && props.pets.length > 0) {
        // Encontrar la primera mascota con coordenadas válidas para centrar el mapa, de lo contrario usar Santiago
        const defaultPet = props.pets.find(p => p.latitude && p.longitude)
        const lat = defaultPet ? parseFloat(defaultPet.latitude) : -33.4489
        const lng = defaultPet ? parseFloat(defaultPet.longitude) : -70.6693

        // Dar un pequeño timeout para asegurar que el div del mapa está renderizado en el DOM
        setTimeout(() => {
          if (!document.getElementById('abandon-map')) return

          const map = L.map('abandon-map').setView([lat, lng], 11)

          // Estilo elegante oscuro de CartoDB
          L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
          }).addTo(map)

          // Dibujar marcadores redondos (Heatmap visual simple)
          props.pets.forEach(pet => {
            const pLat = parseFloat(pet.latitude)
            const pLng = parseFloat(pet.longitude)

            if (!isNaN(pLat) && !isNaN(pLng)) {
              const markerColor = pet.status === 'rescatado' ? '#f43f5e' : (pet.status === 'en_adopcion' ? '#8b5cf6' : '#10b981')
              const statusName = pet.status === 'rescatado' ? 'Rescatado (Abandonado)' : (pet.status === 'en_adopcion' ? 'En Adopción' : 'Adoptado')
              
              const circle = L.circleMarker([pLat, pLng], {
                radius: 8,
                fillColor: markerColor,
                color: '#ffffff',
                weight: 1.5,
                opacity: 1,
                fillOpacity: 0.8
              }).addTo(map)

              circle.bindPopup(`
                <div style="color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; line-height: 1.4;">
                  <strong style="font-size: 14px;">${pet.name}</strong><br/>
                  <span>Especie: <strong style="text-transform: capitalize;">${pet.species}</strong></span><br/>
                  <span>Estado: <span style="font-weight: 700; color: ${markerColor}">${statusName}</span></span>
                </div>
              `)
            }
          })
        }, 400)
      }

      // Inicializar notificaciones push si están soportadas
      initPushNotifications()
    })

    const initPushNotifications = async () => {
      if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        console.log('Push notifications not supported on this browser.');
        return;
      }

      try {
        const registration = await navigator.serviceWorker.ready;
        
        // Consultar suscripción existente
        const existingSub = await registration.pushManager.getSubscription();
        if (existingSub) {
          await sendSubscriptionToServer(existingSub);
          return;
        }

        // Solicitar permisos
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
          console.log('Notification permission denied.');
          return;
        }

        // Llave pública VAPID de contingencia para suscripción local / demo
        const vapidPublicKey = 'BPD9_N73iJv4HkHn7U_oH1vI0684zT9_gN73iJv4HkHn7U_oH1vI0684zT9_gN73iJv4HkHn7U_oH1vI0684zT9';
        const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);

        const newSub = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: applicationServerKey
        });

        await sendSubscriptionToServer(newSub);
        console.log('Suscrito a notificaciones Push exitosamente.');
      } catch (err) {
        console.warn('No se pudo completar el registro Push:', err);
      }
    }

    const sendSubscriptionToServer = async (subscription) => {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      try {
        const base = window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '';
        await fetch(`${base}/api/push-subscriptions`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(subscription)
        });
      } catch (err) {
        console.error('Error al sincronizar la suscripción con el servidor:', err);
      }
    }

    const urlBase64ToUint8Array = (base64String) => {
      const padding = '='.repeat((4 - base64String.length % 4) % 4);
      const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

      const rawData = window.atob(base64);
      const outputArray = new Uint8Array(rawData.length);

      for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
      }
      return outputArray;
    }

    const formatStatus = (statusVal) => {
      const map = {
        rescatado: 'Rescatado',
        en_transito: 'En Tránsito',
        en_adopcion: 'En Adopción',
        adoptado: 'Adoptado',
      }
      return map[statusVal] || statusVal
    }

    return { logout, formatStatus, showPwaBanner, installPwa }
  }
}
</script>


<style scoped>
.dashboard-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  position: relative;
  overflow: hidden;
  box-sizing: border-box;
}

/* Background Blobs */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.15;
  z-index: 0;
  pointer-events: none;
}

.blob-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -100px;
  left: 10%;
}

.blob-2 {
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -100px;
  right: 10%;
}

/* Dashboard Card */
.dashboard-card {
  position: relative;
  z-index: 10;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 2.5rem 2.5rem 3.5rem 2.5rem;
  width: 100%;
  max-width: 650px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2.25rem;
}

@media (max-width: 480px) {
  .dashboard-card {
    padding: 2rem 1.5rem;
  }
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1rem;
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

/* Welcome Box */
.welcome-box {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.welcome-box h2 {
  font-family: var(--font-title);
  font-size: 2rem;
  font-weight: 800;
  margin: 0;
}

.highlight-text {
  color: #c084fc;
}

.role-desc {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  margin: 0;
}

.role-name {
  color: var(--color-secondary);
}

.text-capitalize {
  text-transform: capitalize;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 0.4rem 0.8rem;
  border-radius: 99px;
  align-self: flex-start;
  margin-top: 0.5rem;
  color: var(--color-text-muted);
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: var(--color-warning);
}

.dot.verified {
  background-color: var(--color-success);
}

/* Hub Grid */
.hub-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.hub-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  text-decoration: none;
  color: var(--color-text-main);
  transition: all 0.2s ease-in-out;
  box-sizing: border-box;
}

.item-interactive:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.hub-icon {
  font-size: 2.25rem;
  background: rgba(255, 255, 255, 0.03);
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  flex-shrink: 0;
}

.hub-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.hub-info h4 {
  font-family: var(--font-title);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.hub-info p {
  color: var(--color-text-muted);
  font-size: 0.85rem;
  line-height: 1.4;
  margin: 0;
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

/* Map Dashboard Styles */
.map-card {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding-top: 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.map-title {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  border-left: 3px solid var(--color-primary);
  padding-left: 0.75rem;
}

.map-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

.abandon-map-container {
  height: 300px;
  width: 100%;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
  background: #111;
  z-index: 1; /* Asegurar que no tape el header/menús de ser necesario, pero que se renderice bien */
}

/* Sponsored Section Styles */
.sponsored-section {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding-top: 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.sponsored-title {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  border-left: 3px solid var(--color-primary);
  padding-left: 0.75rem;
}

.sponsored-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

.sponsored-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 0.5rem;
}

@media (max-width: 576px) {
  .sponsored-grid {
    grid-template-columns: 1fr;
  }
}

.sponsored-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-sizing: border-box;
}

.sponsored-card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sponsored-photo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.sponsored-emoji {
  font-size: 1.5rem;
  background: rgba(255, 107, 74, 0.1);
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sponsored-name {
  display: block;
  font-weight: 700;
  font-size: 0.95rem;
}

.sponsored-status {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

/* PWA Banner Styles */
.pwa-install-banner {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(255, 107, 74, 0.1) 100%);
  border: 1px dashed rgba(139, 92, 246, 0.3);
  border-radius: 16px;
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  box-sizing: border-box;
}

@media (max-width: 576px) {
  .pwa-install-banner {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
    gap: 1rem;
  }
}

.pwa-banner-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

@media (max-width: 576px) {
  .pwa-banner-content {
    flex-direction: column;
    gap: 0.5rem;
  }
}

.pwa-icon-large {
  font-size: 2.25rem;
  background: rgba(139, 92, 246, 0.15);
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  flex-shrink: 0;
}

.pwa-banner-text {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  text-align: left;
}

@media (max-width: 576px) {
  .pwa-banner-text {
    text-align: center;
  }
}

.pwa-banner-text h4 {
  font-family: var(--font-title);
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-main);
}

.pwa-banner-text p {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.4;
  margin: 0;
}

.pwa-install-btn {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white !important;
  box-shadow: 0 4px 10px rgba(255, 107, 74, 0.25);
  border: none;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}

.pwa-install-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(255, 107, 74, 0.35);
}
</style>
