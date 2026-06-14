<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">
      
      <!-- Section Header -->
      <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
          <h1>Generador de Flyers de Adopción</h1>
          <p class="subtitle">Personaliza y descarga una gráfica profesional de <strong>{{ pet.name }}</strong> optimizada para tus historias y estados de redes sociales.</p>
        </div>
        <div>
          <Link href="/pets" class="btn btn-secondary btn-sm">Volver al listado</Link>
        </div>
      </div>

      <!-- Generator Columns Grid -->
      <div class="generator-grid">
        
        <!-- Left Column: Live Canvas Preview -->
        <div class="preview-column">
          <div class="card preview-card">
            <h3>👁️ Vista Previa del Flyer</h3>
            <p class="card-desc">Resolución real de exportación: 1080x1920 px (9:16 vertical).</p>
            
            <div class="canvas-wrapper">
              <!-- Hidden canvas doing the high-res drawing -->
              <canvas ref="flyerCanvas" width="1080" height="1920" style="display: none;"></canvas>
              
              <!-- Exported image for smooth CSS rendering and mobile long-press save -->
              <div class="flyer-img-container">
                <img v-if="renderedImageUrl" :src="renderedImageUrl" alt="Flyer de Adopción" class="flyer-preview-img" />
                <div v-else class="canvas-loading">
                  <span class="loading-icon">🐾</span>
                  <span>Generando gráfica...</span>
                </div>
              </div>
            </div>

            <div class="download-block" style="margin-top: 1rem;">
              <a :href="renderedImageUrl" :download="'flyer_adopcion_' + pet.name.toLowerCase() + '.png'" class="btn btn-primary btn-block btn-download" v-if="renderedImageUrl">
                📥 Descargar Imagen de Flyer (PNG)
              </a>
              <p class="mobile-tip" v-if="renderedImageUrl">💡 En móviles, también puedes mantener presionada la imagen para guardarla directamente en tu galería.</p>
            </div>
          </div>
        </div>

        <!-- Right Column: Personalization Panel -->
        <div class="controls-column">
          <div class="card controls-card">
            <h3>🎨 Personalizar Diseño</h3>
            
            <!-- Template Type Select -->
            <div class="form-group">
              <label>Seleccionar Estilo de Plantilla</label>
              <div class="template-selector-row">
                <button 
                  type="button" 
                  class="template-option-btn" 
                  :class="{ active: templateType === 'polaroid' }"
                  @click="changeTemplate('polaroid')"
                >
                  <span class="template-icon">📸</span>
                  <div class="template-text">
                    <strong>Estilo Polaroid</strong>
                    <span>Scrapbook y notas</span>
                  </div>
                </button>

                <button 
                  type="button" 
                  class="template-option-btn" 
                  :class="{ active: templateType === 'ficha' }"
                  @click="changeTemplate('ficha')"
                >
                  <span class="template-icon">📋</span>
                  <div class="template-text">
                    <strong>Estilo Ficha</strong>
                    <span>Infográfico completo</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Custom text inputs -->
            <div class="form-group" style="margin-top: 1rem;">
              <label for="contact-name">Nombre de Organización / Contacto</label>
              <input type="text" id="contact-name" v-model="contactName" placeholder="Ej. @sitioincreible..." @input="debouncedRender" />
            </div>

            <div class="form-group">
              <label for="contact-phone">Teléfono / WhatsApp de Contacto</label>
              <input type="text" id="contact-phone" v-model="contactPhone" placeholder="Ej. +56 9 1234 5678" @input="debouncedRender" />
            </div>

            <div class="form-group">
              <label for="custom-slogan">Lema o Slogan Corto</label>
              <input type="text" id="custom-slogan" v-model="customSlogan" placeholder="Ej. Cambia su vida, cambia la tuya." @input="debouncedRender" />
            </div>

            <div class="form-group">
              <label>Características a Destacar</label>
              <p class="checkbox-hint">Selecciona hasta 5 atributos clave para mostrar en la tarjeta de detalles:</p>
              
              <div class="features-checklist">
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.vaccinated" @change="debouncedRender" />
                  Vacunado/a
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.sterilized" @change="debouncedRender" />
                  Esterilizado/a
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.chipped" @change="debouncedRender" />
                  Con Chip registrado
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.kids" @change="debouncedRender" />
                  Apto con niños
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.dogs" @change="debouncedRender" />
                  Amigable con perros
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.cats" @change="debouncedRender" />
                  Amigable con gatos
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.playful" @change="debouncedRender" />
                  Muy juguetón/a
                </label>
                <label class="feature-check-label">
                  <input type="checkbox" v-model="selectedFeatures.calm" @change="debouncedRender" />
                  Tranquilo y leal
                </label>
              </div>
            </div>

            <hr class="separator" />

            <!-- Pro-tip info banner -->
            <div class="alert alert-info-style">
              <strong>💡 Consejo Profesional:</strong> Puedes cambiar la foto cargada editando el perfil de la mascota desde la gestión del backoffice. Recomendamos usar fotos cuadradas o bien centradas con fondo limpio para un resultado espectacular.
            </div>
          </div>
        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Flyer',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    pet: Object,
  },
  setup(props) {
    const flyerCanvas = ref(null)
    const renderedImageUrl = ref('')
    const templateType = ref('polaroid')

    // Campos de personalización
    const contactName = ref('Organización Empatía Animal')
    const contactPhone = ref('+56 9 7680 0165')
    const customSlogan = ref('¡Cambia su vida, cambia la tuya!')

    // Características seleccionadas
    const selectedFeatures = ref({
      vaccinated: true,
      sterilized: true,
      chipped: props.pet.microchip_code ? true : false,
      kids: props.pet.characteristics?.kids || false,
      dogs: props.pet.characteristics?.dogs || false,
      cats: props.pet.characteristics?.cats || false,
      playful: props.pet.characteristics?.energy >= 4 ? true : false,
      calm: props.pet.characteristics?.energy <= 2 ? true : true,
    })

    let petImageObj = null
    let renderTimeout = null

    // Cambiar plantilla
    const changeTemplate = (type) => {
      templateType.value = type
      drawFlyer()
    }

    // Dibujado en diferido (debounce) para optimizar performance al escribir inputs
    const debouncedRender = () => {
      if (renderTimeout) clearTimeout(renderTimeout)
      renderTimeout = setTimeout(() => {
        drawFlyer()
      }, 300)
    }

    // Dibujar el flyer en el Canvas
    const drawFlyer = () => {
      const canvas = flyerCanvas.value
      if (!canvas) return
      const ctx = canvas.getContext('2d')
      
      // Limpiar lienzo
      ctx.clearRect(0, 0, canvas.width, canvas.height)

      if (templateType.value === 'polaroid') {
        drawPolaroidTemplate(canvas, ctx)
      } else {
        drawFichaTemplate(canvas, ctx)
      }
    }

    // 1. DIBUJAR PLANTILLA POLAROID (Scrapbook)
    const drawPolaroidTemplate = (canvas, ctx) => {
      // A. Fondo con textura orgánica de papel o degradado cálido
      const grad = ctx.createLinearGradient(0, 0, 0, canvas.height)
      grad.addColorStop(0, '#e5efe6') // Verde pastel de fondo
      grad.addColorStop(1, '#d0e0d2')
      ctx.fillStyle = grad
      ctx.fillRect(0, 0, canvas.width, canvas.height)

      // Decoración: Formas abstractas (hojas orgánicas de fondo en el scrapbook)
      ctx.fillStyle = 'rgba(13, 148, 136, 0.08)' // Verde esmeralda suave
      
      // Hoja arriba izquierda
      ctx.beginPath()
      ctx.ellipse(100, 150, 250, 180, Math.PI / 4, 0, 2 * Math.PI)
      ctx.fill()

      // Hoja arriba derecha
      ctx.fillStyle = 'rgba(255, 107, 74, 0.06)' // Naranja coral suave
      ctx.beginPath()
      ctx.ellipse(980, 100, 220, 220, -Math.PI / 6, 0, 2 * Math.PI)
      ctx.fill()

      // B. Rectángulo Blanco Cabecera (Título principal)
      ctx.save()
      ctx.fillStyle = '#ffffff'
      ctx.shadowColor = 'rgba(0,0,0,0.06)'
      ctx.shadowBlur = 15
      ctx.shadowOffsetY = 8
      ctx.fillRect(80, 80, 920, 240)
      ctx.restore()

      // Título en el rectángulo blanco
      ctx.fillStyle = '#ff6b4a' // Coral principal
      ctx.font = "bold 78px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.textAlign = 'center'
      ctx.fillText("ADOPCIÓN", canvas.width / 2, 175)
      
      ctx.fillStyle = '#115e59' // Verde esmeralda oscuro
      ctx.font = "bold 82px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText("RESPONSABLE", canvas.width / 2, 265)

      // C. Dibujar Foto en Polaroid (Centro)
      const polWidth = 620
      const polHeight = 720
      const polX = canvas.width / 2 - polWidth / 2 - 20
      const polY = 400
      const angle = -0.04 // Rotación de -2.3 grados para simular scrapbook hecho a mano

      ctx.save()
      ctx.translate(polX + polWidth / 2, polY + polHeight / 2)
      ctx.rotate(angle)
      
      // Caja blanca polaroid
      ctx.fillStyle = '#ffffff'
      ctx.shadowColor = 'rgba(0, 0, 0, 0.12)'
      ctx.shadowBlur = 25
      ctx.shadowOffsetY = 15
      ctx.fillRect(-polWidth / 2, -polHeight / 2, polWidth, polHeight)

      // Dibujar imagen de la mascota
      if (petImageObj && petImageObj.complete) {
        const imgSize = 540
        const imgX = -polWidth / 2 + 40
        const imgY = -polHeight / 2 + 40

        ctx.save()
        // Clip para recortar la foto cuadrada
        ctx.beginPath()
        ctx.rect(imgX, imgY, imgSize, imgSize)
        ctx.clip()

        // Calcular aspect ratio de la imagen para centrar el recorte (object-fit: cover)
        const imgRatio = petImageObj.width / petImageObj.height
        let dw = imgSize
        let dh = imgSize
        let dx = imgX
        let dy = imgY

        if (imgRatio > 1) {
          dw = imgSize * imgRatio
          dx = imgX - (dw - imgSize) / 2
        } else {
          dh = imgSize / imgRatio
          dy = imgY - (dh - imgSize) / 2
        }

        ctx.drawImage(petImageObj, dx, dy, dw, dh)
        ctx.restore()
      } else {
        // Fallback si no hay foto
        ctx.fillStyle = '#faf8f5'
        ctx.fillRect(-polWidth / 2 + 40, -polHeight / 2 + 40, 540, 540)
        ctx.fillStyle = '#ff6b4a'
        ctx.font = "bold 200px Arial"
        ctx.textAlign = 'center'
        ctx.textBaseline = 'middle'
        ctx.fillText("🐾", 0, -40)
      }

      // Nombre en polaroid (Abajo)
      ctx.fillStyle = '#0f172a'
      ctx.font = "bold 65px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.textAlign = 'center'
      ctx.fillText(props.pet.name.toUpperCase(), 0, 275)
      ctx.restore()

      // Dibujar Cinta Adhesiva (Washi Tape) sobre el Polaroid
      ctx.save()
      ctx.translate(canvas.width / 2 - 30, 390)
      ctx.rotate(0.12)
      ctx.fillStyle = 'rgba(253, 224, 71, 0.45)' // Amarillo translúcido
      ctx.fillRect(-120, -25, 240, 50)
      ctx.restore()

      // D. Tarjeta Adhesiva de Atributos (Abajo Izquierda)
      const cardW = 500
      const cardH = 520
      const cardX = 90
      const cardY = 1200

      ctx.save()
      ctx.fillStyle = '#fffdf0' // Color amarillo nota
      ctx.shadowColor = 'rgba(0, 0, 0, 0.08)'
      ctx.shadowBlur = 15
      ctx.shadowOffsetY = 8
      ctx.fillRect(cardX, cardY, cardW, cardH)

      // Cinta adhesiva nota
      ctx.restore()
      ctx.save()
      ctx.translate(cardX + cardW / 2, cardY + 5)
      ctx.rotate(-0.05)
      ctx.fillStyle = 'rgba(251, 146, 60, 0.35)' // Naranja translúcido
      ctx.fillRect(-80, -20, 160, 40)
      ctx.restore()

      // Textos nota adhesiva
      ctx.fillStyle = '#1d4ed8' // Azul tipo lápiz pasta
      ctx.font = "bold 44px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.textAlign = 'left'
      ctx.fillText(`"${props.pet.name}"`, cardX + 50, cardY + 90)

      ctx.font = "600 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#334155'
      
      // Construir lista de atributos según selección
      const items = []
      items.push(`Edad: ${props.pet.age_text || 'Sin especificar'}`)
      
      if (selectedFeatures.value.sterilized) {
        items.push('✓ Esterilizado/a')
      } else {
        items.push('• Sin Esterilizar')
      }

      if (selectedFeatures.value.vaccinated) {
        items.push('✓ Vacunas al día')
      }

      if (selectedFeatures.value.chipped) {
        items.push('✓ Chip Registrado')
      }

      if (selectedFeatures.value.kids) {
        items.push('✓ Apto con niños')
      } else if (selectedFeatures.value.dogs) {
        items.push('✓ Amigable con perros')
      } else if (selectedFeatures.value.cats) {
        items.push('✓ Amigable con gatos')
      } else if (selectedFeatures.value.playful) {
        items.push('✓ Muy Juguetón/a')
      } else {
        items.push(`Talla: ${props.pet.characteristics?.space === 'grande' ? 'Grande' : (props.pet.characteristics?.space === 'pequeño' ? 'Pequeña' : 'Mediana')}`)
      }

      // Dibujar máximo 5 líneas
      items.slice(0, 5).forEach((item, index) => {
        ctx.fillText(item, cardX + 50, cardY + 180 + (index * 72))
      })

      // E. Datos de contacto (Abajo Derecha)
      ctx.fillStyle = '#0f172a'
      ctx.textAlign = 'right'
      ctx.font = "bold 38px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText("INFORMES CON:", 970, 1480)

      ctx.fillStyle = '#ff6b4a'
      ctx.font = "bold 48px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText(contactName.value, 970, 1550)

      ctx.fillStyle = '#0d9488'
      ctx.font = "bold 46px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText(contactPhone.value, 970, 1625)

      // Slogan en pie de página
      ctx.fillStyle = '#1e293b'
      ctx.font = "italic 600 38px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.textAlign = 'center'
      ctx.fillText(`"${customSlogan.value}"`, canvas.width / 2, 1820)

      // Actualizar imagen para descargar
      renderedImageUrl.value = canvas.toDataURL('image/png')
    }

    // 2. DIBUJAR PLANTILLA FICHA TÉCNICA (Infográfica)
    const drawFichaTemplate = (canvas, ctx) => {
      // A. Fondo crema elegante
      ctx.fillStyle = '#fdfbf7'
      ctx.fillRect(0, 0, canvas.width, canvas.height)

      // Círculos decorativos en las esquinas
      ctx.fillStyle = 'rgba(255, 107, 74, 0.05)'
      ctx.beginPath()
      ctx.arc(0, 0, 300, 0, 2 * Math.PI)
      ctx.fill()

      ctx.fillStyle = 'rgba(13, 148, 136, 0.04)'
      ctx.beginPath()
      ctx.arc(canvas.width, canvas.height, 400, 0, 2 * Math.PI)
      ctx.fill()

      // B. Encabezado llamativo
      ctx.fillStyle = '#ff6b4a' // Fondo naranja coral de cabecera
      ctx.fillRect(0, 0, canvas.width, 220)

      ctx.fillStyle = '#ffffff'
      ctx.font = "bold 78px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.textAlign = 'center'
      ctx.fillText("¡ADOPTA AMOR!", canvas.width / 2, 140)

      ctx.fillStyle = '#7c2d12' // Texto café oscuro
      ctx.font = "bold 42px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText("CAMBIA SU VIDA, CAMBIA LA TUYA.", canvas.width / 2, 290)

      // C. Banner del Nombre de la Mascota
      const bannerW = 600
      const bannerH = 100
      const bannerX = canvas.width / 2 - bannerW / 2
      const bannerY = 350

      ctx.strokeStyle = '#7c2d12'
      ctx.lineWidth = 4
      ctx.setLineDash([12, 8]) // Línea punteada
      ctx.strokeRect(bannerX, bannerY, bannerW, bannerH)
      ctx.setLineDash([]) // Quitar punteado

      ctx.fillStyle = '#7c2d12'
      ctx.font = "bold 56px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText(props.pet.name.toUpperCase(), canvas.width / 2, 420)

      // D. Foto principal enmarcada a la Derecha
      const imgW = 400
      const imgH = 500
      const imgX = 590
      const imgY = 500

      ctx.save()
      ctx.fillStyle = '#ffffff'
      ctx.shadowColor = 'rgba(0,0,0,0.08)'
      ctx.shadowBlur = 20
      ctx.shadowOffsetY = 8
      
      // Dibujar marco blanco alrededor de la foto
      ctx.fillRect(imgX - 15, imgY - 15, imgW + 30, imgH + 30)

      if (petImageObj && petImageObj.complete) {
        ctx.save()
        // Clip para la foto
        ctx.beginPath()
        ctx.rect(imgX, imgY, imgW, imgH)
        ctx.clip()

        // Cover logic
        const imgRatio = petImageObj.width / petImageObj.height
        let dw = imgW
        let dh = imgH
        let dx = imgX
        let dy = imgY

        if (imgRatio > imgW / imgH) {
          dw = imgH * imgRatio
          dx = imgX - (dw - imgW) / 2
        } else {
          dh = imgW / imgRatio
          dy = imgY - (dh - imgH) / 2
        }

        ctx.drawImage(petImageObj, dx, dy, dw, dh)
        ctx.restore()
      } else {
        // Fallback
        ctx.fillStyle = '#faf8f5'
        ctx.fillRect(imgX, imgY, imgW, imgH)
        ctx.fillStyle = '#ff6b4a'
        ctx.font = "bold 150px Arial"
        ctx.textAlign = 'center'
        ctx.textBaseline = 'middle'
        ctx.fillText("🐾", imgX + imgW / 2, imgY + imgH / 2)
      }
      ctx.restore()

      // Fotos adicionales mockups decorativas en miniatura abajo
      ctx.save()
      ctx.fillStyle = '#ffffff'
      ctx.shadowColor = 'rgba(0,0,0,0.06)'
      ctx.shadowBlur = 10
      ctx.shadowOffsetY = 5
      
      // Mini 1
      ctx.fillRect(590, 1060, 180, 180)
      if (petImageObj && petImageObj.complete) {
        ctx.drawImage(petImageObj, 595, 1065, 170, 170)
      }
      
      // Mini 2
      ctx.fillRect(810, 1060, 180, 180)
      if (petImageObj && petImageObj.complete) {
        ctx.drawImage(petImageObj, 815, 1065, 170, 170)
      }
      ctx.restore()

      // E. Ficha Técnica con Emojis (Izquierda)
      const infoX = 80
      const infoY = 500
      
      ctx.textAlign = 'left'

      // Edad
      ctx.font = "bold 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText("🎂 EDAD:", infoX, infoY + 20)
      ctx.fillStyle = '#334155'
      ctx.font = "600 30px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText(props.pet.age_text || '2 años aprox', infoX, infoY + 65)

      // Tamaño
      ctx.font = "bold 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText("📐 TAMAÑO:", infoX, infoY + 140)
      ctx.fillStyle = '#334155'
      ctx.font = "600 30px 'Plus Jakarta Sans', Arial, sans-serif"
      const sizeStr = props.pet.characteristics?.space === 'grande' ? 'Grande (25kg+)' : (props.pet.characteristics?.space === 'pequeño' ? 'Pequeño (1-10kg)' : 'Mediano (11-25kg)')
      ctx.fillText(sizeStr, infoX, infoY + 185)

      // Salud
      ctx.font = "bold 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText("💉 SALUD:", infoX, infoY + 260)
      ctx.fillStyle = '#334155'
      ctx.font = "600 30px 'Plus Jakarta Sans', Arial, sans-serif"
      
      const healthItems = []
      if (selectedFeatures.value.vaccinated) healthItems.push('Vacunado')
      if (selectedFeatures.value.sterilized) healthItems.push('Castrado')
      if (selectedFeatures.value.chipped) healthItems.push('Con Chip')
      const healthStr = healthItems.length > 0 ? healthItems.join(', ') : 'Al día'
      ctx.fillText(healthStr, infoX, infoY + 305)

      // Personalidad
      ctx.font = "bold 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText("🧠 CONDUCTA:", infoX, infoY + 380)
      ctx.fillStyle = '#334155'
      ctx.font = "600 30px 'Plus Jakarta Sans', Arial, sans-serif"
      
      let behaviorStr = 'Tranquilo y leal.'
      if (selectedFeatures.value.playful) behaviorStr = 'Juguetón, alegre.'
      else if (selectedFeatures.value.calm) behaviorStr = 'Muy dócil y faldero.'
      ctx.fillText(behaviorStr, infoX, infoY + 425)

      // Convivencia
      ctx.font = "bold 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText("🐾 CONVIVENCIA:", infoX, infoY + 500)
      ctx.fillStyle = '#334155'
      ctx.font = "600 30px 'Plus Jakarta Sans', Arial, sans-serif"
      
      const convItems = []
      if (selectedFeatures.value.kids) convItems.push('Niños')
      if (selectedFeatures.value.dogs) convItems.push('Perros')
      if (selectedFeatures.value.cats) convItems.push('Gatos')
      const convStr = convItems.length > 0 ? 'Apto con: ' + convItems.join(', ') : 'Sociable con todos'
      ctx.fillText(convStr, infoX, infoY + 545)

      // F. Banner de Contrato e Información (Abajo)
      ctx.save()
      ctx.fillStyle = 'rgba(124, 45, 18, 0.05)'
      ctx.fillRect(80, 1140, 450, 80)
      ctx.restore()
      
      ctx.fillStyle = '#7c2d12'
      ctx.font = "bold 28px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText("📝 SE HACE CONTRATO DE ADOPCIÓN", 100, 1190)

      // G. Pie de Página de Contacto con Logo / Emojis
      ctx.fillStyle = '#115e59' // Verde oscuro
      ctx.fillRect(0, 1340, canvas.width, 360)

      ctx.fillStyle = '#ffffff'
      ctx.textAlign = 'center'
      ctx.font = "bold 36px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText("¿TE ENAMORASTE? CONTACTATE CON NOSOTROS:", canvas.width / 2, 1410)

      // WhatsApp Icon y Teléfono
      ctx.font = "bold 56px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillText(`🟢 ${contactPhone.value}`, canvas.width / 2, 1510)

      ctx.font = "600 34px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = 'rgba(255, 255, 255, 0.75)'
      ctx.fillText(contactName.value, canvas.width / 2, 1575)

      // Slogan final
      ctx.font = "italic 600 36px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ff6b4a'
      ctx.fillText(`"${customSlogan.value}"`, canvas.width / 2, 1650)

      // URL corta del sitio
      ctx.font = "bold 32px 'Plus Jakarta Sans', Arial, sans-serif"
      ctx.fillStyle = '#ffffff'
      ctx.fillText("WWW.ADOPTA.CL", canvas.width / 2, 1800)

      // Actualizar imagen para descargar
      renderedImageUrl.value = canvas.toDataURL('image/png')
    }

    onMounted(() => {
      // Cargar la imagen del perro en memoria para que esté lista al dibujar
      if (props.pet.photo_path) {
        petImageObj = new Image()
        petImageObj.crossOrigin = 'anonymous' // Evitar problemas de CORS locales
        const base = window.location.origin + (window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '')
        petImageObj.src = base + '/' + props.pet.photo_path
        petImageObj.onload = () => {
          drawFlyer()
        }
        petImageObj.onerror = () => {
          console.warn('No se pudo cargar la foto de la mascota. Renderizando con emoji fallback.')
          drawFlyer()
        }
      } else {
        drawFlyer()
      }
    })

    // Observar cambios en variables reactivas para redibujar al instante
    watch([contactName, contactPhone, customSlogan], () => {
      debouncedRender()
    })

    return {
      flyerCanvas,
      renderedImageUrl,
      templateType,
      contactName,
      contactPhone,
      customSlogan,
      selectedFeatures,
      changeTemplate,
      debouncedRender,
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
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -200px;
  left: -200px;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -200px;
  right: -200px;
}

/* Main */
.main-content {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
  position: relative;
  z-index: 10;
}

/* Generator Grid */
.generator-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 2.5rem;
}

@media (max-width: 968px) {
  .generator-grid {
    grid-template-columns: 1fr;
  }
}

.preview-column, .controls-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Card basic */
.card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 24px;
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

/* Canvas Preview Frame */
.canvas-wrapper {
  background: #18181b;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.flyer-img-container {
  width: 100%;
  max-width: 320px;
  aspect-ratio: 9/16;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  background: #27272a;
}

.flyer-preview-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.canvas-loading {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.loading-icon {
  font-size: 2rem;
  animation: pulse-pet 1.5s infinite;
}

@keyframes pulse-pet {
  0% { transform: scale(1); opacity: 0.8; }
  50% { transform: scale(1.15); opacity: 1; }
  100% { transform: scale(1); opacity: 0.8; }
}

.mobile-tip {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-align: center;
  margin: 0.5rem 0 0 0;
  line-height: 1.4;
}

/* Forms & Controls */
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

.form-group input {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  outline: none;
  box-sizing: border-box;
  width: 100%;
  transition: all 0.3s ease;
}

.form-group input:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

/* Template Selector Buttons */
.template-selector-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 0.25rem;
}

.template-option-btn {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.2s ease-in-out;
  text-align: left;
}

.template-option-btn:hover {
  background: rgba(255, 255, 255, 0.05);
}

.template-option-btn.active {
  background: rgba(139, 92, 246, 0.12);
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
}

.template-icon {
  font-size: 1.75rem;
  background: rgba(255, 255, 255, 0.04);
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
}

.template-text {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.template-text strong {
  font-size: 0.9rem;
  color: var(--color-text-main);
}

.template-text span {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

/* Checklist of features */
.checkbox-hint {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin: 0;
}

.features-checklist {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

@media (max-width: 480px) {
  .features-checklist {
    grid-template-columns: 1fr;
  }
}

.feature-check-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  cursor: pointer;
  color: var(--color-text-main);
  font-weight: 500;
  user-select: none;
}

.feature-check-label input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary);
  cursor: pointer;
}

.separator {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 0.75rem 0;
}

/* Alert banner */
.alert-info-style {
  background: rgba(14, 165, 233, 0.08);
  border: 1px solid rgba(14, 165, 233, 0.15);
  color: #bae6fd;
  padding: 1rem;
  border-radius: 14px;
  font-size: 0.8rem;
  line-height: 1.45;
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.75rem 1.6rem;
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
}

.btn-primary:hover {
  background: var(--color-primary-hover);
  transform: translateY(-1px);
}

.btn-download {
  box-shadow: 0 4px 14px rgba(255, 107, 74, 0.25);
  font-weight: 700;
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
