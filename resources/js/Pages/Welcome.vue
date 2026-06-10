<template>
  <div class="welcome-container">
    <!-- Background Elements -->
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <!-- Navigation Header -->
    <Header />

    <!-- Hero Section -->
    <main class="hero">
      <div class="hero-content">
        <div class="badge">Ecosistema Solidario</div>
        <h1 class="hero-title">
          Encuentra a tu compañero de vida con <span class="gradient-text">Inteligencia</span>
        </h1>
        <p class="hero-subtitle">
          Interconectamos rescatistas, hogares temporales y adoptantes definitivos. Algoritmos de match de personalidad, seguimiento automatizado y logística colaborativa para asegurar una tenencia responsable de por vida.
        </p>
        <div class="hero-cta">
          <Link href="/register" class="btn btn-primary">Encontrar Mascota</Link>
          <Link href="/register" class="btn btn-outline">Quiero ser Tránsito</Link>
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

      <!-- Featured Pets Section -->
      <section class="featured-pets-section" v-if="featuredPets && featuredPets.length > 0">
        <div class="section-badge">Nuestras Huellas</div>
        <h2 class="section-title">Conoce a Nuestras Mascotas</h2>
        <p class="section-desc">Animales reales en busca de una familia. Puedes postular para adoptarlos o apadrinarlos hoy mismo.</p>
        
        <div class="pets-welcome-grid">
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
                <Link :href="pet.status === 'adoptado' ? '/pets/' + pet.id + '/qr-pass' : '/pets'" class="btn btn-primary btn-sm">
                  {{ pet.status === 'adoptado' ? 'Ver Qr Pass' : 'Adoptar' }}
                </Link>
                <Link :href="'/pets/' + pet.id + '/sponsor'" class="btn btn-secondary btn-sm" v-if="pet.status !== 'adoptado'">
                  💖 Apadrinar
                </Link>
              </div>
            </div>
          </div>
        </div>
        <div class="all-pets-cta">
          <Link href="/pets" class="btn btn-outline">Explorar Catálogo de Mascotas</Link>
        </div>
      </section>


      <!-- Statistics Section -->
      <section class="stats-section">
        <h2 class="section-title">El Impacto de Nuestro Ecosistema</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-num">1.250+</span>
            <span class="stat-label">Adopciones Exitosas</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">45+</span>
            <span class="stat-label">Refugios y Fundaciones</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">380+</span>
            <span class="stat-label">Conductores Solidarios</span>
          </div>
          <div class="stat-card">
            <span class="stat-num">98.4%</span>
            <span class="stat-label">Índice de Retención</span>
          </div>
        </div>
      </section>

      <!-- How It Works Section -->
      <section class="how-it-works-section">
        <h2 class="section-title">Cómo Funciona el Ecosistema</h2>
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-num">1</div>
            <h3>Completa tu Perfil</h3>
            <p>Registra tu estilo de vida y preferencias de tenencia de forma honesta.</p>
          </div>
          <div class="step-card">
            <div class="step-num">2</div>
            <h3>Algoritmo de Match</h3>
            <p>Buscamos la compatibilidad ideal basada en etología y ficha clínica.</p>
          </div>
          <div class="step-card">
            <div class="step-num">3</div>
            <h3>Firma Electrónica</h3>
            <p>Completa el contrato digital con validez legal desde nuestra Bóveda.</p>
          </div>
          <div class="step-card">
            <div class="step-num">4</div>
            <h3>Seguimiento y Club</h3>
            <p>Reporta el estado diario de la mascota para acumular Huellas canjeables.</p>
          </div>
        </div>
      </section>

      <!-- Virtual Sponsorship Section -->
      <section class="virtual-sponsor-promo">
        <div class="sponsor-promo-content">
          <div class="badge-primary">Impacto Remoto</div>
          <h2>¿No puedes adoptar físicamente?</h2>
          <h3>¡Sé un Padrino Virtual!</h3>
          <p>
            Apoya financieramente a una mascota rescatada mientras espera su hogar definitivo. Tu aporte mensual cubre alimentación, vacunas y gastos veterinarios.
          </p>
          <ul class="promo-features">
            <li>✓ Recibe reportes médicos y de conducta automáticos del animal.</li>
            <li>✓ Acceso a la credencial digital (QR Pet Pass) del ahijado.</li>
            <li>✓ Suma Huellas de fidelización para canjear en el Bazar Animal.</li>
          </ul>
          <Link href="/pets" class="btn btn-primary">Elegir Mascota para Apadrinar</Link>
        </div>
        <div class="sponsor-promo-illustration">
          <span class="heart-pulse-icon">💖</span>
        </div>
      </section>


      <!-- Success Stories Section -->
      <section class="testimonials-section">
        <h2 class="section-title">Historias de Éxito</h2>
        <div class="testimonials-grid">
          <div class="testimonial-card">
            <p class="testimonial-text">"Gracias al match inteligente pudimos adoptar a Milo. Teníamos miedo por nuestros gatos, pero la compatibilidad fue perfecta desde el primer día."</p>
            <div class="testimonial-author">
              <strong>Familia Silva Castro</strong>
              <span>Adoptantes de Milo (Mestizo, 2 años)</span>
            </div>
          </div>
          <div class="testimonial-card">
            <p class="testimonial-text">"La red de Uber Solidario me permitió trasladar alimento para un refugio de manera voluntaria en mi camino al trabajo. Es increíble ver cómo todos colaboran."</p>
            <div class="testimonial-author">
              <strong>Esteban Morales</strong>
              <span>Conductor Voluntario</span>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="cta-section">
        <h2>¿Listo para marcar la diferencia?</h2>
        <p>Regístrate hoy mismo. Encuentra a tu compañero, colabora en logística solidaria o apoya con donaciones transparentes.</p>
        <div class="cta-buttons">
          <Link href="/register" class="btn btn-primary">Registrarse Ahora</Link>
          <Link href="/bazar" class="btn btn-outline">Explorar Bazar</Link>
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



/* Hero Section */
.hero {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1200px;
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 4rem;
  padding: 4rem 2rem;
  box-sizing: border-box;
  align-items: center;
}

@media (max-width: 968px) {
  .hero {
    grid-template-columns: 1fr;
    gap: 3rem;
    padding: 2rem 1.5rem;
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
  grid-template-columns: 1fr 1fr;
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
  padding: 2rem;
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
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
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
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
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
  margin-top: 5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
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
  height: 220px;
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
  flex: 1;
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  border-radius: 8px;
}

.all-pets-cta {
  margin-top: 1.5rem;
}

/* Virtual Sponsor Promo styles */
.virtual-sponsor-promo {
  width: 100%;
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
</style>
