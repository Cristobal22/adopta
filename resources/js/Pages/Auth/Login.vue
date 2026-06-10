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
        <h2>Bienvenido de vuelta</h2>
        <p>Ingresa tus credenciales para acceder a la plataforma</p>
      </div>

      <form @submit.prevent="submit" class="auth-form">
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input 
            type="email" 
            id="email" 
            v-model="form.email" 
            placeholder="ejemplo@correo.com" 
            :class="{ 'input-error': form.errors.email }"
            required 
          />
          <span v-if="form.errors.email" class="error-text">{{ form.errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input 
            type="password" 
            id="password" 
            v-model="form.password" 
            placeholder="••••••••" 
            :class="{ 'input-error': form.errors.password }"
            required 
          />
          <span v-if="form.errors.password" class="error-text">{{ form.errors.password }}</span>
        </div>

        <div class="form-options">
          <label class="checkbox-container">
            <input type="checkbox" v-model="form.remember" />
            <span class="checkmark"></span>
            Recordarme
          </label>
          <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
          {{ form.processing ? 'Iniciando sesión...' : 'Ingresar' }}
        </button>

        <p class="auth-footer">
          ¿No tienes una cuenta? 
          <a href="/register" class="auth-link">Regístrate aquí</a>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3'

export default {
  name: 'Login',
  setup() {
    const form = useForm({
      email: '',
      password: '',
      remember: false,
    })

    const submit = () => {
      form.post('/login', {
        onFinish: () => form.reset('password'),
      })
    }

    return { form, submit }
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
  padding: 1.5rem;
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
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
  top: -50px;
  left: 20%;
}

.blob-2 {
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, var(--color-secondary) 0%, transparent 70%);
  bottom: -50px;
  right: 20%;
}

/* Authentication Card */
.auth-card {
  position: relative;
  z-index: 10;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 3rem 2.5rem;
  width: 100%;
  max-width: 450px;
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

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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

.form-group input {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  transition: all 0.3s ease;
  outline: none;
}

.form-group input::placeholder {
  color: rgba(255, 255, 255, 0.25);
}

.form-group input:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
}

.form-group input.input-error {
  border-color: var(--color-accent);
}

.error-text {
  font-size: 0.8rem;
  color: var(--color-accent);
}

/* Options */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.forgot-link {
  color: var(--color-secondary);
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

/* Custom Checkbox */
.checkbox-container {
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 1.75rem;
  cursor: pointer;
  user-select: none;
  color: var(--color-text-muted);
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
  top: 0;
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
