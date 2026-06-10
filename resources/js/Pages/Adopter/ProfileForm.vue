<template>
  <div class="profile-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <div class="profile-card">
      <div class="profile-header">
        <h1>Formulario de Estilo de Vida</h1>
        <p>Esta información es cruzada automáticamente con el perfil de las mascotas para verificar compatibilidad y evitar devoluciones.</p>
      </div>

      <form @submit.prevent="submit" class="profile-form">
        <!-- Contact Information -->
        <h3>Información de Contacto</h3>
        <div class="form-row">
          <div class="form-group">
            <label for="phone">Teléfono Móvil</label>
            <input type="text" id="phone" v-model="form.phone" placeholder="+56 9 1234 5678" required />
            <span v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</span>
          </div>

          <div class="form-group">
            <label for="region">Región</label>
            <select id="region" v-model="selectedRegion" required>
              <option value="">Selecciona Región...</option>
              <option v-for="region in chileRegions" :key="region.name" :value="region.name">
                {{ region.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="commune">Comuna</label>
            <select id="commune" v-model="selectedCommune" :disabled="!selectedRegion" required>
              <option value="">Selecciona Comuna...</option>
              <option v-for="comuna in availableCommunes" :key="comuna" :value="comuna">
                {{ comuna }}
              </option>
            </select>
            <span v-if="form.errors.city" class="error-text">{{ form.errors.city }}</span>
          </div>

          <div class="form-group">
            <label for="address">Dirección Particular</label>
            <input type="text" id="address" v-model="form.address" placeholder="Calle, número, depto..." required />
            <span v-if="form.errors.address" class="error-text">{{ form.errors.address }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="identification_code">Documento de Identidad (DNI/RUT)</label>
            <input 
              type="text" 
              id="identification_code" 
              v-model="form.identification_code" 
              @input="rutInput"
              placeholder="12.345.678-K" 
              :disabled="hasActiveAdoptions" 
              :class="{ 'input-error': isRutInvalid }"
              required 
            />
            <span v-if="form.errors.identification_code" class="error-text">{{ form.errors.identification_code }}</span>
            <span v-if="isRutInvalid" class="warning-text">⚠️ RUT no válido (dígito verificador incorrecto)</span>
            <small v-if="hasActiveAdoptions" style="color: var(--color-text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
              Bloqueado por trámite de adopción activo.
            </small>
          </div>

          <!-- Dummy spacer field to maintain 2-column grid alignment -->
          <div class="form-group" style="opacity: 0; pointer-events: none;">
            <label>&nbsp;</label>
            <input type="text" disabled />
          </div>
        </div>

        <!-- Housing and Space -->
        <hr class="separator" />
        <h3>Vivienda y Entorno</h3>

        <div class="form-group">
          <label for="house_type">Tipo de Vivienda</label>
          <select id="house_type" v-model="form.house_type" required>
            <option value="departamento">Departamento / Sin patio</option>
            <option value="casa_sin_patio">Casa sin patio</option>
            <option value="casa_patio_mediano">Casa con patio mediano</option>
            <option value="casa_patio_grande">Casa con patio grande / Finca</option>
          </select>
          <span v-if="form.errors.house_type" class="error-text">{{ form.errors.house_type }}</span>
        </div>

        <!-- Co-occupants Toggles -->
        <div class="form-group">
          <label>Integrantes del Hogar</label>
          <div class="toggle-list">
            <label class="toggle-container">
              <input type="checkbox" v-model="form.has_kids" />
              <span class="toggle-slider"></span>
              ¿Hay niños pequeños en el hogar?
            </label>

            <label class="toggle-container">
              <input type="checkbox" v-model="form.has_dogs" />
              <span class="toggle-slider"></span>
              ¿Viven otros perros actualmente en la casa?
            </label>

            <label class="toggle-container">
              <input type="checkbox" v-model="form.has_cats" />
              <span class="toggle-slider"></span>
              ¿Viven otros gatos actualmente en la casa?
            </label>
          </div>
        </div>

        <!-- Habits -->
        <hr class="separator" />
        <h3>Hábitos y Preferencias</h3>

        <div class="form-row">
          <div class="form-group">
            <label for="hours_alone">Horas diarias que la mascota estará sola</label>
            <input type="number" id="hours_alone" v-model.number="form.hours_alone" min="0" max="24" required />
            <span v-if="form.errors.hours_alone" class="error-text">{{ form.errors.hours_alone }}</span>
          </div>

          <div class="form-group">
            <label>Nivel de energía deseado de la mascota: {{ form.energy_preference }} / 5</label>
            <input type="range" min="1" max="5" v-model.number="form.energy_preference" class="slider" />
            <span v-if="form.errors.energy_preference" class="error-text">{{ form.errors.energy_preference }}</span>
          </div>
        </div>

        <!-- Financial Confirmation -->
        <div class="form-group budget-group">
          <label class="checkbox-container">
            <input type="checkbox" v-model="form.budget_confirmed" required />
            <span class="checkmark"></span>
            Confirmo que cuento con la estabilidad financiera para costear alimento, vacunas y atención médica de urgencia para el animal.
          </label>
          <span v-if="form.errors.budget_confirmed" class="error-text">{{ form.errors.budget_confirmed }}</span>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <Link href="/dashboard" class="btn btn-secondary">Cancelar</Link>
          <button type="submit" class="btn btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Guardar Formulario' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { chileRegions, validateRut, formatRut } from '../../Utils/chileLocations'

export default {
  name: 'ProfileForm',
  components: {
    Link,
  },
  props: {
    verification_data: Object,
    phone: String,
    address: String,
    city: String,
    hasActiveAdoptions: Boolean,
  },
  setup(props) {
    const form = useForm({
      phone: props.phone || '',
      address: props.address || '',
      city: props.city || '',
      identification_code: props.verification_data?.identification_code || '',
      house_type: props.verification_data?.house_type || 'departamento',
      has_kids: props.verification_data?.has_kids || false,
      has_dogs: props.verification_data?.has_dogs || false,
      has_cats: props.verification_data?.has_cats || false,
      hours_alone: props.verification_data?.hours_alone !== undefined ? props.verification_data.hours_alone : 4,
      energy_preference: props.verification_data?.energy_preference || 3,
      budget_confirmed: props.verification_data?.budget_confirmed || false,
    })

    const initialRegion = ref('')
    const initialCommune = ref('')
    
    if (props.city && props.city.includes(',')) {
      const parts = props.city.split(',')
      initialCommune.value = parts[0].trim()
      initialRegion.value = parts[1].trim()
    } else if (props.city) {
      initialRegion.value = "Metropolitana de Santiago"
      initialCommune.value = props.city.trim()
    }

    const selectedRegion = ref(initialRegion.value)
    const selectedCommune = ref(initialCommune.value)
    const availableCommunes = ref([])

    const updateAvailableCommunes = (regionName) => {
      if (regionName) {
        const regionObj = chileRegions.find(r => r.name === regionName)
        availableCommunes.value = regionObj ? regionObj.comunas : []
      } else {
        availableCommunes.value = []
      }
    }

    // Initialize available communes on load
    updateAvailableCommunes(selectedRegion.value)

    watch(selectedRegion, (newRegion) => {
      selectedCommune.value = ''
      updateAvailableCommunes(newRegion)
      updateCity()
    })

    watch(selectedCommune, () => {
      updateCity()
    })

    const updateCity = () => {
      if (selectedCommune.value && selectedRegion.value) {
        form.city = `${selectedCommune.value}, ${selectedRegion.value}`
      } else {
        form.city = ''
      }
    }

    // RUT Validation
    const isRutInvalid = ref(false)
    const rutInput = (event) => {
      const rawVal = event.target.value
      const formatted = formatRut(rawVal)
      form.identification_code = formatted
      
      if (formatted) {
        isRutInvalid.value = !validateRut(formatted)
      } else {
        isRutInvalid.value = false
      }
    }

    onMounted(() => {
      if (form.identification_code) {
        isRutInvalid.value = !validateRut(form.identification_code)
      }
    })

    const submit = () => {
      form.post('/adopter/profile')
    }

    return { 
      form, 
      submit,
      chileRegions,
      selectedRegion,
      selectedCommune,
      availableCommunes,
      isRutInvalid,
      rutInput
    }
  }
}
</script>


<style scoped>
.profile-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 3rem 1.5rem;
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

/* Profile Card */
.profile-card {
  position: relative;
  z-index: 10;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 3rem 2.5rem;
  width: 100%;
  max-width: 650px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

@media (max-width: 480px) {
  .profile-card {
    padding: 2rem 1.5rem;
  }
}

.profile-header h1 {
  font-family: var(--font-title);
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.profile-header p {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  line-height: 1.4;
  margin: 0;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.profile-form h3 {
  font-family: var(--font-title);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0.5rem 0 0 0;
  color: var(--color-text-main);
  border-left: 3px solid var(--color-primary);
  padding-left: 0.5rem;
}

.separator {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 0.75rem 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 576px) {
  .form-row {
    grid-template-columns: 1fr;
    gap: 1.25rem;
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
.form-group select {
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
}

.form-group input:focus, 
.form-group select:focus {
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

.warning-text {
  font-size: 0.8rem;
  color: var(--color-accent);
  margin-top: 0.25rem;
  display: block;
}

/* Custom Checkbox / Toggles */
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

/* Budget check container */
.budget-group {
  margin-top: 0.5rem;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  position: relative;
  padding-left: 2rem;
  cursor: pointer;
  user-select: none;
  color: var(--color-text-muted);
  font-size: 0.85rem;
  line-height: 1.4;
}

.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  top: 2px;
  left: 0;
  height: 18px;
  width: 18px;
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 5px;
  transition: all 0.2s ease;
}

.checkbox-container:hover input ~ .checkmark {
  border-color: var(--color-primary);
}

.checkbox-container input:checked ~ .checkmark {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
  display: block;
}

.checkbox-container .checkmark:after {
  left: 6px;
  top: 2px;
  width: 4px;
  height: 9px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
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
  transition: all 0.3s ease;
  display: inline-block;
  text-align: center;
  text-decoration: none;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
  box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
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
</style>
