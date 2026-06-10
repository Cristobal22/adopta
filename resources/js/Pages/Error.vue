<template>
  <div class="error-page-container">
    <div class="bg-gradient-circle bg-circle-1"></div>
    <div class="bg-gradient-circle bg-circle-2"></div>

    <div class="error-card">
      <!-- Icono / Ilustración SVG Dinámica -->
      <div class="illustration-container">
        <!-- SVG para 404 (Perro buscando rastro) -->
        <svg v-if="status === 404" viewBox="0 0 200 200" class="error-svg">
          <circle cx="100" cy="100" r="80" fill="var(--color-bg)" stroke="var(--color-primary)" stroke-width="2" stroke-dasharray="6,6" />
          <!-- Huellas -->
          <circle cx="70" cy="70" r="8" fill="var(--color-text-muted)" opacity="0.4" />
          <circle cx="85" cy="55" r="8" fill="var(--color-text-muted)" opacity="0.6" />
          <circle cx="115" cy="55" r="8" fill="var(--color-text-muted)" opacity="0.8" />
          <circle cx="130" cy="70" r="8" fill="var(--color-primary)" />
          <!-- Perrito simplificado -->
          <path d="M80 120c0-10 8-18 18-18h4c10 0 18 8 18 18v15H80v-15z" fill="var(--color-text-main)" />
          <circle cx="92" cy="115" r="2" fill="var(--color-bg)" />
          <circle cx="108" cy="115" r="2" fill="var(--color-bg)" />
          <!-- Orejas -->
          <path d="M75 105c-3 0-5 5-2 10l5 8V105h-3z" fill="var(--color-primary)" />
          <path d="M125 105c3 0 5 5 2 10l-5 8V105h3z" fill="var(--color-primary)" />
          <!-- Nariz de detective -->
          <circle cx="100" cy="123" r="3" fill="var(--color-secondary)" />
          <!-- Lupa -->
          <circle cx="120" cy="120" r="15" fill="none" stroke="var(--color-primary)" stroke-width="3" />
          <line x1="130" y1="130" x2="145" y2="145" stroke="var(--color-primary)" stroke-width="4" stroke-linecap="round" />
        </svg>

        <!-- SVG para 403 (Gato durmiendo resguardando entrada) -->
        <svg v-else-if="status === 403" viewBox="0 0 200 200" class="error-svg">
          <circle cx="100" cy="100" r="80" fill="var(--color-bg)" stroke="var(--color-secondary)" stroke-width="2" />
          <!-- Escudo de Prohibición -->
          <circle cx="100" cy="100" r="65" fill="none" stroke="#dc2626" stroke-width="4" opacity="0.15" />
          <!-- Gato durmiendo -->
          <path d="M60 130c0-15 15-25 40-25s40 10 40 25v5H60v-5z" fill="var(--color-text-main)" />
          <path d="M65 110l-5-10 10 3z" fill="var(--color-primary)" />
          <path d="M135 110l5-10-10 3z" fill="var(--color-primary)" />
          <path d="M80 120q5 3 10 0" stroke="var(--color-bg)" stroke-width="2" fill="none" />
          <path d="M110 120q5 3 10 0" stroke="var(--color-bg)" stroke-width="2" fill="none" />
          <!-- Cola -->
          <path d="M140 130q15 0 10-15" stroke="var(--color-text-main)" stroke-width="6" stroke-linecap="round" fill="none" />
          <!-- Candado -->
          <rect x="90" y="70" width="20" height="15" rx="3" fill="var(--color-secondary)" />
          <path d="M94 70V63a6 6 0 0 1 12 0v7" fill="none" stroke="var(--color-secondary)" stroke-width="3" />
        </svg>

        <!-- SVG para 500 / 503 (Mascota con herramientas / reparación) -->
        <svg v-else viewBox="0 0 200 200" class="error-svg">
          <circle cx="100" cy="100" r="80" fill="var(--color-bg)" stroke="var(--color-primary)" stroke-width="2" />
          <!-- Engranajes -->
          <circle cx="80" cy="75" r="15" fill="none" stroke="var(--color-text-muted)" stroke-width="3" stroke-dasharray="8,4" />
          <circle cx="115" cy="85" r="10" fill="none" stroke="var(--color-secondary)" stroke-width="3" stroke-dasharray="6,3" />
          <!-- Perrito con collar de herramientas -->
          <path d="M70 140c0-12 10-20 22-20h16c12 0 22 8 22 20v5H70v-5z" fill="var(--color-text-main)" />
          <!-- Casco de constructor -->
          <path d="M82 108c0-8 8-15 18-15s18 7 18 15h-36z" fill="var(--color-primary)" />
          <rect x="78" y="108" width="44" height="4" rx="2" fill="var(--color-primary)" />
          <!-- Nariz -->
          <circle cx="100" cy="128" r="3" fill="var(--color-text-main)" />
          <!-- Llave inglesa -->
          <path d="M125 125l15 15" stroke="var(--color-secondary)" stroke-width="4" stroke-linecap="round" />
          <path d="M123 123m2 2a4 4 0 0 1-6-6" stroke="var(--color-secondary)" stroke-width="3" fill="none" />
        </svg>
      </div>

      <!-- Código de Error -->
      <h1 class="error-code">{{ status }}</h1>

      <!-- Título de Error -->
      <h2 class="error-title">{{ title }}</h2>

      <!-- Descripción Amigable -->
      <p class="error-description">{{ description }}</p>

      <!-- Botones de Acción -->
      <div class="actions-container">
        <a href="/" class="btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Ir al Inicio
        </a>
        <a href="/pets" class="btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Ver Mascotas
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    status: {
      type: Number,
      required: true,
    },
  },
  computed: {
    title() {
      switch (this.status) {
        case 403:
          return 'Acceso Restringido';
        case 404:
          return 'Rastro Perdido';
        case 503:
          return 'Zona en Mantenimiento';
        case 500:
        default:
          return 'Error en el Ecosistema';
      }
    },
    description() {
      switch (this.status) {
        case 403:
          return 'Lo sentimos, no tienes los permisos necesarios para explorar esta sección protegida.';
        case 404:
          return 'No pudimos encontrar la página que buscas. Parece que la mascota ha corrido a otro lugar o el enlace es incorrecto.';
        case 503:
          return 'Estamos limpiando la zona de juegos del refugio. Por favor, vuelve en unos minutos.';
        case 500:
        default:
          return 'Algo se ha enredado en nuestro servidor interno. Nuestro equipo técnico ya está investigando para resolverlo.';
      }
    },
  },
};
</script>

<style scoped>
.error-page-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: var(--color-bg);
  font-family: 'Plus Jakarta Sans', sans-serif;
  position: relative;
  overflow: hidden;
  padding: 1.5rem;
  box-sizing: border-box;
}

/* Blobs de Gradiente */
.bg-gradient-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  z-index: 1;
  opacity: 0.12;
}

.bg-circle-1 {
  width: 400px;
  height: 400px;
  background-color: var(--color-primary);
  top: -100px;
  right: -100px;
}

.bg-circle-2 {
  width: 350px;
  height: 350px;
  background-color: var(--color-secondary);
  bottom: -50px;
  left: -50px;
}

/* Tarjeta de Error Glassmorphic */
.error-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 24px;
  padding: 3rem 2rem;
  width: 100%;
  max-width: 500px;
  text-align: center;
  box-shadow: 0 10px 30px -5px rgba(115, 107, 99, 0.08);
  z-index: 10;
  box-sizing: border-box;
}

.illustration-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.error-svg {
  width: 130px;
  height: 130px;
  filter: drop-shadow(0 4px 6px rgba(115, 107, 99, 0.06));
}

.error-code {
  font-size: 5rem;
  font-weight: 800;
  line-height: 1;
  margin: 0;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -2px;
}

.error-title {
  font-family: var(--font-title);
  font-size: 2rem;
  color: var(--color-text-main);
  margin-top: 0.5rem;
  margin-bottom: 0.75rem;
}

.error-description {
  color: var(--color-text-muted);
  font-size: 0.95rem;
  line-height: 1.6;
  margin-top: 0;
  margin-bottom: 2rem;
  padding: 0 1rem;
}

/* Contenedor de Acciones */
.actions-container {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.btn-primary {
  background-color: var(--color-primary);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(255, 107, 74, 0.2);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 74, 0.3);
}

.btn-secondary {
  background-color: transparent;
  color: var(--color-text-main) !important;
  border: 1px solid rgba(115, 107, 99, 0.2);
}

.btn-secondary:hover {
  background-color: rgba(115, 107, 99, 0.04);
  border-color: rgba(115, 107, 99, 0.3);
  transform: translateY(-2px);
}

@media (max-width: 480px) {
  .error-card {
    padding: 2rem 1.25rem;
  }
  .error-code {
    font-size: 4rem;
  }
  .error-title {
    font-size: 1.75rem;
  }
  .actions-container {
    flex-direction: column;
    width: 100%;
  }
  .btn-primary,
  .btn-secondary {
    width: 100%;
    box-sizing: border-box;
  }
}
</style>
