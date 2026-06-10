<template>
  <div class="about-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <!-- Navigation Header -->
    <Header />

    <main class="main-content">
      <!-- Hero Section -->
      <section class="about-hero">
        <div class="badge">Nuestra Misión</div>
        <h1 class="title">Revolucionando el Bienestar Animal con <span class="gradient-text">Tecnología</span> y Amor</h1>
        <p class="subtitle">
          Adopta es un ecosistema digital integral que conecta fundaciones, rescatistas independientes, voluntarios de transporte y adoptantes definitivos en una sola plataforma estructurada con el fin de asegurar adopciones responsables y duraderas.
        </p>
      </section>

      <!-- Mission & Vision Cards -->
      <section class="grid-sections">
        <div class="about-card">
          <span class="card-icon">🎯</span>
          <h3>Nuestra Misión</h3>
          <p>
            Erradicar el abandono y el maltrato animal a través de procesos de adopción eficientes, seguros y automatizados. Empleamos un algoritmo avanzado de bienestar y comportamiento para verificar el match óptimo entre las necesidades de la mascota y el estilo de vida de la familia adoptante.
          </p>
        </div>
        <div class="about-card">
          <span class="card-icon">👁️</span>
          <h3>Nuestra Visión</h3>
          <p>
            Ser el estándar de gestión de bienestar animal en la región, promoviendo la transparencia financiera de las donaciones mediante informes de trazabilidad, la digitalización de contratos de tenencia responsable con validez legal, y la ayuda comunitaria a gran escala.
          </p>
        </div>
      </section>

      <!-- Core Pillars -->
      <section class="pillars-section">
        <h2 class="section-title">Nuestros Pilares Fundamentales</h2>
        <div class="pillars-grid">
          <div class="pillar-item">
            <h4>🧠 Match de Personalidad</h4>
            <p>Cruzamos el comportamiento, nivel de energía e historial médico de la mascota con las características del hogar del adoptante.</p>
          </div>
          <div class="pillar-item">
            <h4>🛡️ Seguridad Contractual</h4>
            <p>Bóveda digital integrada para la firma electrónica y descarga de contratos de adopción con validez legal.</p>
          </div>
          <div class="pillar-item">
            <h4>🚗 Uber Solidario</h4>
            <p>Red de transporte voluntario para distribuir alimentos, trasladar rescates y kits veterinarios de forma coordinada.</p>
          </div>
          <div class="pillar-item">
            <h4>💖 Trazabilidad Financiera</h4>
            <p>Cada donación ingresada se asocia directamente a una boleta de insumos o historial clínico, visible para toda la comunidad.</p>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="about-cta">
        <h2>¿Listo para formar parte del cambio?</h2>
        <p>Ya seas adoptante, rescatista independiente o quieras colaborar con tu vehículo como conductor voluntario, hay un lugar para ti en nuestro ecosistema.</p>
        <div class="cta-buttons">
          <Link href="/register" class="btn btn-primary">Registrarse Ahora</Link>
          <Link href="/" class="btn btn-outline">Saber Más</Link>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Header from '../Components/Header.vue'
import Footer from '../Components/Footer.vue'

export default {
  name: 'About',
  components: {
    Link,
    Header,
    Footer,
  },
  setup() {
    const page = usePage()
    const user = computed(() => page.props.auth.user)

    return {
      user
    }
  }
}
</script>

<style scoped>
.about-container {
  min-height: 100vh;
  position: relative;
  background-color: var(--color-bg);
  color: var(--color-text-main);
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  overflow-x: hidden;
  font-family: var(--font-body);
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
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 900px;
  padding: 4rem 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 4rem;
}

/* Hero */
.about-hero {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.badge {
  background: rgba(124, 58, 237, 0.15);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #c084fc;
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.title {
  font-family: 'Outfit', sans-serif;
  font-size: 2.75rem;
  font-weight: 800;
  line-height: 1.2;
  letter-spacing: -1px;
  margin: 0;
}

.gradient-text {
  background: linear-gradient(135deg, #a78bfa 0%, #38bdf8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  font-size: 1.15rem;
  line-height: 1.6;
  color: #94a3b8;
  margin: 0;
  max-width: 700px;
}

/* Grid Sections */
.grid-sections {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 768px) {
  .grid-sections {
    grid-template-columns: 1fr;
  }
}

.about-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 2.5rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  transition: all 0.3s ease;
}

.about-card:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

.card-icon {
  font-size: 2.5rem;
}

.about-card h3 {
  font-family: 'Outfit', sans-serif;
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}

.about-card p {
  color: #94a3b8;
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0;
}

/* Pillars Section */
.pillars-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
}

.section-title {
  font-family: 'Outfit', sans-serif;
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0;
}

.pillars-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  width: 100%;
}

@media (max-width: 768px) {
  .pillars-grid {
    grid-template-columns: 1fr;
  }
}

.pillar-item {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  padding: 1.75rem;
  box-sizing: border-box;
}

.pillar-item h4 {
  font-family: 'Outfit', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 0.75rem 0;
}

.pillar-item p {
  color: #94a3b8;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
}

/* CTA */
.about-cta {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.08) 0%, rgba(14, 165, 233, 0.04) 100%);
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: 24px;
  padding: 3rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.about-cta h2 {
  font-family: 'Outfit', sans-serif;
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0;
}

.about-cta p {
  color: #94a3b8;
  font-size: 1.05rem;
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

/* Buttons */
.btn {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.8rem 1.8rem;
  border-radius: 12px;
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
  box-shadow: 0 4px 14px rgba(255, 107, 74, 0.3);
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
  background: rgba(255, 107, 74, 0.05);
  transform: translateY(-2px);
}

.btn-sm {
  font-size: 0.85rem;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
}


</style>
