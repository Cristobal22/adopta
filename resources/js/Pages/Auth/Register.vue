<template>
  <div class="auth-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <div class="auth-card">
      <div class="logo">
        <span class="logo-icon">🐾</span>
        <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
      </div>

      <div class="auth-header">
        <h2>Crea tu cuenta</h2>
        <p>Únete al ecosistema y ayuda a transformar vidas animales</p>
      </div>

      <form @submit.prevent="submit" class="auth-form">
        <!-- Grid fields -->
        <div class="form-row">
          <div class="form-group">
            <label for="name">Nombre Completo</label>
            <input 
              type="text" 
              id="name" 
              v-model="form.name" 
              placeholder="Tu nombre" 
              :class="{ 'input-error': form.errors.name }"
              required 
            />
            <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
          </div>

          <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input 
              type="email" 
              id="email" 
              v-model="form.email" 
              placeholder="correo@ejemplo.com" 
              :class="{ 'input-error': form.errors.email }"
              required 
            />
            <span v-if="form.errors.email" class="error-text">{{ form.errors.email }}</span>
          </div>
        </div>

        <!-- Role Selection Cards -->
        <div class="form-group">
          <label>¿Cuál será tu rol principal?</label>
          <div class="role-grid">
            <div 
              class="role-card" 
              :class="{ active: form.role === 'adoptante' }" 
              @click="form.role = 'adoptante'"
            >
              <span class="role-icon">🏠</span>
              <span class="role-title">Adoptante</span>
            </div>

            <div 
              class="role-card" 
              :class="{ active: form.role === 'transito' }" 
              @click="form.role = 'transito'"
            >
              <span class="role-icon">🕒</span>
              <span class="role-title">Tránsito</span>
            </div>

            <div 
              class="role-card" 
              :class="{ active: form.role === 'rescatista' }" 
              @click="form.role = 'rescatista'"
            >
              <span class="role-icon">📍</span>
              <span class="role-title">Rescatista</span>
            </div>

            <div 
              class="role-card" 
              :class="{ active: form.role === 'fundacion' }" 
              @click="form.role = 'fundacion'"
            >
              <span class="role-icon">🏢</span>
              <span class="role-title">Fundación</span>
            </div>

            <div 
              class="role-card" 
              :class="{ active: form.role === 'donante' }" 
              @click="form.role = 'donante'"
            >
              <span class="role-icon">🎁</span>
              <span class="role-title">Donante</span>
            </div>

            <div 
              class="role-card" 
              :class="{ active: form.role === 'municipalidad' }" 
              @click="form.role = 'municipalidad'"
            >
              <span class="role-icon">🏛️</span>
              <span class="role-title">Municipalidad</span>
            </div>
          </div>
          <span v-if="form.errors.role" class="error-text">{{ form.errors.role }}</span>
        </div>

        <!-- Secondary Fields -->
        <div class="form-row">
          <div class="form-group">
            <label for="phone">Teléfono de Contacto</label>
            <input 
              type="text" 
              id="phone" 
              v-model="form.phone" 
              placeholder="+56 9 1234 5678" 
              :class="{ 'input-error': form.errors.phone }"
            />
            <span v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</span>
          </div>

          <div class="form-group">
            <label for="region">Región</label>
            <select 
              id="region" 
              v-model="selectedRegion"
              :class="{ 'input-error': form.errors.city }"
              required
            >
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
            <select 
              id="commune" 
              v-model="selectedCommune" 
              :disabled="!selectedRegion"
              :class="{ 'input-error': form.errors.city }"
              required
            >
              <option value="">Selecciona Comuna...</option>
              <option v-for="comuna in availableCommunes" :key="comuna" :value="comuna">
                {{ comuna }}
              </option>
            </select>
            <span v-if="form.errors.city" class="error-text">{{ form.errors.city }}</span>
          </div>

          <!-- Dummy spacer field to maintain 2-column grid alignment -->
          <div class="form-group" style="opacity: 0; pointer-events: none;">
            <label>&nbsp;</label>
            <input type="text" disabled />
          </div>
        </div>

        <!-- Password Fields -->
        <div class="form-row">
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input 
              type="password" 
              id="password" 
              v-model="form.password" 
              placeholder="Mín. 8 caracteres" 
              :class="{ 'input-error': form.errors.password }"
              required 
            />
            <span v-if="form.errors.password" class="error-text">{{ form.errors.password }}</span>
          </div>

          <div class="form-group">
            <label for="password_confirmation">Confirmar Contraseña</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="form.password_confirmation" 
              placeholder="Repite la contraseña" 
              required 
            />
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
          {{ form.processing ? 'Registrando...' : 'Crear Cuenta' }}
        </button>

        <p class="auth-footer">
          ¿Ya tienes una cuenta? 
          <a href="/login" class="auth-link">Inicia sesión</a>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { chileRegions } from '../../Utils/chileLocations'

export default {
  name: 'Register',
  setup() {
    const form = useForm({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'adoptante',
      phone: '',
      city: '',
    })

    const selectedRegion = ref('')
    const selectedCommune = ref('')
    const availableCommunes = ref([])

    watch(selectedRegion, (newRegion) => {
      selectedCommune.value = ''
      if (newRegion) {
        const regionObj = chileRegions.find(r => r.name === newRegion)
        availableCommunes.value = regionObj ? regionObj.comunas : []
      } else {
        availableCommunes.value = []
      }
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

    const submit = () => {
      form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
      })
    }

    return { 
      form, 
      submit,
      chileRegions,
      selectedRegion,
      selectedCommune,
      availableCommunes
    }
  }
}
</script>


<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 2.5rem 1.5rem;
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

/* Card */
.auth-card {
  position: relative;
  z-index: 10;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 3rem 2.5rem;
  width: 100%;
  max-width: 600px; /* Slightly wider for registry form rows */
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 2rem 1.5rem;
  }
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
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

.auth-header {
  text-align: center;
}

.auth-header h2 {
  font-family: var(--font-title);
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.auth-header p {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  line-height: 1.4;
  margin: 0;
}

/* Forms */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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

.form-group input, .form-group select {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  transition: all 0.3s ease;
  outline: none;
  width: 100%;
}

.form-group select option {
  background-color: #1e1b4b; /* Dark theme option background */
  color: white;
}

.form-group input::placeholder {
  color: rgba(255, 255, 255, 0.25);
}

.form-group input:focus, .form-group select:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
}

.form-group input.input-error, .form-group select.input-error {
  border-color: var(--color-accent);
}

.error-text {
  font-size: 0.8rem;
  color: var(--color-accent);
}

/* Custom Role Selector Grid */
.role-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.5rem;
  margin-top: 0.25rem;
}

@media (max-width: 576px) {
  .role-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 400px) {
  .role-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.role-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 1rem 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  text-align: center;
}

.role-card:hover {
  border-color: rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
}

.role-card.active {
  background: rgba(139, 92, 246, 0.1);
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

.role-icon {
  font-size: 1.5rem;
}

.role-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-main);
}

.role-card.active .role-title {
  color: #c084fc;
}

/* Button */
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
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-block {
  width: 100%;
}

.auth-footer {
  text-align: center;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin: 0;
}

.auth-link {
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 600;
}

.auth-link:hover {
  text-decoration: underline;
}
</style>
