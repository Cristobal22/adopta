<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">
      <!-- Section Header -->
      <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h1>Fiscalización de Alertas de Maltrato</h1>
          <p class="subtitle">Inspección de reportes de diario alertados automáticamente por la IA en la comuna de <strong>{{ communeName }}</strong>.</p>
        </div>
        <div>
          <Link href="/municipality/dashboard" class="btn btn-secondary btn-sm">Volver al Dashboard</Link>
        </div>
      </div>

        <!-- Alerts list -->
        <div class="alerts-container">
          <div class="card alert-card" v-for="alert in alerts" :key="alert.id" :class="{ 'abuse-visual': alert.ai_abuse_flag }">
            <div class="alert-header">
              <div class="meta-section">
                <span class="badge-danger-sm" v-if="alert.ai_abuse_flag">🚨 Alerta Visual: Maltrato Detectado</span>
                <span class="badge-warning-sm" v-else>⚠️ Alerta: Sentimiento Extremadamente Negativo</span>
                <span class="alert-date">Fecha: {{ formatDate(alert.report_date) }}</span>
              </div>
              <span class="mood-emoji">{{ alert.emoji_mood }}</span>
            </div>

            <div class="alert-body">
              <div class="details-row">
                <div class="detail-item">
                  <strong>Mascota:</strong>
                  <span>{{ alert.adoption?.pet?.name }} (ID #{{ alert.adoption?.pet?.id }})</span>
                </div>
                <div class="detail-item">
                  <strong>Adoptante:</strong>
                  <span>{{ alert.adoption?.adopter?.name }} (RUT: {{ formatIdentification(alert.adoption?.adopter?.verification_data?.identification_code) }})</span>
                </div>
                <div class="detail-item">
                  <strong>Contacto:</strong>
                  <span>{{ alert.adoption?.adopter?.phone }} / {{ alert.adoption?.adopter?.email }}</span>
                </div>
              </div>

              <div class="comment-section">
                <h5>Comentario del Reporte:</h5>
                <blockquote>"{{ alert.comment }}"</blockquote>
              </div>

              <div class="ai-diagnose">
                <h5>Diagnóstico de Inteligencia Artificial:</h5>
                <ul>
                  <li>Puntuación de Sentimiento del texto: <strong :class="getSentimentClass(alert.ai_sentiment_score)">{{ alert.ai_sentiment_score }}</strong> (rango -1.0 a 1.0)</li>
                  <li>Alerta de abuso/maltrato visual en imagen: <strong>{{ alert.ai_abuse_flag ? 'SÍ (Sospecha crítica)' : 'NO (Sin anomalías visuales)' }}</strong></li>
                </ul>
              </div>

              <!-- Foto del diario -->
              <div class="diary-photo-wrapper" v-if="alert.photo_path">
                <img :src="'/adoptions/' + alert.adoption_id + '/diaries/' + alert.id + '/photo'" alt="Foto reportada" class="diary-photo-img" />
              </div>
            </div>

            <div class="alert-footer">
              <button class="btn btn-danger btn-sm" @click="dispatchInspector(alert)">
                👮 Despachar Inspector Municipal
              </button>
              <button class="btn btn-secondary btn-sm" @click="contactAdopter(alert)">
                ✉ Contactar Adoptante
              </button>
            </div>
          </div>

          <div class="card empty-state" v-if="alerts.length === 0">
            <div class="empty-icon">✓</div>
            <h3>Sin alertas críticas en la comuna</h3>
            <p>El algoritmo etológico y de visión no ha detectado sospechas de maltrato o adaptación crítica en los diarios recientes de tu comuna.</p>
          </div>
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'AuditAbuse',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    commune: String,
    alerts: Array,
  },
  setup(props) {
    const communeName = computed(() => {
      if (!props.commune) return 'Colaboradora'
      return props.commune.split(',')[0].trim()
    })

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }

    const formatIdentification = (rut) => {
      if (!rut) return 'Sin registrar'
      return rut
    }

    const getSentimentClass = (score) => {
      if (score < -0.6) return 'text-danger font-bold'
      if (score < -0.2) return 'text-warning'
      return ''
    }

    const dispatchInspector = (alert) => {
      alert(`Orden de fiscalización municipal despachada para la dirección de ${alert.adoption.adopter.name}. Se auditará esta acción.`);
    }

    const contactAdopter = (alert) => {
      alert(`Contacto iniciado. Se ha enviado un correo oficial a ${alert.adoption.adopter.email} solicitando aclaración del reporte.`);
    }

    return {
      communeName,
      formatDate,
      formatIdentification,
      getSentimentClass,
      dispatchInspector,
      contactAdopter,
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
  max-width: 1000px;
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
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
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

.subtitle {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  margin: 0;
}

/* Alert Cards */
.alerts-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.alert-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  border-left: 4px solid rgba(245, 158, 11, 0.5);
}

.alert-card.abuse-visual {
  border-left-color: #f43f5e;
  background: radial-gradient(circle at top right, rgba(244, 63, 94, 0.04), transparent);
}

.alert-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.meta-section {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.badge-danger-sm {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: #fca5a5;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  align-self: flex-start;
}

.badge-warning-sm {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fde047;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  align-self: flex-start;
}

.alert-date {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.mood-emoji {
  font-size: 2rem;
}

.alert-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.details-row {
  display: grid;
  grid-template-columns: 1.2fr 1.1fr 1fr;
  gap: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 0.85rem;
}

@media (max-width: 600px) {
  .details-row {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.comment-section h5, .ai-diagnose h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

blockquote {
  margin: 0;
  padding-left: 1rem;
  border-left: 3px solid var(--color-secondary);
  font-style: italic;
  font-size: 0.95rem;
  color: var(--color-text-main);
}

.ai-diagnose ul {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.85rem;
  line-height: 1.5;
}

.diary-photo-wrapper {
  margin-top: 0.5rem;
  width: 100%;
  max-width: 400px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.diary-photo-img {
  width: 100%;
  height: auto;
  display: block;
}

.alert-footer {
  display: flex;
  gap: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 1.25rem;
}

@media (max-width: 480px) {
  .alert-footer {
    flex-direction: column;
  }
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  font-size: 3rem;
  color: var(--color-success);
  margin-bottom: 1rem;
}

/* Utilities */
.text-danger { color: #f43f5e; }
.text-warning { color: #eab308; }
.font-bold { font-weight: 700; }

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

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

.btn-danger {
  background: #e11d48;
  color: white;
}

.btn-danger:hover {
  background: #be123c;
}

.btn-sm {
  font-size: 0.75rem;
  padding: 0.35rem 0.75rem;
}
</style>
