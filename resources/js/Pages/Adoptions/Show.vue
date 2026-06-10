<template>
  <div class="backoffice-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <div class="dashboard-layout">
      <!-- Main Content -->
      <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
          <div class="logo">
            <span class="logo-icon">🐾</span>
            <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
          </div>
          <Link href="/adoptions" class="btn btn-secondary btn-sm">Volver al listado</Link>
        </header>

        <!-- Main Dashboard Column Grid -->
        <div class="show-grid">
          <!-- Left Column: Details & Status -->
          <div class="show-column">
            <!-- Header Status Card -->
            <div class="card status-card">
              <span class="status-badge-lg" :class="'badge-' + adoption.status">
                {{ formatStatus(adoption.status) }}
              </span>
              <h1>Adopción de {{ adoption.pet.name }}</h1>
              <p class="meta-info">Solicitud #{{ adoption.id }} • Iniciado el {{ formatDate(adoption.created_at) }}</p>
              
              <div class="match-score-badge">
                <span class="score-percent">{{ Math.round(adoption.compatibility_score) }}%</span>
                <span class="score-label">Compatibilidad</span>
              </div>
            </div>

            <!-- Pet Details Card -->
            <div class="card">
              <h3>Ficha de la Mascota</h3>
              <div class="detail-row">
                <span class="detail-label">Nombre</span>
                <span class="detail-val">{{ adoption.pet.name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Especie / Raza</span>
                <span class="detail-val text-capitalize">{{ adoption.pet.species }} / {{ adoption.pet.breed || 'Mestizo' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Género / Edad</span>
                <span class="detail-val text-capitalize">{{ adoption.pet.gender }} • {{ adoption.pet.age_text }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Microchip</span>
                <span class="detail-val code-text">{{ adoption.pet.microchip_code || 'Sin registrar' }}</span>
              </div>
            </div>

            <!-- Adopter Details Card -->
            <div class="card">
              <h3>Datos del Adoptante</h3>
              <div class="detail-row">
                <span class="detail-label">Nombre Completo</span>
                <span class="detail-val">{{ adoption.adopter.name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Contacto</span>
                <span class="detail-val">{{ adoption.adopter.phone }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Ubicación</span>
                <span class="detail-val">{{ adoption.adopter.address }}, {{ adoption.adopter.city }}</span>
              </div>
            </div>

            <!-- Compatibility Warnings Card (Rescuers/Fundacion/Admin only) -->
            <div class="card match-warnings-card" v-if="isStaff && matchResult && matchResult.reasons.length > 0" style="border-color: rgba(245, 158, 11, 0.3); background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.05), transparent); margin-bottom: 2rem;">
              <h3 style="color: var(--color-warning); font-family: var(--font-title); font-size: 1.25rem; border-left: 3px solid var(--color-warning); padding-left: 0.75rem; margin: 0 0 0.5rem 0;">⚠️ Advertencias de Compatibilidad</h3>
              <p class="card-desc">El algoritmo etológico detectó los siguientes detalles en el emparejamiento con la mascota:</p>
              
              <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.85rem; line-height: 1.5; color: var(--color-text-main);">
                <li v-for="(reason, idx) in matchResult.reasons" :key="idx" style="margin-bottom: 0.5rem;">
                  {{ reason }}
                </li>
              </ul>
            </div>

            <!-- Administrative Actions (Rescuers/Fundacion/Admin only) -->
            <div class="card" v-if="isStaff && adoption.status === 'solicitado'">
              <h3>Evaluación de la Fundación</h3>
              <p class="card-desc">Evalúa al postulante. Al "Aprobar", la solicitud avanzará al estado "En Trámite" para que el adoptante pueda proceder con la firma del contrato.</p>
              
              <div class="action-buttons-group">
                <button class="btn btn-primary" @click="updateStatus('en_proceso')">✓ Aprobar para Firma</button>
                <button class="btn btn-danger" @click="updateStatus('rechazado')">✕ Rechazar Solicitud</button>
              </div>
            </div>
          </div>

          <!-- Right Column: Interactive Contract Signatures -->
          <div class="show-column">
            <!-- If finalized: Contract Certification Seal -->
            <div class="card cert-card" v-if="adoption.status === 'finalizado'">
              <div class="cert-icon">🏆</div>
              <h3>¡Adopción Certificada!</h3>
              <p>El acuerdo de adopción responsable ha sido completado y firmado exitosamente por ambas partes.</p>
              
              <div class="signed-info">
                <div class="detail-row">
                  <span class="detail-label">Tipo de Firma</span>
                  <span class="detail-val text-capitalize">{{ adoption.signature_type === 'digital_canvas' ? 'Firma Digital en Pantalla' : 'Documento Físico Cargado' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Fecha de Cierre</span>
                  <span class="detail-val">{{ formatDate(adoption.updated_at) }}</span>
                </div>
              </div>

              <!-- Contract Template Link (If canvas, download signed HTML directly) -->
              <a :href="'/adoptions/' + adoption.id + '/download-signed'" class="btn btn-primary btn-block">
                📥 Descargar Contrato Firmado
              </a>
            </div>

            <!-- If requested: Pending Approval message for adopters -->
            <div class="card pending-card" v-else-if="!isStaff && adoption.status === 'solicitado'">
              <div class="pending-icon">⏳</div>
              <h3>Solicitud Pendiente de Revisión</h3>
              <p>Tu postulación está siendo evaluada por el rescatista / fundación. Te notificaremos cuando habiliten el contrato de adopción para su firma.</p>
            </div>

            <!-- If rejected -->
            <div class="card rejected-card" v-else-if="adoption.status === 'rechazado'">
              <div class="rejected-icon">✕</div>
              <h3>Postulación Rechazada</h3>
              <p>Esta postulación ha sido denegada. Puedes revisar otras mascotas compatibles con tu estilo de vida en la plataforma.</p>
            </div>

            <!-- IF EN PROCESO (SIGNING HUB FOR ADOPTER) -->
            <div class="card sign-card" v-else-if="!isStaff && adoption.status === 'en_proceso'">
              <h3>Firma del Contrato Legal</h3>
              <p class="card-desc">Has sido seleccionado. Por favor escoge una de las siguientes opciones para firmar tu acuerdo de tenencia responsable:</p>

              <!-- Tabs selector -->
              <div class="tabs">
                <button class="tab-btn" :class="{ active: activeTab === 'canvas' }" @click="activeTab = 'canvas'">
                  🖌️ Firma en Pantalla
                </button>
                <button class="tab-btn" :class="{ active: activeTab === 'upload' }" @click="activeTab = 'upload'">
                  📁 Cargar PDF Firmado
                </button>
              </div>

              <!-- TAB 1: HTML5 CANVAS DRAWING -->
              <div v-show="activeTab === 'canvas'" class="tab-content">
                <p class="tab-desc">Dibuja tu firma utilizando el ratón o tu dedo en pantallas táctiles dentro del siguiente pad:</p>
                
                <div class="canvas-container">
                  <canvas 
                    ref="sigCanvas" 
                    width="450" 
                    height="180" 
                    @mousedown="startDrawing" 
                    @mousemove="draw" 
                    @mouseup="stopDrawing" 
                    @mouseleave="stopDrawing"
                    @touchstart="startDrawingTouch"
                    @touchmove="drawTouch"
                    @touchend="stopDrawing"
                  ></canvas>
                </div>

                <div class="canvas-actions">
                  <button type="button" class="btn btn-secondary btn-sm" @click="clearCanvas">Borrar</button>
                  <button type="button" class="btn btn-primary" @click="submitCanvas">Guardar y Firmar</button>
                </div>
              </div>

              <!-- TAB 2: PHYSICAL PDF DOWNLOAD/UPLOAD -->
              <div v-show="activeTab === 'upload'" class="tab-content">
                <p class="tab-desc">1. Descarga el borrador del contrato que generamos con tus datos:</p>
                <a :href="'/adoptions/' + adoption.id + '/template'" class="btn btn-secondary btn-block" style="margin-bottom: 1.5rem;">
                  📥 Descargar Borrador de Contrato
                </a>

                <p class="tab-desc">2. Firma el documento físicamente y vuelve a subirlo escaneado o fotografiado (PDF, JPG, PNG):</p>
                
                <form @submit.prevent="submitUpload" class="upload-form">
                  <div class="file-dropzone">
                    <input type="file" id="contract_file" @change="handleFileUpload" required />
                    <span class="file-name-label" v-if="selectedFile">{{ selectedFile.name }}</span>
                    <span class="dropzone-text" v-else>Seleccionar archivo...</span>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block" :disabled="uploading">
                    {{ uploading ? 'Cargando...' : 'Subir Contrato Firmado' }}
                  </button>
                </form>
              </div>
            </div>

            <!-- Rescuer waiting on signature view -->
            <div class="card pending-card" v-else-if="isStaff && adoption.status === 'en_proceso'">
              <div class="pending-icon">✍️</div>
              <h3>Esperando Firma del Adoptante</h3>
              <p>Hemos habilitado el contrato. El adoptante está en proceso de firmar el documento de manera digital o cargando el PDF físico.</p>
            </div>

            <!-- SECCIÓN POST-ADOPCIÓN (DIARIOS Y SOS) -->
            <template v-if="adoption.status === 'finalizado'">
              <!-- Alertas de éxito y advertencias -->
              <div class="alert alert-success" v-if="flash.success">
                {{ flash.success }}
              </div>
              <div class="alert alert-warning" v-if="flash.alert_sos">
                ⚠️ Las cosas pueden ser difíciles al principio. Hemos activado guías de adiestramiento de emergencia para apoyarte.
              </div>

              <!-- Guías de Adiestramiento de Emergencia -->
              <div class="card guides-card" v-if="flash.show_sos_guides || showGuidesManual">
                <div class="guides-header">
                  <h3>📚 Guías de Adiestramiento de Emergencia</h3>
                  <button class="btn btn-secondary btn-sm" @click="showGuidesManual = false" v-if="showGuidesManual">✕ Cerrar</button>
                </div>
                <p class="card-desc">Recursos preparados por etólogos y especialistas en comportamiento animal para la contención rápida de conductas.</p>
                <div class="guides-list">
                  <div class="guide-item">
                    <h5>🏠 Guía 1: Adaptación de los Primeros Días</h5>
                    <p>Mantén rutinas claras y tranquilas. Dale a tu mascota un espacio seguro (manta, jaula de transporte abierta o rincón) y no la obligues a interactuar de golpe.</p>
                  </div>
                  <div class="guide-item">
                    <h5>🎾 Guía 2: Ansiedad por Separación</h5>
                    <p>No hagas despedidas ni bienvenidas efusivas. Acostúmbralo a estar solo en periodos cortos y dale juguetes rellenables de comida para mantenerlo concentrado.</p>
                  </div>
                  <div class="guide-item">
                    <h5>💩 Guía 3: Educación del Baño en Casa</h5>
                    <p>Establece horarios estrictos para comida y paseos. Si ocurre un accidente, limpia sin castigar y prémialo con comida cuando lo haga en la zona correcta.</p>
                  </div>
                  <div class="guide-item">
                    <h5>🐕 Guía 4: Paseo y Socialización Positiva</h5>
                    <p>Pasea en horas de baja afluencia si se estresa. Usa correas largas y no tensas para evitar transmitirle tensión. Refuerza positivamente la calma ante estímulos.</p>
                  </div>
                </div>
              </div>

              <!-- Formulario de Reporte Diario de 3 Pasos (Solo Adoptantes) -->
              <div class="card diary-form-card" v-if="!isStaff">
                <h3>Reportar Diario de Adopción (3 Pasos)</h3>
                <p class="card-desc">Ayuda a la fundación a monitorear la adaptación y gana puntos en el Club de Huellas.</p>

                <!-- Indicador de Pasos -->
                <div class="step-indicator">
                  <div class="step-indicator-item" :class="{ active: diaryStep >= 1, completed: diaryStep > 1 }">
                    <span class="step-num">1</span>
                    <span class="step-label">Humor</span>
                  </div>
                  <div class="step-indicator-line" :class="{ completed: diaryStep > 1 }"></div>
                  <div class="step-indicator-item" :class="{ active: diaryStep >= 2, completed: diaryStep > 2 }">
                    <span class="step-num">2</span>
                    <span class="step-label">Comentario</span>
                  </div>
                  <div class="step-indicator-line" :class="{ completed: diaryStep > 2 }"></div>
                  <div class="step-indicator-item" :class="{ active: diaryStep === 3 }">
                    <span class="step-num">3</span>
                    <span class="step-label">Foto</span>
                  </div>
                </div>

                <form @submit.prevent="submitDiary" class="diary-steps-form">
                  <!-- PASO 1: Emojis -->
                  <div v-show="diaryStep === 1" class="step-pane">
                    <label class="step-pane-label">¿Cómo ha estado el estado de ánimo de tu mascota hoy?</label>
                    <div class="emoji-selector">
                      <button 
                        type="button" 
                        class="emoji-option-btn" 
                        v-for="opt in emojiOptions" 
                        :key="opt.value"
                        :class="{ active: diaryForm.emoji_mood === opt.value }"
                        @click="selectEmoji(opt.value)"
                      >
                        <span class="emoji-pic">{{ opt.value }}</span>
                        <span class="emoji-txt">{{ opt.label }}</span>
                      </button>
                    </div>
                    <div class="pane-actions text-center">
                      <button type="button" class="btn btn-primary" :disabled="!diaryForm.emoji_mood" @click="diaryStep = 2">Siguiente Paso →</button>
                    </div>
                  </div>

                  <!-- PASO 2: Comentario -->
                  <div v-show="diaryStep === 2" class="step-pane">
                    <label for="diary_comment_input" class="step-pane-label">Escribe un comentario breve sobre su día:</label>
                    <textarea 
                      id="diary_comment_input" 
                      v-model="diaryForm.comment" 
                      rows="4" 
                      placeholder="Escribe aquí (mínimo 5 caracteres)..."
                    ></textarea>
                    <span v-if="diaryForm.errors.comment" class="error-text">{{ diaryForm.errors.comment }}</span>

                    <div class="pane-actions">
                      <button type="button" class="btn btn-secondary" @click="diaryStep = 1">← Atrás</button>
                      <button type="button" class="btn btn-primary" :disabled="diaryForm.comment.length < 5" @click="diaryStep = 3">Siguiente Paso →</button>
                    </div>
                  </div>

                  <!-- PASO 3: Foto -->
                  <div v-show="diaryStep === 3" class="step-pane">
                    <label class="step-pane-label">Adjunta una foto de su día (opcional):</label>
                    
                    <div class="file-dropzone">
                      <input type="file" @change="handleDiaryPhoto" accept="image/*" />
                      <span class="file-name-label" v-if="diaryPhotoFile">{{ diaryPhotoFile.name }}</span>
                      <span class="dropzone-text" v-else>Seleccionar foto...</span>
                    </div>

                    <!-- NUEVA CASILLA PÚBLICA -->
                    <div class="form-group-checkbox" style="margin-top: 1.25rem; display: flex; align-items: center; gap: 0.5rem; justify-content: center; font-size: 0.9rem;">
                      <input type="checkbox" id="is_public_checkbox" v-model="diaryForm.is_public" style="width: 18px; height: 18px; cursor: pointer;" />
                      <label for="is_public_checkbox" style="cursor: pointer; font-weight: 600;">✨ Compartir públicamente en la historia de éxito de mi mascota</label>
                    </div>

                    <!-- CASILLA DE CONSENTIMIENTO DE FOTO (Solo si es pública y se adjunta foto) -->
                    <div class="form-group-checkbox" v-if="diaryForm.is_public && diaryPhotoFile" style="margin-top: 1rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.5rem; max-width: 450px; margin-left: auto; margin-right: auto; text-align: left; font-size: 0.85rem; background: rgba(255, 107, 74, 0.05); padding: 0.75rem; border-radius: 10px; border: 1px solid rgba(255, 107, 74, 0.15);">
                      <input type="checkbox" id="photo_consent_checkbox" v-model="diaryForm.photo_consent" :required="diaryForm.is_public && !!diaryPhotoFile" style="width: 18px; height: 18px; cursor: pointer; margin-top: 0.1rem; flex-shrink: 0;" />
                      <label for="photo_consent_checkbox" style="cursor: pointer; font-weight: 600; color: var(--color-text-main);">🔒 Acepto y autorizo expresamente que esta fotografía pueda ser publicada en el perfil de éxito público de la mascota.</label>
                    </div>

                    <div class="pane-actions">
                      <button type="button" class="btn btn-secondary" @click="diaryStep = 2">← Atrás</button>
                      <button type="submit" class="btn btn-success" :disabled="diaryForm.processing || (diaryForm.is_public && diaryPhotoFile && !diaryForm.photo_consent)">
                        {{ diaryForm.processing ? 'Enviando...' : '✓ Enviar y Sumar 20 Huellas' }}
                      </button>
                    </div>
                  </div>
                </form>

                <!-- SOS Button inside Adopter's Follow-up -->
                <div class="sos-block">
                  <div class="sos-divider"></div>
                  <div class="sos-footer">
                    <p class="sos-text">⚠️ ¿Estás teniendo dificultades graves de comportamiento o adaptación que te hacen pensar en la devolución?</p>
                    <button type="button" class="btn btn-danger btn-block" @click="triggerSos">
                      🚨 Activar Auxilio S.O.S
                    </button>
                  </div>
                </div>
              </div>

              <!-- Card para Subir Certificado de Esterilización (Solo Adoptante y si está finalizado y pendiente) -->
              <div class="card sterilization-card" v-if="!isStaff && !hasSterilization" style="border-color: rgba(14, 165, 233, 0.3); background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.05), transparent); margin-bottom: 2rem;">
                <h3 class="sterilization-title" style="color: var(--color-secondary); font-family: var(--font-title); font-size: 1.25rem; border-left: 3px solid var(--color-secondary); padding-left: 0.75rem; margin: 0 0 0.5rem 0;">✂️ Compromiso de Esterilización Obligatorio</h3>
                <p class="card-desc">
                  Esta mascota aún no registra esterilización en su historial clínico. Para dar cumplimiento a las cláusulas del contrato, por favor sube el certificado veterinario de castración/esterilización.
                </p>

                <form @submit.prevent="submitSterilization" class="upload-form">
                  <div class="file-dropzone">
                    <input type="file" @change="handleSterilizationFile" accept="image/*,application/pdf" required />
                    <span class="file-name-label" v-if="sterilizationFile">{{ sterilizationFile.name }}</span>
                    <span class="dropzone-text" v-else>Seleccionar certificado (PDF, JPG, PNG)...</span>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block" :disabled="submittingSterilization">
                    {{ submittingSterilization ? 'Subiendo...' : 'Enviar Certificado Veterinario' }}
                  </button>
                </form>
              </div>

              <!-- Historial de Diarios Registrados -->
              <div class="card diaries-history-card">
                <div class="history-header">
                  <h3>Historial de Diarios</h3>
                  <button 
                    v-if="!isStaff && (!flash.show_sos_guides && !showGuidesManual)"
                    class="btn btn-secondary btn-sm" 
                    @click="showGuidesManual = true"
                  >
                    📚 Ver Guías
                  </button>
                </div>
                <p class="card-desc">Reportes de seguimiento enviados por la familia adoptante para este caso.</p>

                <div class="diaries-list" v-if="adoption.diaries && adoption.diaries.length > 0">
                  <div class="diary-item-card" v-for="d in adoption.diaries" :key="d.id">
                    <div class="diary-item-header">
                      <div class="diary-mood-info">
                        <span class="diary-emoji">{{ d.emoji_mood }}</span>
                        <span class="diary-date">📅 {{ formatDate(d.report_date) }}</span>
                      </div>

                      <!-- Alertas de IA -->
                      <div class="ai-warnings">
                        <span class="warning-tag" v-if="d.ai_sentiment_score < -0.2">
                          ⚠️ Alerta: Sentimiento Negativo (Score: {{ d.ai_sentiment_score }})
                        </span>
                        <span class="danger-tag" v-if="d.ai_abuse_flag && isStaff">
                          🚨 ALERTA: Posible Maltrato Detectado
                        </span>
                      </div>
                    </div>

                    <p class="diary-comment-text">"{{ d.comment }}"</p>

                    <!-- Foto del diario -->
                    <div class="diary-photo-wrapper" v-if="d.photo_path">
                      <img :src="'/adoptions/' + adoption.id + '/diaries/' + d.id + '/photo'" alt="Foto del diario" class="diary-photo-img" />
                    </div>

                    <!-- Badges de compartición y moderación -->
                    <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;" v-if="d.is_public">
                      <span style="font-size: 0.75rem; font-weight: 700; background: rgba(13, 148, 136, 0.1); border: 1px solid rgba(13, 148, 136, 0.3); color: #0d9488; padding: 0.2rem 0.5rem; border-radius: 6px;">
                        📢 Compartido públicamente
                      </span>
                      <span style="font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px;" :style="d.moderation_status === 'approved' ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981;' : (d.moderation_status === 'rejected' ? 'background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e;' : 'background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b;')">
                        {{ d.moderation_status === 'approved' ? 'Aprobado por Moderación ✓' : (d.moderation_status === 'rejected' ? 'Rechazado' : 'Pendiente de Moderación ⏳') }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="empty-state-sm" v-else>
                  Aún no se han enviado diarios de seguimiento para esta mascota.
                </div>
              </div>
            </template>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'

export default {
  name: 'Show',
  components: {
    Link,
  },
  props: {
    adoption: Object,
    matchResult: Object,
  },
  setup(props) {
    const userRole = ref(usePage().props.auth?.user?.role || 'adoptante')
    const isStaff = computed(() => ['admin', 'fundacion', 'rescatista'].includes(userRole.value))
    const activeTab = ref('canvas')
    
    // Canvas Refs & states
    const sigCanvas = ref(null)
    const isDrawing = ref(false)
    const context = ref(null)
    const hasDrawn = ref(false)

    // File upload
    const selectedFile = ref(null)
    const uploading = ref(false)

    onMounted(() => {
      if (sigCanvas.value) {
        context.value = sigCanvas.value.getContext('2d')
        context.value.strokeStyle = '#ffffff'
        context.value.lineWidth = 3
        context.value.lineCap = 'round'
      }
    })

    // Mouse drawing actions
    const startDrawing = (e) => {
      isDrawing.value = true
      const rect = sigCanvas.value.getBoundingClientRect()
      const x = (e.clientX - rect.left) * (sigCanvas.value.width / rect.width)
      const y = (e.clientY - rect.top) * (sigCanvas.value.height / rect.height)
      context.value.beginPath()
      context.value.moveTo(x, y)
    }

    const draw = (e) => {
      if (!isDrawing.value) return
      const rect = sigCanvas.value.getBoundingClientRect()
      const x = (e.clientX - rect.left) * (sigCanvas.value.width / rect.width)
      const y = (e.clientY - rect.top) * (sigCanvas.value.height / rect.height)
      context.value.lineTo(x, y)
      context.value.stroke()
      hasDrawn.value = true
    }

    const stopDrawing = () => {
      isDrawing.value = false
    }

    // Touch drawing actions for mobile support
    const getTouchPos = (e) => {
      const rect = sigCanvas.value.getBoundingClientRect()
      return {
        x: (e.touches[0].clientX - rect.left) * (sigCanvas.value.width / rect.width),
        y: (e.touches[0].clientY - rect.top) * (sigCanvas.value.height / rect.height)
      }
    }

    const startDrawingTouch = (e) => {
      e.preventDefault()
      isDrawing.value = true
      const pos = getTouchPos(e)
      context.value.beginPath()
      context.value.moveTo(pos.x, pos.y)
    }

    const drawTouch = (e) => {
      e.preventDefault()
      if (!isDrawing.value) return
      const pos = getTouchPos(e)
      context.value.lineTo(pos.x, pos.y)
      context.value.stroke()
      hasDrawn.value = true
    }

    const clearCanvas = () => {
      context.value.clearRect(0, 0, sigCanvas.value.width, sigCanvas.value.height)
      hasDrawn.value = false
    }

    const submitCanvas = () => {
      if (!hasDrawn.value) {
        alert('Por favor dibuja tu firma antes de guardar.')
        return
      }

      const signatureData = sigCanvas.value.toDataURL('image/png')
      router.post(`/adoptions/${props.adoption.id}/sign-canvas`, {
        signature_data: signatureData,
      })
    }

    // File uploads
    const handleFileUpload = (e) => {
      selectedFile.value = e.target.files[0]
    }

    const submitUpload = () => {
      if (!selectedFile.value) {
        alert('Por favor selecciona un archivo PDF o imagen del contrato firmado.')
        return
      }

      uploading.value = true
      const formData = new FormData()
      formData.append('contract_file', selectedFile.value)

      router.post(`/adoptions/${props.adoption.id}/sign-upload`, formData, {
        onFinish: () => {
          uploading.value = false
        }
      })
    }

    // Administration status changes
    const updateStatus = (newStatus) => {
      if (confirm(`¿Estás seguro de cambiar el estado de la adopción a "${newStatus === 'en_proceso' ? 'Aprobado para Firma' : 'Rechazado'}"?`)) {
        router.post(`/adoptions/${props.adoption.id}/status`, {
          status: newStatus,
        })
      }
    }

    const formatStatus = (statusVal) => {
      const map = {
        solicitado: 'Postulación Recibida',
        en_proceso: 'En Trámite de Firma',
        aprobado: 'Aprobado',
        rechazado: 'Rechazado',
        finalizado: 'Adopción Finalizada ✓',
      }
      return map[statusVal] || statusVal
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }

    // Variables y métodos para Diario de Seguimiento Post-Adopción y SOS
    const diaryStep = ref(1)
    const diaryPhotoFile = ref(null)
    const showGuidesManual = ref(false)

    const page = usePage()
    const flash = computed(() => page.props.flash || {})

    const diaryForm = useForm({
      emoji_mood: '',
      comment: '',
      photo: null,
      is_public: false,
      photo_consent: false,
    })

    const emojiOptions = [
      { value: '😊', label: 'Excelente' },
      { value: '🐕', label: 'Juguetón' },
      { value: '😐', label: 'Normal' },
      { value: '😞', label: 'Triste' },
      { value: '😡', label: 'Problema' }
    ]

    const selectEmoji = (emoji) => {
      diaryForm.emoji_mood = emoji
      diaryStep.value = 2
    }

    const handleDiaryPhoto = (e) => {
      diaryPhotoFile.value = e.target.files[0]
      diaryForm.photo = e.target.files[0]
    }

    const submitDiary = () => {
      diaryForm.post(`/adoptions/${props.adoption.id}/diaries`, {
        onSuccess: () => {
          diaryForm.reset()
          diaryStep.value = 1
          diaryPhotoFile.value = null
        }
      })
    }

    const triggerSos = () => {
      if (confirm('¿Estás teniendo dificultades críticas de adaptación o comportamiento? Si confirmas, se enviará una alerta S.O.S de ayuda y se habilitarán guías de adiestramiento inmediato.')) {
        router.post(`/adoptions/${props.adoption.id}/sos`, {}, {
          onSuccess: () => {
            showGuidesManual.value = true
          }
        })
      }
    }

    // Sterilization certificate states and methods
    const sterilizationFile = ref(null)
    const submittingSterilization = ref(false)

    const hasSterilization = computed(() => {
      const history = props.adoption.pet?.clinical_history || []
      return history.some(event => event.type === 'esterilizacion')
    })

    const handleSterilizationFile = (e) => {
      sterilizationFile.value = e.target.files[0]
    }

    const submitSterilization = () => {
      if (!sterilizationFile.value) {
        alert('Por favor selecciona un certificado veterinario de esterilización.')
        return
      }

      submittingSterilization.value = true
      const formData = new FormData()
      formData.append('sterilization_certificate', sterilizationFile.value)

      router.post(`/adoptions/${props.adoption.id}/upload-sterilization`, formData, {
        onSuccess: () => {
          sterilizationFile.value = null
        },
        onFinish: () => {
          submittingSterilization.value = false
        }
      })
    }

    return {
      userRole,
      isStaff,
      activeTab,
      sigCanvas,
      sterilizationFile,
      submittingSterilization,
      hasSterilization,
      handleSterilizationFile,
      submitSterilization,
      startDrawing,
      draw,
      stopDrawing,
      startDrawingTouch,
      drawTouch,
      clearCanvas,
      submitCanvas,
      selectedFile,
      handleFileUpload,
      submitUpload,
      uploading,
      updateStatus,
      formatStatus,
      formatDate,
      diaryStep,
      diaryPhotoFile,
      showGuidesManual,
      flash,
      diaryForm,
      emojiOptions,
      selectEmoji,
      handleDiaryPhoto,
      submitDiary,
      triggerSos,
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

/* Show Grid */
.show-grid {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 2rem;
  box-sizing: border-box;
  align-items: flex-start;
}

@media (max-width: 968px) {
  .show-grid {
    grid-template-columns: 1fr;
  }
}

.show-column {
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
  line-height: 1.4;
  margin: -0.5rem 0 0 0;
}

/* Status Header Card */
.status-card {
  align-items: center;
  text-align: center;
  background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
}

.status-card h2 {
  font-family: var(--font-title);
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
}

.meta-info {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin: 0;
}

.match-score-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(139, 92, 246, 0.1);
  border: 1px solid rgba(139, 92, 246, 0.2);
  width: 90px;
  height: 90px;
  border-radius: 50%;
  margin-top: 0.5rem;
}

.score-percent {
  font-family: var(--font-title);
  font-size: 1.5rem;
  font-weight: 800;
  color: #c084fc;
}

.score-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-text-muted);
}

/* Detail Rows */
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  padding-bottom: 0.75rem;
  font-size: 0.9rem;
}

.detail-row:last-child {
  border: none;
  padding-bottom: 0;
}

.detail-label {
  color: var(--color-text-muted);
  font-weight: 500;
}

.detail-val {
  font-weight: 600;
  color: var(--color-text-main);
}

.code-text {
  font-family: monospace;
  background: rgba(255, 255, 255, 0.05);
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.85rem;
}

/* Action Buttons Grid */
.action-buttons-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

@media (max-width: 480px) {
  .action-buttons-group {
    grid-template-columns: 1fr;
  }
}

/* Verification Seals */
.cert-card {
  align-items: center;
  text-align: center;
  border-color: rgba(16, 185, 129, 0.3);
  background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.05), transparent);
}

.cert-icon {
  font-size: 3rem;
  animation: bounce 2s infinite;
}

.signed-info {
  width: 100%;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 1.25rem;
  border-radius: 12px;
  box-sizing: border-box;
}

/* Pending Card */
.pending-card, .rejected-card {
  align-items: center;
  text-align: center;
  padding: 3rem 2rem;
}

.pending-icon { font-size: 3rem; color: var(--color-warning); animation: pulse 2s infinite; }
.rejected-icon { font-size: 3rem; color: var(--color-accent); }

/* Tabs selector */
.tabs {
  display: flex;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.3rem;
}

.tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-family: var(--font-body);
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.6rem 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: var(--color-primary);
  color: white;
  box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
}

.tab-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-top: 0.5rem;
}

.tab-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

/* Signature Pad Canvas */
.canvas-container {
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.2);
  height: 180px;
  width: 100%;
}

canvas {
  display: block;
  cursor: crosshair;
  touch-action: none;
  width: 100%;
  height: 100%;
}

.canvas-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Upload zone */
.upload-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.file-dropzone {
  position: relative;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.01);
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.file-dropzone:hover {
  border-color: var(--color-primary);
  background: rgba(139, 92, 246, 0.02);
}

.file-dropzone input[type="file"] {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.file-name-label {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--color-secondary);
}

.dropzone-text {
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

/* Badges */
.status-badge-lg {
  display: inline-block;
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.badge-solicitado {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--color-text-muted);
}

.badge-en_proceso {
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
}

.badge-finalizado {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
}

.badge-rechazado {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: var(--color-accent);
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

.btn-danger {
  background: rgba(244, 63, 94, 0.1);
  color: var(--color-accent);
  border: 1px solid rgba(244, 63, 94, 0.2);
}

.btn-danger:hover {
  background: var(--color-accent);
  color: white;
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

/* Animations */
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Alerts */
.alert {
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.4;
  margin-bottom: 1.5rem;
  border: 1px solid transparent;
}
.alert-success {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.2);
  color: var(--color-success);
}
.alert-warning {
  background: rgba(245, 158, 11, 0.1);
  border-color: rgba(245, 158, 11, 0.2);
  color: var(--color-warning);
}

/* Guides Card */
.guides-card {
  border-color: rgba(139, 92, 246, 0.3);
  background: radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.05), transparent);
}
.guides-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.guides-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.guide-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 1rem;
  border-radius: 10px;
}
.guide-item h5 {
  margin: 0 0 0.4rem 0;
  font-family: var(--font-title);
  font-size: 0.95rem;
  color: var(--color-secondary);
}
.guide-item p {
  margin: 0;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.4;
}

/* Step Indicator */
.step-indicator {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 0.5rem 0 1.5rem 0;
  padding: 0 1rem;
}
.step-indicator-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  z-index: 2;
}
.step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--color-text-muted);
  font-size: 0.85rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}
.step-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.step-indicator-item.active .step-num {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
  box-shadow: 0 0 12px rgba(139, 92, 246, 0.4);
}
.step-indicator-item.active .step-label {
  color: var(--color-text-main);
}
.step-indicator-item.completed .step-num {
  background: var(--color-success);
  border-color: var(--color-success);
  color: white;
}
.step-indicator-line {
  flex: 1;
  height: 2px;
  background: rgba(255, 255, 255, 0.05);
  margin-top: -12px;
  z-index: 1;
  transition: all 0.3s ease;
}
.step-indicator-line.completed {
  background: var(--color-success);
}

/* Emoji Selector */
.emoji-selector {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.75rem;
  margin: 1.5rem 0;
}
.emoji-option-btn {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 0.75rem 0.5rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  transition: all 0.2s ease;
}
.emoji-option-btn:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}
.emoji-option-btn.active {
  background: rgba(139, 92, 246, 0.1);
  border-color: var(--color-primary);
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);
}
.emoji-pic {
  font-size: 1.75rem;
}
.emoji-txt {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-muted);
}
.emoji-option-btn.active .emoji-txt {
  color: var(--color-primary);
}

/* Step Panes */
.step-pane {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.step-pane-label {
  font-size: 0.9rem;
  font-weight: 600;
}
.step-pane textarea {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  outline: none;
  resize: vertical;
  transition: all 0.3s ease;
  box-sizing: border-box;
}
.step-pane textarea:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}
.pane-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}
.pane-actions.text-center {
  justify-content: center;
}

/* SOS Block */
.sos-block {
  margin-top: 1.5rem;
}
.sos-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  margin-bottom: 1.5rem;
}
.sos-footer {
  background: rgba(244, 63, 94, 0.03);
  border: 1px solid rgba(244, 63, 94, 0.1);
  padding: 1.25rem;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-sizing: border-box;
}
.sos-text {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  line-height: 1.4;
  margin: 0;
}

/* History */
.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.diaries-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 0.5rem;
}
.diary-item-card {
  background: rgba(255, 255, 255, 0.015);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.diary-item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.diary-mood-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.diary-emoji {
  font-size: 1.5rem;
}
.diary-date {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}
.ai-warnings {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  align-items: flex-end;
}
.warning-tag {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  color: var(--color-warning);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}
.danger-tag {
  background: rgba(244, 63, 94, 0.1);
  border: 1px solid rgba(244, 63, 94, 0.2);
  color: var(--color-accent);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}
.diary-comment-text {
  font-size: 0.85rem;
  line-height: 1.45;
  color: var(--color-text-main);
  margin: 0;
}
.diary-photo-wrapper {
  margin-top: 0.5rem;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.05);
  max-width: 100%;
}
.diary-photo-img {
  display: block;
  max-height: 220px;
  width: 100%;
  object-fit: cover;
}
</style>
