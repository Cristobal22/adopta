<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">
      <!-- Section Header -->
      <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h1>{{ isEdit ? 'Editar Mascota' : 'Registrar Nueva Mascota' }}</h1>
          <p class="subtitle">Ingresa la información detallada para habilitar el algoritmo de match inteligente y el seguimiento.</p>
        </div>
        <div>
          <Link href="/pets" class="btn btn-secondary btn-sm">Volver al listado</Link>
        </div>
      </div>

        <form @submit.prevent="submit" class="pet-form-grid">
          <!-- Left Column: Core Details -->
          <div class="form-column">
            <!-- Fotografía de la Mascota Card -->
            <div class="card">
              <h3>Fotografía de la Mascota</h3>
              <p class="card-desc">Carga una foto en primer plano de la mascota. Se convertirá automáticamente a WebP para optimizar el almacenamiento.</p>
              
              <div class="photo-upload-section">
                <div v-if="photoPreview || pet?.photo_path" class="photo-preview-wrapper">
                  <img :src="photoPreview || '/' + pet.photo_path" alt="Vista previa de la mascota" class="pet-form-img-preview" />
                </div>
                <div v-else class="photo-placeholder-wrapper">
                  <span class="photo-placeholder-icon">🐾</span>
                  <span class="photo-placeholder-text">Sin fotografía cargada</span>
                </div>
                
                <div class="photo-upload-controls">
                  <input type="file" ref="photoInput" @change="handlePhotoChange" accept="image/*" class="hidden-file-input" style="display: none;" />
                  <button type="button" class="btn btn-secondary btn-sm" @click="$refs.photoInput.click()">
                    {{ (photoPreview || pet?.photo_path) ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}
                  </button>
                  <span v-if="form.errors.photo" class="error-text block mt-1">{{ form.errors.photo }}</span>
                </div>
              </div>
            </div>

            <div class="card">
              <h3>Información Básica</h3>
              
              <div class="form-group">
                <label for="name">Nombre de la Mascota</label>
                <input type="text" id="name" v-model="form.name" placeholder="Ej. Bobby, Luna..." required />
                <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="species">Especie</label>
                  <select id="species" v-model="form.species" required>
                    <option value="perro">Perro</option>
                    <option value="gato">Gato</option>
                    <option value="otro">Otro</option>
                  </select>
                  <span v-if="form.errors.species" class="error-text">{{ form.errors.species }}</span>
                </div>

                <div class="form-group">
                  <label for="gender">Género</label>
                  <select id="gender" v-model="form.gender" required>
                    <option value="macho">Macho</option>
                    <option value="hembra">Hembra</option>
                  </select>
                  <span v-if="form.errors.gender" class="error-text">{{ form.errors.gender }}</span>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="breed">Raza</label>
                  <input type="text" id="breed" v-model="form.breed" placeholder="Ej. Golden Retriever, Mestizo..." />
                  <span v-if="form.errors.breed" class="error-text">{{ form.errors.breed }}</span>
                </div>

                <div class="form-group">
                  <label for="age_text">Edad Estimada</label>
                  <input type="text" id="age_text" v-model="form.age_text" placeholder="Ej. 2 años, 4 meses..." />
                  <span v-if="form.errors.age_text" class="error-text">{{ form.errors.age_text }}</span>
                </div>
              </div>

              <div class="form-group">
                <label for="status">Estado Actual</label>
                <select id="status" v-model="form.status" required>
                  <option value="rescatado">Rescatado</option>
                  <option value="en_transito">En Tránsito</option>
                  <option value="en_adopcion">En Adopción</option>
                  <option value="adoptado">Adoptado</option>
                </select>
                <span v-if="form.errors.status" class="error-text">{{ form.errors.status }}</span>
              </div>

              <div class="form-group">
                <label for="description">Descripción / Historia</label>
                <textarea id="description" v-model="form.description" rows="4" placeholder="Describe su personalidad, comportamiento o historia de rescate..."></textarea>
                <span v-if="form.errors.description" class="error-text">{{ form.errors.description }}</span>
              </div>
            </div>

            <!-- Geolocation Card -->
            <div class="card">
              <h3>Geolocalización (Mapa de Calor)</h3>
              <p class="card-desc">Ubicación aproximada del rescate o refugio para estadísticas y mapas de abandono.</p>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="latitude">Latitud</label>
                  <input type="number" id="latitude" v-model="form.latitude" step="any" placeholder="Ej. -33.4489" />
                  <span v-if="form.errors.latitude" class="error-text">{{ form.errors.latitude }}</span>
                </div>

                <div class="form-group">
                  <label for="longitude">Longitud</label>
                  <input type="number" id="longitude" v-model="form.longitude" step="any" placeholder="Ej. -70.6693" />
                  <span v-if="form.errors.longitude" class="error-text">{{ form.errors.longitude }}</span>
                </div>
              </div>
            </div>

            <!-- Microchip Card -->
            <div class="card">
              <h3>Identificación</h3>
              <div class="form-group">
                <label for="microchip_code">Código del Microchip</label>
                <input type="text" id="microchip_code" v-model="form.microchip_code" placeholder="Código de 15 dígitos..." />
                <span v-if="form.errors.microchip_code" class="error-text">{{ form.errors.microchip_code }}</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Clinical History & Match Characteristics -->
          <div class="form-column">
            <!-- Characteristics (Match Algorithm Data) -->
            <div class="card">
              <h3>Características del Animal (Filtros de Match)</h3>
              <p class="card-desc">Define las variables de comportamiento que el algoritmo cruzará con el estilo de vida del adoptante.</p>

              <!-- Energy Level (1-5) -->
              <div class="form-group">
                <label>Nivel de Energía: {{ form.characteristics.energy || 3 }} / 5</label>
                <input type="range" min="1" max="5" v-model.number="form.characteristics.energy" class="slider" />
              </div>

              <!-- Space Requirement -->
              <div class="form-group">
                <label for="space_req">Espacio Mínimo Requerido</label>
                <select id="space_req" v-model="form.characteristics.space">
                  <option value="pequeño">Departamento / Sin patio</option>
                  <option value="mediano">Casa con patio mediano</option>
                  <option value="grande">Casa con patio grande / Campo</option>
                </select>
              </div>

              <!-- Compatibility Toggles -->
              <div class="form-group">
                <label>Compatibilidad</label>
                <div class="toggle-list">
                  <label class="toggle-container">
                    <input type="checkbox" v-model="form.characteristics.kids" />
                    <span class="toggle-slider"></span>
                    Apto para niños
                  </label>

                  <label class="toggle-container">
                    <input type="checkbox" v-model="form.characteristics.dogs" />
                    <span class="toggle-slider"></span>
                    Apto para convivir con perros
                  </label>

                  <label class="toggle-container">
                    <input type="checkbox" v-model="form.characteristics.cats" />
                    <span class="toggle-slider"></span>
                    Apto para convivir con gatos
                  </label>

                  <label class="toggle-container">
                    <input type="checkbox" v-model="form.characteristics.alone" />
                    <span class="toggle-slider"></span>
                    Tolera tiempo a solas (jornada laboral)
                  </label>
                </div>
              </div>
            </div>

            <!-- Dynamic Clinical History Builder -->
            <div class="card">
              <h3>Historial Clínico Digital</h3>
              <p class="card-desc">Agrega vacunas, desparasitaciones, castraciones o cirugías de forma estructurada.</p>

              <!-- Inline Clinical Record Constructor -->
              <div class="clinical-builder">
                <div class="builder-row">
                  <select v-model="newClinicalEvent.type" class="builder-select">
                    <option value="vacuna">Vacuna</option>
                    <option value="desparasitacion">Desparasitación</option>
                    <option value="esterilizacion">Esterilización</option>
                    <option value="enfermedad">Enfermedad</option>
                    <option value="cirugia">Cirugía</option>
                  </select>
                  <input type="text" v-model="newClinicalEvent.title" placeholder="Ej. Antirrábica, Triple Felina..." class="builder-input" />
                </div>
                <div class="builder-row">
                  <input type="date" v-model="newClinicalEvent.date" class="builder-date" />
                  <button type="button" class="btn btn-secondary" @click="addClinicalEvent">+ Agregar</button>
                </div>
              </div>

              <!-- Events List -->
              <div class="clinical-events-list" v-if="form.clinical_history.length > 0">
                <div class="clinical-event-item" v-for="(event, index) in form.clinical_history" :key="index">
                  <div class="event-details">
                    <span class="event-tag" :class="'tag-' + event.type">{{ event.type }}</span>
                    <strong>{{ event.title }}</strong>
                    <span class="event-date">📅 {{ formatDate(event.date) }}</span>
                  </div>
                  <button type="button" class="btn-delete-event" @click="removeClinicalEvent(index)">✕</button>
                </div>
              </div>
              <div class="empty-clinical-state" v-else>
                <span>Sin registros médicos agregados aún.</span>
              </div>
            </div>
          </div>

          <!-- Bottom Bar Actions -->
          <div class="form-actions-bar">
            <Link href="/pets" class="btn btn-secondary">Cancelar</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              {{ form.processing ? 'Guardando...' : (isEdit ? 'Actualizar Mascota' : 'Registrar Mascota') }}
            </button>
          </div>
        </form>
      </main>

      <Footer />
  </div>
</template>

<script>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Form',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    pet: Object,
  },
  setup(props) {
    const isEdit = computed(() => !!props.pet)

    // Form inicial con valores por defecto o cargados
    const form = useForm({
      name: props.pet?.name || '',
      species: props.pet?.species || 'perro',
      breed: props.pet?.breed || '',
      age_text: props.pet?.age_text || '',
      status: props.pet?.status || 'rescatado',
      gender: props.pet?.gender || 'macho',
      description: props.pet?.description || '',
      latitude: props.pet?.latitude || null,
      longitude: props.pet?.longitude || null,
      microchip_code: props.pet?.microchip_code || '',
      clinical_history: props.pet?.clinical_history || [],
      characteristics: props.pet?.characteristics || {
        energy: 3,
        space: 'mediano',
        kids: true,
        dogs: true,
        cats: true,
        alone: false,
      },
      photo: null,
    })

    // Estado del constructor clínico
    const newClinicalEvent = ref({
      type: 'vacuna',
      title: '',
      date: new Date().toISOString().substring(0, 10),
    })

    const photoPreview = ref(null)

    const handlePhotoChange = (e) => {
      const file = e.target.files[0]
      if (file) {
        form.photo = file
        photoPreview.value = URL.createObjectURL(file)
      }
    }

    const addClinicalEvent = () => {
      if (!newClinicalEvent.value.title.trim()) {
        alert('Por favor ingresa un título para el evento médico.')
        return
      }
      
      form.clinical_history.push({
        type: newClinicalEvent.value.type,
        title: newClinicalEvent.value.title.trim(),
        date: newClinicalEvent.value.date,
      })

      // Resetear inputs del constructor
      newClinicalEvent.value.title = ''
    }

    const removeClinicalEvent = (index) => {
      form.clinical_history.splice(index, 1)
    }

    const submit = () => {
      if (isEdit.value) {
        // Spoofing de PUT sobre petición POST para soportar subidas de archivos en Laravel
        form.transform((data) => ({
          ...data,
          _method: 'PUT',
        })).post(`/backoffice/pets/${props.pet.id}`)
      } else {
        form.post('/backoffice/pets')
      }
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const parts = dateString.split('-')
      if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`
      }
      return dateString
    }

    return {
      isEdit,
      form,
      newClinicalEvent,
      photoPreview,
      handlePhotoChange,
      addClinicalEvent,
      removeClinicalEvent,
      submit,
      formatDate,
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

/* Background Blob */
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

.badge-role {
  background: rgba(13, 165, 233, 0.15);
  border: 1px solid rgba(13, 165, 233, 0.3);
  color: var(--color-secondary);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  margin-left: 0.5rem;
}

/* Section Header */
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

/* Form Grid */
.pet-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  box-sizing: border-box;
}

@media (max-width: 968px) {
  .pet-form-grid {
    grid-template-columns: 1fr;
  }
}

.form-column {
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

/* Form Fields */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 480px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

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

.form-group input[type="text"], 
.form-group input[type="number"], 
.form-group select, 
.form-group textarea {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  transition: all 0.3s ease;
  outline: none;
  box-sizing: border-box;
  width: 100%;
}

.form-group textarea {
  resize: vertical;
}

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
}

.form-group select option {
  background-color: var(--color-bg);
  color: white;
}

.error-text {
  font-size: 0.8rem;
  color: var(--color-accent);
}

/* Slider */
.slider {
  -webkit-appearance: none;
  width: 100%;
  height: 6px;
  border-radius: 5px;
  background: rgba(255, 255, 255, 0.1);
  outline: none;
  margin: 10px 0;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--color-primary);
  cursor: pointer;
  box-shadow: 0 0 10px rgba(139, 92, 246, 0.5);
  transition: all 0.2s ease;
}

.slider::-webkit-slider-thumb:hover {
  background: var(--color-primary-hover);
  transform: scale(1.1);
}

/* Toggle Switches */
.toggle-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.25rem;
}

.toggle-container {
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 3.5rem;
  cursor: pointer;
  font-size: 0.9rem;
  user-select: none;
  height: 24px;
}

.toggle-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.toggle-slider {
  position: absolute;
  top: 0;
  left: 0;
  height: 24px;
  width: 44px;
  background-color: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  transition: .3s;
  border-radius: 34px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}

.toggle-container input:checked ~ .toggle-slider {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
}

.toggle-container input:checked ~ .toggle-slider:before {
  transform: translateX(20px);
}

/* Clinical History Builder */
.clinical-builder {
  background: rgba(255, 255, 255, 0.015);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 1.25rem;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.builder-row {
  display: flex;
  gap: 0.75rem;
}

@media (max-width: 480px) {
  .builder-row {
    flex-direction: column;
  }
}

.builder-select {
  flex: 0 0 130px;
}

.builder-input {
  flex: 1;
}

.builder-date {
  flex: 1;
}

.clinical-events-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.clinical-event-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  padding: 0.8rem 1rem;
  border-radius: 12px;
}

.event-details {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  font-size: 0.85rem;
}

.event-tag {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.2rem 0.5rem;
  border-radius: 5px;
}

.tag-vacuna { background: rgba(14, 165, 233, 0.15); color: var(--color-secondary); border: 1px solid rgba(14, 165, 233, 0.3); }
.tag-desparasitacion { background: rgba(245, 158, 11, 0.15); color: var(--color-warning); border: 1px solid rgba(245, 158, 11, 0.3); }
.tag-esterilizacion { background: rgba(16, 185, 129, 0.15); color: var(--color-success); border: 1px solid rgba(16, 185, 129, 0.3); }
.tag-enfermedad { background: rgba(244, 63, 94, 0.15); color: var(--color-accent); border: 1px solid rgba(244, 63, 94, 0.3); }
.tag-cirugia { background: rgba(139, 92, 246, 0.15); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.3); }

.event-date {
  color: var(--color-text-muted);
}

.btn-delete-event {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  transition: color 0.2s ease;
}

.btn-delete-event:hover {
  color: var(--color-accent);
}

.empty-clinical-state {
  text-align: center;
  padding: 1.5rem;
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

/* Form Actions Bottom Bar */
.form-actions-bar {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 1.5rem;
  margin-top: 1rem;
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

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}

/* Photo Upload Styles */
.photo-upload-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
  background: rgba(255, 255, 255, 0.015);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 12px;
}

.photo-preview-wrapper {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid var(--color-primary);
  box-shadow: 0 4px 10px rgba(255, 107, 74, 0.2);
}

.pet-form-img-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder-wrapper {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.04);
  border: 2px dashed rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.photo-placeholder-icon {
  font-size: 2.5rem;
  opacity: 0.5;
}

.photo-placeholder-text {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.photo-upload-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>

