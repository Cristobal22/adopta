<template>
  <div class="sponsor-container">
    <div class="bg-gradient-circle blob-1"></div>
    <div class="bg-gradient-circle blob-2"></div>

    <header class="header">
      <div class="logo">
        <span class="logo-icon">🐾</span>
        <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
      </div>
      <div class="user-menu">
        <Link href="/pets" class="btn btn-secondary btn-sm">Volver al Catálogo</Link>
      </div>
    </header>

    <main class="main-content">
      <div class="section-header">
        <div class="pet-sponsor-profile">
          <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="sponsor-pet-photo" />
          <div class="pet-sponsor-details">
            <div class="badge">Padrino Virtual</div>
            <h1>Apadrina a <span class="gradient-text">{{ pet.name }}</span></h1>
            <p class="subtitle">Aporta mensualmente para cubrir sus gastos de comida y veterinaria mientras encuentra un hogar definitivo.</p>
          </div>
        </div>
      </div>

      <!-- Main Layout Grid -->
      <div class="sponsor-layout">
        <!-- Plans Column -->
        <div class="plans-column">
          <h3>1. Selecciona tu Plan de Apadrinamiento</h3>
          
          <div class="plans-grid">
            <!-- Bronce -->
            <div 
              class="plan-card" 
              :class="{ active: selectedTier === 'bronce' }"
              @click="selectPlan('bronce', 4990)"
            >
              <div class="plan-header">
                <span class="plan-icon">🥉</span>
                <h4>Plan Bronce</h4>
              </div>
              <div class="plan-price">$4.990<span>/mes</span></div>
              <p class="plan-desc">Aporta el 25% de su alimentación mensual y recibe reportes médicos.</p>
              <span class="badge-points">+50 Huellas/mes</span>
            </div>

            <!-- Plata -->
            <div 
              class="plan-card plan-silver" 
              :class="{ active: selectedTier === 'plata' }"
              @click="selectPlan('plata', 9990)"
            >
              <div class="plan-header">
                <span class="plan-icon">🥈</span>
                <h4>Plan Plata</h4>
              </div>
              <div class="plan-price">$9.990<span>/mes</span></div>
              <p class="plan-desc">Cubre el 50% de sus gastos y recibe videos exclusivos de sus paseos.</p>
              <span class="badge-points">+100 Huellas/mes</span>
            </div>

            <!-- Oro -->
            <div 
              class="plan-card plan-gold" 
              :class="{ active: selectedTier === 'oro' }"
              @click="selectPlan('oro', 19990)"
            >
              <div class="plan-header">
                <span class="plan-icon">🥇</span>
                <h4>Plan Oro</h4>
              </div>
              <div class="plan-price">$19.990<span>/mes</span></div>
              <p class="plan-desc">Cubre el 100% de su alimentación y recibe llamadas de agradecimiento virtuales.</p>
              <span class="badge-points">+200 Huellas/mes</span>
            </div>
          </div>
        </div>

        <!-- Checkout Form Column -->
        <div class="form-column">
          <div class="checkout-card">
            <h3>2. Datos de Facturación y Pago</h3>
            
            <form @submit.prevent="submit" class="checkout-form">
              <!-- Name -->
              <div class="form-group">
                <label for="sponsor_name">Tu Nombre Completo</label>
                <input 
                  type="text" 
                  id="sponsor_name" 
                  v-model="form.sponsor_name" 
                  placeholder="Juan Pérez" 
                  required
                />
              </div>

              <!-- Email -->
              <div class="form-group">
                <label for="sponsor_email">Correo Electrónico</label>
                <input 
                  type="email" 
                  id="sponsor_email" 
                  v-model="form.sponsor_email" 
                  placeholder="juan@correo.com" 
                  required
                />
                <span class="hint-text">Enviaremos actualizaciones sobre {{ pet.name }} aquí.</span>
              </div>

              <!-- Mock Credit Card Info -->
              <div class="payment-box">
                <div class="payment-header">
                  <span>💳 Pago Seguro Simulado</span>
                  <span class="card-brands">Visa / Mastercard / MP</span>
                </div>
                
                <div class="form-group">
                  <label for="card_num">Número de Tarjeta</label>
                  <input 
                    type="text" 
                    id="card_num" 
                    placeholder="4557 •••• •••• 8812" 
                    value="4557 1234 5678 8812" 
                    disabled
                  />
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="card_exp">Vencimiento</label>
                    <input type="text" id="card_exp" placeholder="12/29" value="12/29" disabled />
                  </div>
                  <div class="form-group">
                    <label for="card_cvv">CVV</label>
                    <input type="text" id="card_cvv" placeholder="123" value="123" disabled />
                  </div>
                </div>
              </div>

              <!-- Total Row -->
              <div class="total-row">
                <span>Total a Pagar:</span>
                <strong>${{ formatMoney(form.amount) }} / mes</strong>
              </div>

              <!-- Submit Button -->
              <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
                {{ form.processing ? 'Procesando...' : '💳 Confirmar y Apadrinar con MercadoPago' }}
              </button>

              <!-- Legal / Transparency Note -->
              <div class="transparency-note" style="margin-top: 1.25rem; padding: 0.85rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.4; text-align: left;">
                🔒 <strong>Nota de Transparencia:</strong> Tu aporte se destina prioritariamente a cubrir la alimentación, vacunas y cuidados de <strong>{{ pet.name }}</strong>. En caso de superarse su presupuesto mensual o ser adoptado, el excedente se destinará al Fondo Común de Rescate para apoyar al resto de los animales del refugio.
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

export default {
  name: 'Sponsor',
  components: {
    Link,
  },
  props: {
    pet: Object,
  },
  setup(props) {
    const page = usePage()
    const currentUser = page.props.auth.user

    const selectedTier = ref('plata')
    const selectedAmount = ref(9990)

    const form = useForm({
      tier: 'plata',
      amount: 9990,
      sponsor_name: currentUser ? currentUser.name : '',
      sponsor_email: currentUser ? currentUser.email : '',
    })

    const selectPlan = (tier, amount) => {
      selectedTier.value = tier
      selectedAmount.value = amount
      form.tier = tier
      form.amount = amount
    }

    const submit = () => {
      form.post(`/pets/${props.pet.id}/sponsor/process`)
    }

    const formatMoney = (val) => {
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    }

    return {
      selectedTier,
      form,
      selectPlan,
      submit,
      formatMoney,
    }
  }
}
</script>

<style scoped>
.sponsor-container {
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  background-color: var(--color-bg);
  color: var(--color-text-main);
  font-family: var(--font-body);
}

/* Blobs */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.12;
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

/* Header */
.header {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1200px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  margin: 0 auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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

/* Content Layout */
.main-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
  box-sizing: border-box;
}

.section-header {
  margin-bottom: 3rem;
}

.pet-sponsor-profile {
  display: flex;
  align-items: center;
  gap: 2rem;
  width: 100%;
}

.sponsor-pet-photo {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--color-primary);
  box-shadow: 0 8px 24px rgba(255, 107, 74, 0.15);
  flex-shrink: 0;
}

.pet-sponsor-details {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.75rem;
}

@media (max-width: 768px) {
  .pet-sponsor-profile {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }
  
  .sponsor-pet-photo {
    width: 90px;
    height: 90px;
  }
}

.badge {
  background: rgba(255, 107, 74, 0.15);
  border: 1px solid rgba(255, 107, 74, 0.3);
  color: var(--color-primary);
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 600;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.section-header h1 {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
}

.gradient-text {
  background: linear-gradient(135deg, var(--color-primary) 0%, #fb7185 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  color: var(--color-text-muted);
  font-size: 1rem;
  margin: 0;
}

/* Layout Columns */
.sponsor-layout {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 3rem;
}

@media (max-width: 968px) {
  .sponsor-layout {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }
}

.plans-column h3, .form-column h3 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 1.5rem 0;
  color: var(--color-text-main);
}

.plans-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.plan-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 1.5rem 2rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: grid;
  grid-template-columns: 1fr auto;
  grid-template-rows: auto auto;
  align-items: center;
  gap: 0.5rem;
}

.plan-card:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateX(4px);
}

.plan-card.active {
  background: rgba(255, 107, 74, 0.05);
  border-color: var(--color-primary);
  box-shadow: 0 0 20px rgba(255, 107, 74, 0.1);
}

.plan-card.plan-silver.active {
  background: rgba(13, 148, 136, 0.05);
  border-color: var(--color-secondary);
  box-shadow: 0 0 20px rgba(13, 148, 136, 0.1);
}

.plan-card.plan-gold.active {
  background: rgba(245, 158, 11, 0.05);
  border-color: var(--color-warning);
  box-shadow: 0 0 20px rgba(245, 158, 11, 0.1);
}

.plan-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  grid-column: 1;
}

.plan-icon {
  font-size: 1.75rem;
}

.plan-header h4 {
  font-family: var(--font-title);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
}

.plan-price {
  font-family: var(--font-title);
  font-size: 1.5rem;
  font-weight: 800;
  grid-column: 2;
  grid-row: 1;
  text-align: right;
}

.plan-price span {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

.plan-desc {
  grid-column: 1;
  grid-row: 2;
  color: var(--color-text-muted);
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.4;
}

.badge-points {
  grid-column: 2;
  grid-row: 2;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-primary);
  background: rgba(255, 107, 74, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  text-align: right;
  align-self: center;
}

.plan-silver .badge-points {
  color: var(--color-secondary);
  background: rgba(13, 148, 136, 0.1);
}

.plan-gold .badge-points {
  color: var(--color-warning);
  background: rgba(245, 158, 11, 0.1);
}

/* Checkout Form */
.checkout-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 2.25rem;
  box-sizing: border-box;
}

.checkout-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-muted);
}

.form-group input {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.95rem;
  outline: none;
  transition: all 0.3s ease;
}

.form-group input:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.hint-text {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

/* Payment Box */
.payment-box {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.payment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--color-text-muted);
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  padding-bottom: 0.75rem;
}

.card-brands {
  font-size: 0.75rem;
  color: var(--color-primary);
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.total-row span {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

.total-row strong {
  font-family: var(--font-title);
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-text-main);
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
  box-shadow: 0 4px 14px rgba(255, 107, 74, 0.3);
}

.btn-primary:hover {
  background: var(--color-primary-hover);
  transform: translateY(-2px);
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

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}

.btn-block {
  width: 100%;
}
</style>
