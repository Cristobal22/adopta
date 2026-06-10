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
            <span class="badge-role">Bazar & Club</span>
          </div>
          <div class="user-menu">
            <Link href="/dashboard" class="btn btn-secondary btn-sm" v-if="$page.props.auth.user">Volver al Dashboard</Link>
            <Link href="/" class="btn btn-secondary btn-sm" v-else>Volver al Inicio</Link>
          </div>
        </header>

        <!-- Section Header -->
        <div class="section-header">
          <h1>Bazar Animal y Club de Huellas</h1>
          <p class="subtitle">Canjea tus puntos por recompensas y apoya a emprendedores locales certificados.</p>
        </div>

        <!-- Flash messages for Cupón / MercadoPago Checkout Link -->
        <div class="flash-alert success-alert" v-if="$page.props.flash.success">
          <div class="alert-content">
            <span class="alert-icon">✓</span>
            <div>
              <strong>Acción Procesada:</strong> {{ $page.props.flash.success }}
              <!-- MercadoPago Checkout Button -->
              <div v-if="$page.props.flash.checkout_url" style="margin-top: 1rem;">
                <a :href="$page.props.flash.checkout_url" target="_blank" class="btn btn-mercadopago">
                  💳 Pagar con MercadoPago
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="flash-alert error-alert" v-if="errors.points">
          <div class="alert-content">
            <span class="alert-icon">✕</span>
            <div><strong>Error:</strong> {{ errors.points }}</div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs-nav">
          <button class="tab-btn" :class="{ active: currentTab === 'bazar' }" @click="currentTab = 'bazar'">
            🛍️ Bazar Animal (Vitrina)
          </button>
          <button class="tab-btn" :class="{ active: currentTab === 'club' }" @click="currentTab = 'club'">
            🐾 Club de Huellas (Recompensas)
          </button>
          <button class="tab-btn" :class="{ active: currentTab === 'premium' }" @click="currentTab = 'premium'">
            👑 Suscripciones Premium
          </button>
        </div>

        <!-- TAB 1: BAZAR ANIMAL VITRINA -->
        <div v-show="currentTab === 'bazar'" class="bazar-container">
          <div class="bazar-grid">
            <div class="product-card" v-for="product in products" :key="product.id">
              <div class="product-header">
                <span class="product-emoji">{{ product.image_placeholder }}</span>
                <span class="solidary-badge" v-if="product.is_solidary">
                  ❤️ Solidario
                </span>
              </div>
              <div class="product-body">
                <span class="store-name">🏬 {{ product.store_name }}</span>
                <h4>{{ product.title }}</h4>
                <div class="price-row">
                  <span class="price-val">${{ formatMoney(product.price) }}</span>
                  <span class="tax-desc" v-if="product.is_solidary">Dona 10% a rescates</span>
                </div>
              </div>
              <div class="product-actions">
                <button class="btn btn-primary btn-block btn-sm" @click="buyProduct(product)">
                  Contactar Tienda
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: CLUB DE HUELLAS GAMIFICACION -->
        <div v-show="currentTab === 'club'" class="club-container">
          <!-- User Points Header Card -->
          <div class="points-header-card" v-if="$page.props.auth.user">
            <span class="huella-decor-icon">🐾</span>
            <div class="points-info">
              <h2>Tienes <span class="points-val">{{ points }}</span> Huellas</h2>
              <p>Gana puntos realizando seguimientos diarios de adopciones, completando vacunas o conduciendo traslados solidarios.</p>
            </div>
          </div>
          <div class="points-header-card" v-else>
            <span class="huella-decor-icon">🐾</span>
            <div class="points-info">
              <h2>¿Quieres ganar Huellas?</h2>
              <p>Regístrate en la plataforma y gana puntos por realizar seguimientos diarios de tus adopciones, reportar vacunas o ayudar en traslados.</p>
              <div style="margin-top: 1rem;">
                <Link href="/register" class="btn btn-primary btn-sm">Crear Cuenta</Link>
              </div>
            </div>
          </div>

          <!-- Rewards Grid -->
          <div class="rewards-grid">
            <div class="reward-card" v-for="reward in rewards" :key="reward.id">
              <div class="reward-cost-badge">
                <span>{{ reward.cost }}</span>
                <span class="huella-label">Huellas</span>
              </div>
              <h4>{{ reward.title }}</h4>
              <p class="reward-sponsor">Ofrecido por: <strong>{{ reward.sponsor }}</strong></p>
              <p class="reward-desc">{{ reward.description }}</p>
              
              <button 
                class="btn btn-primary btn-block btn-sm" 
                :disabled="points < reward.cost"
                @click="claimReward(reward)"
              >
                {{ points < reward.cost ? 'Huellas Insuficientes' : 'Canjear Premio' }}
              </button>
            </div>
          </div>
        </div>

        <!-- TAB 3: PREMIUM SUBSCRIPTIONS (MERCADOPAGO CHEKOUT) -->
        <div v-show="currentTab === 'premium'" class="premium-container">
          <div class="premium-grid">
            <div class="plan-card">
              <div class="plan-badge">Popular</div>
              <h3>Plan Visibilidad Bronce</h3>
              <div class="plan-price">$4.990<span>/mes</span></div>
              <p class="plan-desc">Aparece destacado en la vitrina del Bazar Animal y accede al sello de Emprendimiento Solidario.</p>
              
              <ul class="plan-features">
                <li>✓ 2 publicaciones destacadas</li>
                <li>✓ Sello de Emprendimiento Solidario</li>
                <li>✓ Métricas de visualización</li>
              </ul>

              <button class="btn btn-primary btn-block" @click="initiatePremiumPlan('Suscripción Visibilidad Bronce', 4990)">
                💳 Suscribirse con MercadoPago
              </button>
            </div>

            <div class="plan-card plan-gold">
              <div class="plan-badge badge-gold">Recomendado</div>
              <h3>Plan Visibilidad Oro</h3>
              <div class="plan-price">$9.990<span>/mes</span></div>
              <p class="plan-desc">Posicionamiento en portada garantizado, notificaciones a adoptantes locales e insignias especiales.</p>
              
              <ul class="plan-features">
                <li>✓ Publicación en el Top-3 de portada</li>
                <li>✓ Envío de correos promocionales</li>
                <li>✓ Sello Dorado de Emprendimiento</li>
                <li>✓ Soporte prioritario</li>
              </ul>

              <button class="btn btn-primary btn-block" @click="initiatePremiumPlan('Suscripción Visibilidad Oro', 9990)">
                💳 Suscribirse con MercadoPago
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

export default {
  name: 'Hub',
  components: {
    Link,
  },
  props: {
    products: Array,
    rewards: Array,
    points: Number,
    errors: Object,
  },
  setup() {
    const currentTab = ref('bazar')

    const claimReward = (reward) => {
      if (confirm(`¿Estás seguro de que quieres canjear "${reward.title}" por ${reward.cost} Huellas?`)) {
        router.post('/bazar/claim-reward', {
          reward_id: reward.id,
          cost: reward.cost,
        })
      }
    }

    const initiatePremiumPlan = (planName, price) => {
      router.post('/bazar/premium', {
        plan_name: planName,
        price: price,
      })
    }

    const buyProduct = (product) => {
      alert(`Contacto iniciado con la tienda "${product.store_name}" para adquirir el producto "${product.title}".`)
    }

    const formatMoney = (val) => {
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    }

    return {
      currentTab,
      claimReward,
      initiatePremiumPlan,
      buyProduct,
      formatMoney,
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
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
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

/* Flash alerts */
.flash-alert {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #a7f3d0;
  padding: 1.25rem 1.5rem;
  border-radius: 14px;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.error-alert {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: #fecdd3;
}

.alert-content {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.alert-icon {
  font-size: 1.25rem;
  font-weight: 700;
}

.btn-mercadopago {
  background: #009ee3;
  color: white;
  font-weight: bold;
  border-radius: 8px;
  padding: 0.5rem 1.2rem;
  font-size: 0.85rem;
  text-decoration: none;
  display: inline-block;
  box-shadow: 0 4px 10px rgba(0, 158, 227, 0.3);
}

.btn-mercadopago:hover {
  background: #0084c0;
}

/* Tabs Navigation */
.tabs-nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 2.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1rem;
}

.tab-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-family: var(--font-body);
  font-size: 1.05rem;
  font-weight: 600;
  padding: 0.5rem 1rem;
  cursor: pointer;
  position: relative;
  transition: color 0.3s ease;
}

.tab-btn:hover {
  color: var(--color-text-main);
}

.tab-btn.active {
  color: var(--color-primary);
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -1.25rem;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--color-primary);
}

/* TAB 1: BAZAR GRID */
.bazar-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

@media (max-width: 968px) {
  .bazar-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .bazar-grid {
    grid-template-columns: 1fr;
  }
}

.product-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  box-sizing: border-box;
}

.product-header {
  height: 140px;
  background: rgba(255, 255, 255, 0.01);
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.product-emoji {
  font-size: 3.5rem;
}

.solidary-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: var(--color-accent);
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.product-body {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.store-name {
  font-size: 0.75rem;
  color: var(--color-secondary);
  font-weight: 600;
}

.product-body h4 {
  font-family: var(--font-title);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.35;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.5rem;
}

.price-val {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--color-text-main);
}

.tax-desc {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* TAB 2: CLUB DE HUELLAS */
.points-header-card {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.12) 0%, rgba(255,255,255,0.01) 100%);
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 20px;
  padding: 2.25rem;
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 2.5rem;
}

@media (max-width: 576px) {
  .points-header-card {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
}

.huella-decor-icon {
  font-size: 3.5rem;
  background: rgba(139, 92, 246, 0.15);
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.points-info h2 {
  font-family: var(--font-title);
  font-size: 2rem;
  font-weight: 800;
  margin: 0 0 0.5rem 0;
}

.points-val {
  color: #c084fc;
}

.points-info p {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  line-height: 1.45;
  margin: 0;
  max-width: 500px;
}

.rewards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

@media (max-width: 968px) {
  .rewards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .rewards-grid {
    grid-template-columns: 1fr;
  }
}

.reward-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  box-sizing: border-box;
}

.reward-cost-badge {
  align-self: flex-start;
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
  padding: 0.4rem 0.8rem;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.reward-cost-badge span {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 800;
}

.huella-label {
  font-size: 0.6rem;
  text-transform: uppercase;
}

.reward-card h4 {
  font-family: var(--font-title);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.35;
}

.reward-sponsor {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin: -0.5rem 0 0 0;
}

.reward-desc {
  font-size: 0.85rem;
  line-height: 1.4;
  color: var(--color-text-muted);
  margin: 0;
  flex-grow: 1;
}

/* TAB 3: PREMIUM SUBSCRIPTIONS */
.premium-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

@media (max-width: 600px) {
  .premium-grid {
    grid-template-columns: 1fr;
  }
}

.plan-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  padding: 3rem 2.25rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  position: relative;
}

.plan-gold {
  border-color: rgba(245, 158, 11, 0.3);
  background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.04), transparent);
}

.plan-badge {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.3rem 0.75rem;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-gold {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--color-warning);
}

.plan-card h3 {
  font-family: var(--font-title);
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}

.plan-price {
  font-family: var(--font-title);
  font-size: 2.25rem;
  font-weight: 800;
}

.plan-price span {
  font-size: 1rem;
  font-weight: 500;
  color: var(--color-text-muted);
}

.plan-desc {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  line-height: 1.45;
  margin: 0;
}

.plan-features {
  list-style: none;
  padding: 0;
  margin: 0.5rem 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  font-size: 0.9rem;
}

.plan-features li {
  color: var(--color-text-main);
  font-weight: 500;
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
