<template>
  <div class="welcome-container">
    <!-- Background Elements -->
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <!-- Navigation Header -->
    <Header />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-content">
          <div class="badge">Coquimbo & La Serena • Sin Fines de Lucro</div>
          <h1 class="hero-title">
            Construyamos el ecosistema solidario de bienestar animal <span class="gradient-text">Juntos</span>
          </h1>
          <p class="hero-subtitle">
            Adopta es una plataforma colaborativa y 100% sin fines de lucro, diseñada para conectar y promover adopciones responsables en las comunas de **Coquimbo** y **La Serena** (esperando expandirnos a más comunas a futuro). Actualmente estamos en fase de prelanzamiento. ¡Pre-regístrate y únete hoy!
          </p>
          <div class="hero-cta">
            <Link href="/register" class="btn btn-primary">Unirse a la Red</Link>
            <Link href="/nosotros" class="btn btn-outline">Saber Más</Link>
          </div>
        </div>
        
        <!-- Feature Grid -->
        <div class="feature-grid">
          <div class="card card-interactive">
            <div class="card-icon icon-purple">🧠</div>
            <h2>Match Inteligente</h2>
            <p>Cruzamos tu estilo de vida con la personalidad y ficha clínica de la mascota para evitar devoluciones.</p>
          </div>
          <div class="card card-interactive">
            <div class="card-icon icon-orange">🚗</div>
            <h2>Uber Solidario</h2>
            <p>Colabora trasladando alimentos, kits de bienvenida o mascotas en tu zona de forma voluntaria.</p>
          </div>
          <div class="card card-interactive">
            <div class="card-icon icon-teal">📅</div>
            <h2>Seguimiento Activo</h2>
            <p>IA predictiva analiza los reportes del diario de adopción para anticiparse a problemas clínicos o de conducta.</p>
          </div>
          <div class="card card-interactive">
            <div class="card-icon icon-pink">🛡️</div>
            <h2>Bóveda de Contratos</h2>
            <p>Firmas digitales seguras con validez legal para el resguardo de la tenencia responsable.</p>
          </div>
        </div>
      </section>

      <!-- Featured Pets Section -->
      <section class="featured-pets-section">
        <div class="section-badge">Nuestras Huellas</div>
        <h2 class="section-title">Nuestras Mascotas</h2>
        <p class="section-desc">Fichas clínicas y perfiles etológicos reales. Si ya eres parte de la red, puedes postular a su adopción o apadrinamiento.</p>
        
        <div v-if="featuredPets && featuredPets.length > 0" class="pets-welcome-grid">
          <div class="pet-welcome-card" v-for="pet in featuredPets" :key="pet.id">
            <div class="pet-img-container">
              <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="pet-welcome-img" />
              <div v-else class="pet-welcome-placeholder">🐾</div>
              <span class="pet-welcome-status text-capitalize" :class="'status-' + pet.status">{{ pet.status }}</span>
            </div>
            <div class="pet-welcome-info">
              <div class="pet-welcome-header">
                <h3>{{ pet.name }}</h3>
                <span class="pet-welcome-breed text-capitalize">{{ pet.breed || pet.species }}</span>
              </div>
              <p class="pet-welcome-meta">📅 Edad: {{ pet.age_text || 'Sin especificar' }}</p>
              <div class="pet-welcome-actions">
                <Link :href="pet.status === 'adoptado' ? '/pets/' + pet.id + '/qr-pass' : '/pets'" class="btn btn-primary btn-sm" style="flex: 1.5;">
                  {{ pet.status === 'adoptado' ? 'Ver Qr Pass' : 'Adoptar' }}
                </Link>
                <Link :href="'/pets/' + pet.id + '/sponsor'" class="btn btn-secondary btn-sm" v-if="pet.status !== 'adoptado'" style="flex: 1.5;">
                  💖 Apadrinar
                </Link>
                <button @click="sharePet(pet)" class="btn btn-secondary btn-sm btn-share-icon" title="Compartir">
                  🔗
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State Convocatoria -->
        <div v-else class="empty-pets-recruitment">
          <div class="empty-pets-icon">🐾</div>
          <h3>¡Fase de Convocatoria y Carga de Casos!</h3>
          <p>
            Actualmente nos encontramos preparando la plataforma para iniciar operaciones. Aún no hay mascotas registradas para adopción. 
            **Si eres rescatista independiente o perteneces a una Fundación, te invitamos a registrarte y comenzar a publicar tus primeros casos.**
          </p>
          <div class="empty-pets-actions">
            <Link href="/register" class="btn btn-primary">Registrarme como Rescatista</Link>
            <Link href="/register" class="btn btn-outline">Postular como Hogar Temporal</Link>
          </div>
        </div>

        <div class="all-pets-cta" v-if="featuredPets && featuredPets.length > 0">
          <Link href="/pets" class="btn btn-outline">Explorar Catálogo de Mascotas</Link>
        </div>
      </section>

      <!-- Statistics Section (Launch Goals) -->
      <section class="stats-section">
        <div class="section-badge">Metas de Lanzamiento</div>
        <h2 class="section-title">Metas de la Red Comunitaria</h2>
        <p class="section-desc">Partimos de cero y apostamos por la transparencia total. Para iniciar la marcha blanca y habilitar las funciones automáticas, necesitamos alcanzar las siguientes metas de la red en la zona.</p>
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-num">{{ launchStats.pets_count }} / 5</span>
            <span class="stat-label">Mascotas Registradas</span>
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: Math.min(100, Math.round((launchStats.pets_count / 5) * 100)) + '%' }"></div>
            </div>
            <span class="stat-progress-sub">Meta inicial para activar match ({{ Math.min(100, Math.round((launchStats.pets_count / 5) * 100)) }}%)</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ launchStats.trips_count }} / 2</span>
            <span class="stat-label">Traslados Completados</span>
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: Math.min(100, Math.round((launchStats.trips_count / 2) * 100)) + '%' }"></div>
            </div>
            <span class="stat-progress-sub">Meta de Uber Solidario ({{ Math.min(100, Math.round((launchStats.trips_count / 2) * 100)) }}%)</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ launchStats.entrepreneurs_count }} / 10</span>
            <span class="stat-label">Emprendedores en el Bazar</span>
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: Math.min(100, Math.round((launchStats.entrepreneurs_count / 10) * 100)) + '%' }"></div>
            </div>
            <span class="stat-progress-sub">Para canje en Club de Huellas ({{ Math.min(100, Math.round((launchStats.entrepreneurs_count / 10) * 100)) }}%)</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">{{ launchStats.users_count }} / 15</span>
            <span class="stat-label">Pre-registros de Adoptantes</span>
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: Math.min(100, Math.round((launchStats.users_count / 15) * 100)) + '%' }"></div>
            </div>
            <span class="stat-progress-sub">Meta de la comunidad fundadora ({{ Math.min(100, Math.round((launchStats.users_count / 15) * 100)) }}%)</span>
          </div>
        </div>
      </section>

      <!-- How It Works Section (Elige tu Rol) -->
      <section class="how-it-works-section">
        <div class="section-badge">Participación</div>
        <h2 class="section-title">¿Cómo puedes sumarte?</h2>
        <p class="section-desc">Cada actor es vital para erradicar el abandono y resguardar el bienestar animal. Registra tu perfil y sé un pionero.</p>
        <div class="steps-grid recruitment-grid">
          <div class="step-card recruitment-card card-interactive">
            <div class="step-num">1</div>
            <h3>Rescatistas y ONGs</h3>
            <p>Registra tu organización. Centraliza fichas clínicas, genera contratos de adopción digitales firmados y recibe alertas de IA sobre bienestar.</p>
            <Link href="/register" class="btn btn-outline btn-sm recruitment-btn">Registrar Refugio</Link>
          </div>
          <div class="step-card recruitment-card card-interactive">
            <div class="step-num">2</div>
            <h3>Conductores Solidarios</h3>
            <p>¿Tienes vehículo y quieres ayudar? Súmate a la red logística de "Uber Solidario" para trasladar mascotas rescatadas o kits de alimento en tu sector.</p>
            <Link href="/register" class="btn btn-outline btn-sm recruitment-btn">Quiero Ayudar</Link>
          </div>
          <div class="step-card recruitment-card card-interactive">
            <div class="step-num">3</div>
            <h3>Pymes y Bazar Animal</h3>
            <p>Inscribe tu comercio local de mascotas. Obtén visibilidad premium participando en el Club de Huellas con cupones de descuento.</p>
            <Link href="/register" class="btn btn-outline btn-sm recruitment-btn">Inscribir Negocio</Link>
          </div>
          <div class="step-card recruitment-card card-interactive">
            <div class="step-num">4</div>
            <h3>Adoptantes y Padrinos</h3>
            <p>Pre-regístrate. Completa tu perfil etológico y de estilo de vida para que estés listo para postular en cuanto se carguen las primeras fichas.</p>
            <Link href="/register" class="btn btn-outline btn-sm recruitment-btn">Crear Cuenta</Link>
          </div>
        </div>
      </section>

      <!-- Virtual Sponsorship Section -->
      <section class="virtual-sponsor-promo">
        <div class="sponsor-promo-content">
          <div class="badge-primary">Impacto Remoto</div>
          <h2>¿No puedes adoptar físicamente?</h2>
          <h3>Súmate como Padrino Virtual</h3>
          <p>
            Muchos animales rescatados requieren financiamiento para tratamientos, vacunas y alimentación antes de estar listos para adopción. Al apadrinar a distancia, cubres estos gastos y sumas "Huellas" canjeables en nuestra comunidad.
          </p>
          <ul class="promo-features">
            <li>✓ Recibe el Diario de Seguimiento digital del animal.</li>
            <li>✓ Acceso a la credencial digital pública (QR Pet Pass).</li>
            <li>✓ Notificaciones del estado de salud de tu mascota ahijada.</li>
          </ul>
          <Link href="/register" class="btn btn-primary">Registrarme para Apadrinar</Link>
        </div>
        <div class="sponsor-promo-illustration">
          <span class="heart-pulse-icon">💖</span>
        </div>
      </section>

      <!-- Success Stories Section (Founded Vision) -->
      <section class="testimonials-section">
        <div class="section-badge">Visión Solidaria</div>
        <h2 class="section-title">El Origen del Proyecto</h2>
        <div class="testimonials-grid">
          <div class="testimonial-card">
            <p class="testimonial-text">"La falta de trazabilidad y el abandono son problemas graves. Crear una plataforma donde la IA y la comunidad se unan para vigilar el bienestar de por vida es el paso que necesitábamos para una tenencia responsable."</p>
            <div class="testimonial-author">
              <strong>Equipo Fundador Adopta</strong>
              <span>Comprometidos con el bienestar animal y la transparencia</span>
            </div>
          </div>
          <div class="testimonial-card">
            <p class="testimonial-text">"Como rescatista independiente, la logística y la validación de adoptantes siempre es el cuello de botella. Saber que habrá una red organizada de traslados y contratos digitales me motivó a sumarme de inmediato."</p>
            <div class="testimonial-author">
              <strong>Carla Fuentes</strong>
              <span>Rescatista Colaboradora Inicial</span>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="cta-section">
        <h2>¿Listo para marcar la diferencia desde el día uno?</h2>
        <p>Regístrate hoy de manera transparente. Ayúdanos a poblar el catálogo de mascotas, sumarte a la red de transporte o preparar tu perfil de adopción.</p>
        <div class="cta-buttons">
          <Link href="/register" class="btn btn-primary">Registrarse Ahora</Link>
          <Link href="/nosotros" class="btn btn-outline">Leer Sobre Nosotros</Link>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Header from '../Components/Header.vue'
import Footer from '../Components/Footer.vue'

export default {
  name: 'Welcome',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    featuredPets: {
      type: Array,
      default: () => []
    },
    launchStats: {
      type: Object,
      default: () => ({
        pets_count: 0,
        trips_count: 0,
        entrepreneurs_count: 3,
        users_count: 0
      })
    }
  },
  methods: {
    sharePet(pet) {
      const base = window.location.origin + (window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '');
      const shareUrl = `${base}/pets/${pet.id}/story`;
      const shareTitle = `Conoce a ${pet.name} en Adopta`;
      const shareText = `¡Mira el caso de ${pet.name}! Únete al ecosistema solidario de bienestar animal.`;

      if (navigator.share) {
        navigator.share({
          title: shareTitle,
          text: shareText,
          url: shareUrl,
        }).catch(console.error);
      } else {
        navigator.clipboard.writeText(shareUrl).then(() => {
          alert('¡Enlace de la historia copiado al portapapeles!');
        }).catch(err => {
          console.error('Error al copiar el enlace:', err);
        });
      }
    }
  }
}
</script>


<style scoped>
.welcome-container {
  min-height: 100vh;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
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
  left: -100px;
}

.blob-2 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -150px;
  right: -100px;
}



.main-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 2rem;
  box-sizing: border-box;
  z-index: 10;
}

@media (max-width: 1150px) {
  .main-content {
    padding: 0 1.5rem;
  }
}

/* Hero Section */
.hero {
  position: relative;
  width: 100%;
  max-width: 1200px;
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 4rem;
  padding: 4rem 0;
  box-sizing: border-box;
  align-items: center;
}

@media (max-width: 1150px) {
  .hero {
    grid-template-columns: 1fr;
    gap: 3rem;
    padding: 2rem 0;
  }
}

.hero-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1.5rem;
}

.badge {
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.hero-title {
  font-family: var(--font-title);
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -1.5px;
  margin: 0;
}

@media (max-width: 576px) {
  .hero-title {
    font-size: 2.75rem;
  }
}

.gradient-text {
  background: linear-gradient(135deg, #a78bfa 0%, #38bdf8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-subtitle {
  font-size: 1.1rem;
  line-height: 1.6;
  color: var(--color-text-muted);
  margin: 0;
}

.hero-cta {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

@media (max-width: 480px) {
  .hero-cta {
    flex-direction: column;
    width: 100%;
  }
  .hero-cta .btn {
    width: 100%;
    text-align: center;
  }
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.8rem 1.8rem;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
  box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
}

.btn-primary:hover {
  background: var(--color-primary-hover);
  transform: translateY(-2px);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.07);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}

.btn-outline {
  background: transparent;
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  background: rgba(139, 92, 246, 0.05);
  transform: translateY(-2px);
}

/* Feature Grid & Cards */
.feature-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 1.5rem;
}

@media (max-width: 576px) {
  .feature-grid {
    grid-template-columns: 1fr;
  }
}

.card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-sizing: border-box;
}

.card-interactive:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.05);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.icon-purple { background: rgba(139, 92, 246, 0.1); }
.icon-orange { background: rgba(249, 115, 22, 0.1); }
.icon-teal { background: rgba(20, 184, 166, 0.1); }
.icon-pink { background: rgba(244, 63, 94, 0.1); }

.card h2 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-main);
}

.card p {
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--color-text-muted);
  margin: 0;
}

/* Animations */
@keyframes wag {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(15deg); }
}

/* Stats Section */
.stats-section {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
  box-sizing: border-box;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  width: 100%;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-num {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--color-secondary);
}

.stat-label {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  font-weight: 500;
}

/* How It Works */
.how-it-works-section {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
  box-sizing: border-box;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  width: 100%;
}

@media (max-width: 768px) {
  .steps-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .steps-grid {
    grid-template-columns: 1fr;
  }
}

.step-card {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
}

.step-num {
  width: 36px;
  height: 36px;
  background: rgba(124, 58, 237, 0.15);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #c084fc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.1rem;
}

.step-card h3 {
  font-family: var(--font-title);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
}

.step-card p {
  color: var(--color-text-muted);
  font-size: 0.85rem;
  line-height: 1.45;
  margin: 0;
}

/* Testimonials */
.testimonials-section {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
  box-sizing: border-box;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  width: 100%;
}

@media (max-width: 768px) {
  .testimonials-grid {
    grid-template-columns: 1fr;
  }
}

.testimonial-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 20px;
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  box-sizing: border-box;
}

.testimonial-text {
  font-style: italic;
  color: var(--color-text-main);
  line-height: 1.6;
  font-size: 1.05rem;
  margin: 0;
}

.testimonial-author {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.testimonial-author strong {
  font-family: var(--font-title);
  font-size: 1rem;
}

.testimonial-author span {
  color: var(--color-text-muted);
  font-size: 0.8rem;
}

/* CTA Section */
.cta-section {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.08) 0%, rgba(14, 165, 233, 0.04) 100%);
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: 24px;
  padding: 4rem 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  box-sizing: border-box;
}

.cta-section h2 {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0;
}

.cta-section p {
  color: var(--color-text-muted);
  font-size: 1.1rem;
  line-height: 1.6;
  margin: 0;
  max-width: 600px;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
}

@media (max-width: 480px) {
  .cta-buttons {
    flex-direction: column;
    width: 100%;
  }
}

/* New Featured Pets Styles */
.featured-pets-section {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  box-sizing: border-box;
}

.section-badge {
  background: rgba(255, 107, 74, 0.15);
  border: 1px solid rgba(255, 107, 74, 0.3);
  color: var(--color-primary);
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.section-title {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0;
  text-align: center;
}

.section-desc {
  color: var(--color-text-muted);
  font-size: 1.05rem;
  text-align: center;
  margin: 0;
  max-width: 600px;
}

.pets-welcome-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  width: 100%;
  margin-top: 1.5rem;
  box-sizing: border-box;
}

@media (max-width: 968px) {
  .pets-welcome-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 576px) {
  .pets-welcome-grid {
    grid-template-columns: 1fr;
  }
}

.pet-welcome-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-sizing: border-box;
  text-align: left;
}

.pet-welcome-card:hover {
  transform: translateY(-4px);
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.pet-img-container {
  height: 280px;
  position: relative;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.05);
}

.pet-welcome-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.pet-welcome-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  background: rgba(255, 255, 255, 0.02);
}

.pet-welcome-status {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.3rem 0.75rem;
  border-radius: 99px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.status-en_adopcion {
  background: rgba(245, 158, 11, 0.85);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: white;
}

.status-adoptado {
  background: rgba(16, 185, 129, 0.85);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: white;
}

.status-rescatado {
  background: rgba(244, 63, 94, 0.85);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: white;
}

.pet-welcome-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex: 1;
  box-sizing: border-box;
}

.pet-welcome-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}

.pet-welcome-header h3 {
  font-family: var(--font-title);
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0;
}

.pet-welcome-breed {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

.pet-welcome-meta {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin: 0;
}

.pet-welcome-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.pet-welcome-actions .btn {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  border-radius: 8px;
}

.btn-share-icon {
  padding: 0.5rem;
  min-width: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 8px;
}

.all-pets-cta {
  margin-top: 1.5rem;
}

/* Virtual Sponsor Promo styles */
.virtual-sponsor-promo {
  width: 100%;
  max-width: 1200px;
  margin-top: 5rem;
  background: linear-gradient(135deg, rgba(255, 107, 74, 0.08) 0%, rgba(13, 148, 136, 0.04) 100%);
  border: 1px solid rgba(255, 107, 74, 0.2);
  border-radius: 24px;
  padding: 3.5rem;
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 3rem;
  align-items: center;
  box-sizing: border-box;
}

@media (max-width: 768px) {
  .virtual-sponsor-promo {
    grid-template-columns: 1fr;
    padding: 2.5rem;
    gap: 2rem;
  }
}

.sponsor-promo-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1.25rem;
  text-align: left;
}

.badge-primary {
  background: rgba(255, 107, 74, 0.15);
  border: 1px solid rgba(255, 107, 74, 0.3);
  color: var(--color-primary);
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.sponsor-promo-content h2 {
  font-size: 1.25rem;
  color: var(--color-text-muted);
  margin: 0;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sponsor-promo-content h3 {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: -0.5rem 0 0 0;
  color: var(--color-text-main);
}

.sponsor-promo-content p {
  color: var(--color-text-muted);
  font-size: 1.05rem;
  line-height: 1.6;
  margin: 0;
}

.promo-features {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: var(--color-text-main);
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.sponsor-promo-illustration {
  display: flex;
  justify-content: center;
  align-items: center;
}

.heart-pulse-icon {
  font-size: 8rem;
  color: var(--color-primary);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.9; }
  50% { transform: scale(1.1); opacity: 1; }
}

/* Pre-launch Convocatoria Styles */
.empty-pets-recruitment {
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.15);
  border-radius: 24px;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 700px;
  margin: 2rem auto 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
  box-sizing: border-box;
}

.empty-pets-icon {
  font-size: 3.5rem;
  background: rgba(139, 92, 246, 0.1);
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: 1px solid rgba(139, 92, 246, 0.2);
}

.empty-pets-recruitment h3 {
  font-family: var(--font-title);
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  color: var(--color-text-main);
}

.empty-pets-recruitment p {
  color: var(--color-text-muted);
  font-size: 1.05rem;
  line-height: 1.6;
  margin: 0;
}

.empty-pets-actions {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
}

@media (max-width: 576px) {
  .empty-pets-actions {
    flex-direction: column;
    width: 100%;
  }
  .empty-pets-actions .btn {
    width: 100%;
  }
}

/* Progress Bar and Goal styles */
.progress-bar-container {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 99px;
  overflow: hidden;
  margin: 0.75rem 0 0.25rem 0;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border-radius: 99px;
}

.stat-progress-sub {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* Recruitment Buttons */
.recruitment-btn {
  margin-top: auto;
  width: 100%;
  text-align: center;
}
</style>
