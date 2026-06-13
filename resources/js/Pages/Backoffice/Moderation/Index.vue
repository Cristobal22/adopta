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
          <h1>Moderación de Historias de Éxito</h1>
          <p class="subtitle">Revisa y aprueba las bitácoras que los adoptantes decidieron compartir públicamente en el perfil de su mascota.</p>
        </div>

        <!-- Moderation List -->
        <div class="moderation-grid" v-if="diaries.length > 0">
          <div class="card moderation-card" v-for="diary in diaries" :key="diary.id">
            <!-- Header Meta -->
            <div class="card-header-meta">
              <div class="pet-user-info">
                <h3>Mascota: <span class="highlight-text">{{ diary.adoption?.pet?.name }}</span></h3>
                <span class="meta-label">Adoptante: <strong>{{ diary.adoption?.adopter?.name }}</strong></span>
              </div>
              <span class="mood-badge">{{ diary.emoji_mood }}</span>
            </div>

            <!-- Content Comment -->
            <div class="comment-block">
              <h5>Comentario sugerido para el perfil público:</h5>
              <blockquote>"{{ diary.comment }}"</blockquote>
              <span class="report-date">Fecha del reporte: {{ formatDate(diary.report_date) }}</span>
            </div>

            <!-- Photo pre-viewer -->
            <div class="photo-preview" v-if="diary.photo_path">
              <h5>Fotografía adjunta:</h5>
              <div class="image-wrapper">
                <img :src="'/adoptions/' + diary.adoption_id + '/diaries/' + diary.id + '/photo'" alt="Foto enviada" class="preview-img" />
              </div>
            </div>

            <!-- Action buttons -->
            <div class="card-actions">
              <button class="btn btn-success" @click="approveDiary(diary)" :disabled="processingId === diary.id">
                {{ processingId === diary.id ? 'Aprobando...' : '✓ Aprobar y Publicar (+50 Huellas)' }}
              </button>
              <button class="btn btn-danger" @click="rejectDiary(diary)" :disabled="processingId === diary.id">
                {{ processingId === diary.id ? 'Rechazando...' : '✕ Rechazar Publicación' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div class="card empty-state" v-else>
          <div class="empty-icon">✓</div>
          <h3>No hay bitácoras pendientes de moderación</h3>
          <p>Todos los reportes marcados como públicos han sido procesados. ¡Gran trabajo manteniendo al día la comunidad!</p>
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Header from '../../../Components/Header.vue'
import Footer from '../../../Components/Footer.vue'

export default {
  name: 'Index',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    diaries: Array,
  },
  setup() {
    const page = usePage()
    const flash = computed(() => page.props.flash || {})
    const processingId = ref(null)

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }

    const approveDiary = (diary) => {
      processingId.value = diary.id
      router.post(`/backoffice/moderation/${diary.id}/approve`, {}, {
        onFinish: () => {
          processingId.value = null
        }
      })
    }

    const rejectDiary = (diary) => {
      if (confirm('¿Estás seguro de rechazar la publicación de esta bitácora? Permanecerá visible solo de manera privada para la fundación y el adoptante.')) {
        processingId.value = diary.id
        router.post(`/backoffice/moderation/${diary.id}/reject`, {}, {
          onFinish: () => {
            processingId.value = null
          }
        })
      }
    }

    return {
      flash,
      processingId,
      formatDate,
      approveDiary,
      rejectDiary,
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
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2rem;
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
  background: rgba(13, 148, 136, 0.15);
  border: 1px solid rgba(13, 148, 136, 0.3);
  color: #2dd4bf;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
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

.moderation-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.moderation-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  box-sizing: border-box;
}

.card-header-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  padding-bottom: 1rem;
}

.pet-user-info h3 {
  font-family: var(--font-title);
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
}

.highlight-text {
  color: var(--color-primary);
}

.meta-label {
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.mood-badge {
  font-size: 2rem;
  background: rgba(255, 255, 255, 0.04);
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.comment-block h5, .photo-preview h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

blockquote {
  margin: 0 0 0.5rem 0;
  padding-left: 1rem;
  border-left: 3px solid var(--color-secondary);
  font-style: italic;
  font-size: 0.95rem;
}

.report-date {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  display: block;
}

.photo-preview {
  display: flex;
  flex-direction: column;
}

.image-wrapper {
  max-width: 400px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.preview-img {
  width: 100%;
  height: auto;
  display: block;
}

.card-actions {
  display: flex;
  gap: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  padding-top: 1.25rem;
}

@media (max-width: 500px) {
  .card-actions {
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
  transition: all 0.2s;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

.btn-success {
  background: var(--color-secondary);
  color: white;
}

.btn-success:hover {
  background: #0d9488;
}

.btn-danger {
  background: var(--color-accent);
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
