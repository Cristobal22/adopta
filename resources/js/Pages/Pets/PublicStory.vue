<template>
  <div class="public-story-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <main class="main-content">
      <!-- Success Story Profile Header Card -->
      <section class="profile-card">
        <div class="profile-photo-wrapper">
          <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="profile-img" />
          <div v-else class="profile-emoji-fallback">🐾</div>
          <span class="badge-adopted">Adoptado 💖</span>
        </div>

        <div class="profile-info">
          <h1>La Nueva Vida de <span class="highlight-text">{{ pet.name }}</span></h1>
          <p class="profile-meta">
            {{ pet.species === 'perro' ? '🐕 Perro' : '🐈 Gato' }} • {{ pet.breed || 'Mestizo' }} • {{ pet.age_text }}
          </p>
          <p class="profile-desc" v-if="pet.description">
            {{ pet.description }}
          </p>
          <div class="adoption-milestone" v-if="adoption">
            <span class="milestone-icon">🏠</span>
            <div class="milestone-text">
              <strong>Adoptado el {{ formatDate(adoption.updated_at) }}</strong>
              <span>por una familia amorosa en {{ adoption.commune }} 📍</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Reactions Panel -->
      <section class="reactions-card card">
        <h4>Envía cariño a {{ pet.name }} y su nueva familia</h4>
        <div class="reactions-list">
          <button class="reaction-btn" @click="incrementReaction('love')">
            <span class="react-emoji">💖</span>
            <span class="react-count">{{ reactions.love }}</span>
            <span class="react-label">Me alegra</span>
          </button>
          <button class="reaction-btn" @click="incrementReaction('cheers')">
            <span class="react-emoji">👏</span>
            <span class="react-count">{{ reactions.cheers }}</span>
            <span class="react-label">¡Bravo!</span>
          </button>
          <button class="reaction-btn" @click="incrementReaction('party')">
            <span class="react-emoji">🎉</span>
            <span class="react-count">{{ reactions.party }}</span>
            <span class="react-label">Felicidades</span>
          </button>
        </div>
      </section>

      <!-- Share Pet Story Card -->
      <section class="share-card card">
        <h4>📢 Comparte la historia de {{ pet.name }} en redes sociales</h4>
        <p class="share-desc">Ayúdanos a difundir este caso de éxito para inspirar a más familias a adoptar y apadrinar de forma responsable.</p>
        <div class="share-buttons-row">
          <button @click="shareSocial('whatsapp')" class="btn btn-share-social whatsapp-btn">
            🟢 WhatsApp
          </button>
          <button @click="shareSocial('facebook')" class="btn btn-share-social facebook-btn">
            🔵 Facebook
          </button>
          <button @click="shareSocial('twitter')" class="btn btn-share-social twitter-btn">
            ⚫ Twitter / X
          </button>
          <button @click="shareSocial('native')" class="btn btn-share-social native-btn">
            🔗 Compartir Enlace
          </button>
        </div>
      </section>

      <!-- Success Timeline / Gallery -->
      <section class="timeline-section">
        <h2 class="timeline-title">📈 Bitácoras de Evolución</h2>
        <p class="timeline-subtitle">Sigue de cerca las actualizaciones públicas autorizadas sobre su adaptación en su nuevo hogar.</p>

        <div class="timeline" v-if="diaries.length > 0">
          <div class="timeline-item" v-for="diary in diaries" :key="diary.id">
            <div class="timeline-badge">
              <span class="badge-emoji">{{ diary.emoji_mood }}</span>
            </div>
            <div class="timeline-card card">
              <div class="timeline-header">
                <span class="timeline-date">📅 {{ formatDate(diary.report_date) }}</span>
                <span class="timeline-mood-label">Humor: {{ getMoodLabel(diary.emoji_mood) }}</span>
              </div>
              <blockquote class="timeline-comment">
                "{{ diary.comment }}"
              </blockquote>
              <div class="timeline-photo-wrapper" v-if="diary.photo_path">
                <img :src="'/adoptions/' + adoption.id + '/diaries/' + diary.id + '/photo'" alt="Foto de evolución" class="timeline-img-photo" />
              </div>
            </div>
          </div>
        </div>

        <div class="empty-timeline-card card" v-else>
          <span class="empty-icon">⏳</span>
          <h3>La historia de éxito está comenzando</h3>
          <p>La familia adoptiva aún no ha compartido bitácoras públicas. ¡Vuelve pronto para ver las fotos y novedades de {{ pet.name }}!</p>
        </div>
      </section>

      <!-- CTA Card to Adopt -->
      <section class="cta-bottom-card card">
        <div class="cta-content">
          <h3>¿Quieres ser el héroe de otra mascota?</h3>
          <p>Hay cientos de animales esperando una familia en nuestra plataforma. Encuentra a tu compañero ideal hoy mismo.</p>
          <div class="cta-buttons">
            <Link href="/pets" class="btn btn-primary">🐾 Ver Mascotas en Adopción</Link>
            <Link href="/nosotros" class="btn btn-secondary">Conocer Más</Link>
          </div>
        </div>
      </section>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'PublicStory',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    pet: Object,
    adoption: Object,
    diaries: Array,
  },
  setup(props) {
    // Simular un contador de reacciones locales persistente en localStorage por mascota
    const getStoredReactions = () => {
      const storageKey = `pet_reactions_${props.pet.id}`
      const stored = localStorage.getItem(storageKey)
      if (stored) {
        try {
          return JSON.parse(stored)
        } catch (e) {}
      }
      return { love: 12, cheers: 8, party: 5 } // Fallbacks iniciales simpáticos
    }

    const reactions = ref(getStoredReactions())

    const incrementReaction = (type) => {
      reactions.value[type]++
      localStorage.setItem(`pet_reactions_${props.pet.id}`, JSON.stringify(reactions.value))
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
      })
    }

    const getMoodLabel = (emoji) => {
      const map = {
        '😊': 'Excelente / Muy Feliz',
        '🐕': 'Juguetón / Activo',
        '😐': 'Normal / Adaptado',
        '😞': 'Triste / Tímido',
        '😡': 'Problema de conducta'
      }
      return map[emoji] || 'Adaptándose'
    }

    const shareSocial = (platform) => {
      const base = window.location.origin + (window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '')
      const shareUrl = `${base}/pets/${props.pet.id}/story`
      const shareTitle = `La Nueva Vida de ${props.pet.name} 💖`
      const shareText = `¡Mira cómo le va a ${props.pet.name} en su nuevo hogar! Un caso de éxito de adopción responsable.`

      if (platform === 'whatsapp') {
        window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + ' ' + shareUrl)}`, '_blank')
      } else if (platform === 'facebook') {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank')
      } else if (platform === 'twitter') {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`, '_blank')
      } else if (platform === 'native') {
        if (navigator.share) {
          navigator.share({
            title: shareTitle,
            text: shareText,
            url: shareUrl,
          }).catch(console.error)
        } else {
          navigator.clipboard.writeText(shareUrl).then(() => {
            alert('¡Enlace de la historia copiado al portapapeles!')
          }).catch(err => {
            console.error('Error al copiar el enlace:', err)
          })
        }
      }
    }

    return {
      reactions,
      incrementReaction,
      formatDate,
      getMoodLabel,
      shareSocial,
    }
  }
}
</script>

<style scoped>
.public-story-container {
  min-height: 100vh;
  background-color: var(--color-bg);
  color: var(--color-text-main);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Share Story Card Styles */
.share-card {
  margin-top: 1.5rem;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.share-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.5;
  margin: 0;
}

.share-buttons-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-top: 0.5rem;
}

@media (max-width: 576px) {
  .share-buttons-row {
    flex-direction: column;
    gap: 0.75rem;
  }
}

.btn-share-social {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.75rem 1.25rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

@media (max-width: 576px) {
  .btn-share-social {
    width: 100%;
  }
}

.whatsapp-btn {
  background: rgba(37, 211, 102, 0.1);
  color: #25d366 !important;
  border-color: rgba(37, 211, 102, 0.25);
}

.whatsapp-btn:hover {
  background: rgba(37, 211, 102, 0.2);
  transform: translateY(-2px);
}

.facebook-btn {
  background: rgba(24, 119, 242, 0.1);
  color: #1877f2 !important;
  border-color: rgba(24, 119, 242, 0.25);
}

.facebook-btn:hover {
  background: rgba(24, 119, 242, 0.2);
  transform: translateY(-2px);
}

.twitter-btn {
  background: rgba(255, 255, 255, 0.03);
  color: var(--color-text-main) !important;
  border-color: rgba(255, 255, 255, 0.12);
}

.twitter-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.native-btn {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(255, 107, 74, 0.2);
}

.native-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 74, 0.3);
}

/* Background Blobs */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.08;
  z-index: 0;
  pointer-events: none;
}

.blob-1 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -150px;
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
.public-header {
  position: relative;
  z-index: 10;
  max-width: 1100px;
  width: 100%;
  margin: 0 auto;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-sizing: border-box;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.logo-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: var(--color-text-main);
}

.logo-icon {
  font-size: 1.75rem;
}

.logo-text {
  font-family: var(--font-title);
  font-weight: 800;
  font-size: 1.4rem;
}

.logo-dot {
  color: var(--color-primary);
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-btn {
  text-decoration: none;
  color: var(--color-text-muted);
  font-weight: 600;
  font-size: 0.95rem;
  transition: color 0.2s;
}

.nav-btn:hover {
  color: var(--color-primary);
}

/* Main */
.main-content {
  flex: 1;
  position: relative;
  z-index: 10;
  max-width: 800px;
  width: 100%;
  margin: 0 auto;
  padding: 2.5rem 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

/* Profile Card */
.profile-card {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 2.5rem;
  align-items: center;
}

@media (max-width: 650px) {
  .profile-card {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .profile-photo-wrapper {
    margin: 0 auto;
  }
}

.profile-photo-wrapper {
  position: relative;
  width: 240px;
  height: 240px;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.08);
  border: 4px solid white;
}

.profile-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-emoji-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  font-size: 5rem;
}

.badge-adopted {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-primary);
  color: white;
  font-weight: 800;
  font-size: 0.85rem;
  padding: 0.4rem 1rem;
  border-radius: 99px;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(255, 107, 74, 0.3);
}

.profile-info h1 {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0 0 0.5rem 0;
  line-height: 1.25;
}

.highlight-text {
  color: var(--color-primary);
}

.profile-meta {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-text-muted);
  margin: 0 0 1rem 0;
}

.profile-desc {
  font-size: 0.95rem;
  line-height: 1.6;
  color: var(--color-text-muted);
  margin: 0 0 1.5rem 0;
}

.adoption-milestone {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(13, 148, 136, 0.06);
  border: 1px solid rgba(13, 148, 136, 0.15);
  padding: 0.75rem 1.25rem;
  border-radius: 16px;
  text-align: left;
}

.milestone-icon {
  font-size: 1.5rem;
}

.milestone-text {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
}

.milestone-text strong {
  font-size: 0.95rem;
  color: var(--color-secondary);
}

.milestone-text span {
  color: var(--color-text-muted);
}

/* Card */
.card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 20px;
  padding: 1.75rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.02);
  box-sizing: border-box;
}

/* Reactions */
.reactions-card {
  text-align: center;
}

.reactions-card h4 {
  font-family: var(--font-title);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 1.25rem 0;
}

.reactions-list {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.reaction-btn {
  background: #faf8f5;
  border: 1px solid rgba(0, 0, 0, 0.04);
  border-radius: 16px;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  transition: all 0.2s ease-in-out;
  min-width: 100px;
}

.reaction-btn:hover {
  transform: translateY(-3px);
  background: white;
  border-color: var(--color-primary);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.react-emoji {
  font-size: 1.75rem;
}

.react-count {
  font-weight: 800;
  font-size: 1.1rem;
}

.react-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

/* Timeline */
.timeline-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.timeline-title {
  font-family: var(--font-title);
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0;
}

.timeline-subtitle {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  margin: 0 0 1.5rem 0;
}

.timeline {
  display: flex;
  flex-direction: column;
  position: relative;
  padding-left: 2.5rem;
  box-sizing: border-box;
}

.timeline::before {
  content: '';
  position: absolute;
  top: 10px;
  left: 15px;
  bottom: 10px;
  width: 2px;
  background: rgba(0, 0, 0, 0.06);
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
}

.timeline-badge {
  position: absolute;
  left: -2.5rem;
  top: 10px;
  width: 32px;
  height: 32px;
  background: white;
  border: 2px solid var(--color-secondary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.badge-emoji {
  font-size: 1rem;
}

.timeline-card {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--color-text-muted);
}

.timeline-comment {
  margin: 0;
  padding-left: 1rem;
  border-left: 3px solid var(--color-secondary);
  font-style: italic;
  font-size: 1rem;
  line-height: 1.5;
  color: var(--color-text-main);
}

.timeline-photo-wrapper {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.06);
  max-width: 100%;
}

.timeline-img-photo {
  width: 100%;
  max-height: 400px;
  object-fit: cover;
  display: block;
}

.empty-timeline-card {
  text-align: center;
  padding: 3rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.empty-icon {
  font-size: 2.5rem;
}

/* CTA Card */
.cta-bottom-card {
  text-align: center;
  background: radial-gradient(circle at top right, rgba(255, 107, 74, 0.04), transparent), white;
  border-color: rgba(255, 107, 74, 0.15);
}

.cta-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.cta-content h3 {
  font-family: var(--font-title);
  font-size: 1.4rem;
  font-weight: 800;
  margin: 0;
}

.cta-content p {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  max-width: 500px;
  line-height: 1.5;
  margin: 0 0 1rem 0;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

/* Footer */
.public-footer {
  margin-top: auto;
  text-align: center;
  padding: 2rem;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* General Buttons */
.btn {
  font-family: var(--font-body);
  font-weight: 600;
  padding: 0.6rem 1.4rem;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  display: inline-block;
  text-align: center;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 107, 74, 0.2);
}

.btn-primary:hover {
  background: #e05333;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #faf8f5;
  color: var(--color-text-main);
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.btn-secondary:hover {
  background: #eae6e0;
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}

.btn-block {
  width: 100%;
  box-sizing: border-box;
}
</style>
