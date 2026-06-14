<template>
  <div class="backoffice-container" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="bg-gradient-circle blob-1"></div>

    <Header />

    <!-- Main Content -->
    <main class="main-content" style="width: 100%; max-width: 1200px; margin: 0 auto; flex-grow: 1; padding: 2rem; box-sizing: border-box; position: relative; z-index: 10;">

        <!-- Flash Messages -->
        <div class="flash-alert success-alert" v-if="$page.props.flash.success">
          <div class="alert-content">
            <span class="alert-icon">✓</span>
            <div>
              <div>{{ $page.props.flash.success }}</div>
              <div v-if="$page.props.flash.checkout_url" style="margin-top: 0.75rem;">
                <a :href="$page.props.flash.checkout_url" target="_blank" class="btn btn-primary btn-sm" style="background: #0d9488; color: white;">
                  💳 Completar Pago Seguro en MercadoPago
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="flash-alert error-alert" v-if="$page.props.errors && Object.keys($page.props.errors).length > 0">
          <div class="alert-content">
            <span class="alert-icon">✕</span>
            <div>
              <div v-for="(err, key) in $page.props.errors" :key="key">{{ err }}</div>
            </div>
          </div>
        </div>

        <!-- Section Header -->
        <div class="section-header">
          <div v-if="!user || ['adoptante', 'transito', 'donante'].includes(user.role)">
            <h1>Mascotas en Adopción</h1>
            <p class="subtitle">Encuentra a tu compañero de vida y postula para iniciar el proceso de adopción.</p>
          </div>
          <div v-else>
            <h1>Administración de Mascotas</h1>
            <p class="subtitle">Registra y edita el historial clínico y características de los animales bajo resguardo.</p>
          </div>
          <Link href="/backoffice/pets/create" class="btn btn-primary" v-if="user && ['admin', 'fundacion', 'rescatista'].includes(user.role)">
            + Registrar Mascota
          </Link>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card">
          <div class="filter-group search-box">
            <label for="search">Buscar</label>
            <input 
              type="text" 
              id="search" 
              v-model="search" 
              placeholder="Nombre, raza o microchip..." 
              @keyup.enter="applyFilters"
            />
          </div>

          <div class="filter-group">
            <label for="species">Especie</label>
            <select id="species" v-model="species" @change="applyFilters">
              <option value="">Todas</option>
              <option value="perro">Perros</option>
              <option value="gato">Gatos</option>
              <option value="otro">Otros</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="status">Estado</label>
            <select id="status" v-model="status" @change="applyFilters">
              <option value="">Todos</option>
              <option value="rescatado">Rescatado</option>
              <option value="en_transito">En Tránsito</option>
              <option value="en_adopcion">En Adopción</option>
              <option value="adoptado">Adoptado</option>
            </select>
          </div>

          <div class="filter-actions">
            <button class="btn btn-outline btn-full" @click="applyFilters">Filtrar</button>
            <button class="btn btn-secondary btn-full" @click="resetFilters">Limpiar</button>
          </div>
        </div>

        <!-- Pets Table / Grid -->
        <div class="table-container">
          <table class="data-table" v-if="pets.data.length > 0">
            <thead>
              <tr>
                <th>Mascota</th>
                <th>Especie / Raza</th>
                <th>Género / Edad</th>
                <th>Estado</th>
                <th>Microchip</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pet in pets.data" :key="pet.id">
                <td>
                  <div class="pet-profile-cell">
                    <img v-if="pet.photo_path" :src="'/' + pet.photo_path" :alt="pet.name" class="pet-avatar" />
                    <div v-else class="pet-avatar-placeholder">
                      {{ pet.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                      <div class="pet-name">{{ pet.name }}</div>
                      <div class="pet-id">ID: #{{ pet.id }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="text-capitalize">{{ pet.species }}</div>
                  <div class="text-muted">{{ pet.breed || 'Sin especificar' }}</div>
                </td>
                <td>
                  <div class="text-capitalize">{{ pet.gender }}</div>
                  <div class="text-muted">{{ pet.age_text || 'Sin especificar' }}</div>
                </td>
                <td>
                  <span class="status-badge" :class="'badge-' + pet.status">
                    {{ formatStatus(pet.status) }}
                  </span>
                </td>
                <td>
                  <span class="code-badge" v-if="pet.microchip_code">
                    📟 {{ pet.microchip_code }}
                  </span>
                  <span class="text-muted" v-else>Sin registrar</span>
                </td>
                <td class="text-right">
                  <div class="action-buttons" v-if="user && ['admin', 'fundacion', 'rescatista'].includes(user.role)">
                    <Link :href="'/backoffice/pets/' + pet.id + '/edit'" class="btn btn-secondary btn-icon" title="Editar">
                      ✏️
                    </Link>
                    <button class="btn btn-danger btn-icon" @click="deletePet(pet)" title="Eliminar">
                      🗑️
                    </button>
                    <Link :href="'/pets/' + pet.id + '/qr-pass'" class="btn btn-secondary btn-sm" title="Ver QR Pass">
                      🎫 Qr Pass
                    </Link>
                    <Link :href="'/pets/' + pet.id + '/flyer'" class="btn btn-secondary btn-sm" title="Generar Flyer">
                      🖼️ Flyer
                    </Link>
                    <button class="btn btn-secondary btn-sm" @click="sharePet(pet)" title="Compartir">
                      🔗 Compartir
                    </button>
                  </div>
                  <div class="action-buttons" v-else>
                    <button 
                      class="btn btn-primary btn-sm" 
                      @click="postular(pet.id)"
                      v-if="(!user || user.role !== 'donante') && pet.status === 'en_adopcion'"
                    >
                      Solicitar Adopción
                    </button>
                    <Link 
                      :href="'/pets/' + pet.id + '/sponsor'" 
                      class="btn btn-secondary btn-sm"
                      v-if="pet.status === 'en_adopcion' || pet.status === 'rescatado'"
                    >
                      💖 Apadrinar
                    </Link>
                    <Link :href="'/pets/' + pet.id + '/qr-pass'" class="btn btn-secondary btn-sm" v-if="pet.status === 'adoptado'">
                      🎫 Qr Pass
                    </Link>
                    <Link 
                      :href="'/pets/' + pet.id + '/flyer'" 
                      class="btn btn-secondary btn-sm" 
                      v-if="pet.status !== 'adoptado'"
                      title="Generar Flyer"
                    >
                      🖼️ Flyer
                    </Link>
                    <button class="btn btn-secondary btn-sm" @click="sharePet(pet)" title="Compartir">
                      🔗 Compartir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="empty-state" v-else>
            <div class="empty-icon">📂</div>
            <h3>No se encontraron mascotas</h3>
            <p>Intenta ajustar tus criterios de búsqueda o agrega una nueva mascota.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="pets.links.length > 3">
          <Link 
            v-for="(link, k) in pets.links" 
            :key="k" 
            :href="link.url || '#'" 
            class="page-link"
            :class="{ active: link.active, disabled: !link.url }"
            v-html="link.label"
          />
        </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import Footer from '../../Components/Footer.vue'

export default {
  name: 'Index',
  components: {
    Link,
    Header,
    Footer,
  },
  props: {
    pets: Object,
    filters: Object,
  },
  setup(props) {
    const search = ref(props.filters.search || '')
    const species = ref(props.filters.species || '')
    const status = ref(props.filters.status || '')

    const page = usePage()
    const user = page.props.auth.user

    const applyFilters = () => {
      router.get('/pets', {
        search: search.value,
        species: species.value,
        status: status.value,
      }, {
        preserveState: true,
        replace: true,
      })
    }

    const resetFilters = () => {
      search.value = ''
      species.value = ''
      status.value = ''
      applyFilters()
    }

    const deletePet = (pet) => {
      if (confirm(`¿Estás seguro de que deseas eliminar permanentemente a "${pet.name}" del ecosistema? Se auditará esta acción.`)) {
        router.delete(`/backoffice/pets/${pet.id}`)
      }
    }

    const postular = (petId) => {
      router.post('/adoptions', {
        pet_id: petId
      })
    }

    const formatStatus = (statusVal) => {
      const map = {
        rescatado: 'Rescatado',
        en_transito: 'En Tránsito',
        en_adopcion: 'En Adopción',
        adoptado: 'Adoptado',
      }
      return map[statusVal] || statusVal
    }

    const sharePet = (pet) => {
      const base = window.location.origin + (window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '')
      const shareUrl = `${base}/pets/${pet.id}/story`
      const shareTitle = `Conoce a ${pet.name} en Adopta`
      const shareText = `¡Mira el caso de ${pet.name}! Únete al ecosistema solidario de bienestar animal.`

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

    return {
      user,
      search,
      species,
      status,
      applyFilters,
      resetFilters,
      deletePet,
      postular,
      formatStatus,
      sharePet,
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


/* Section Header */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  gap: 1rem;
}

@media (max-width: 576px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .section-header .btn {
    width: 100%;
  }
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

/* Filters */
.filter-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  padding: 1.5rem;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1.25rem;
  align-items: flex-end;
  margin-bottom: 2rem;
  box-sizing: border-box;
}

@media (max-width: 768px) {
  .filter-card {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-muted);
}

.filter-group input, .filter-group select {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem 1rem;
  color: white;
  font-family: var(--font-body);
  font-size: 0.9rem;
  outline: none;
  transition: all 0.3s ease;
}

.filter-group input:focus, .filter-group select:focus {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.08);
}

.filter-group select option {
  background-color: var(--color-bg);
  color: white;
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-full {
  width: 100%;
}

/* Data Table */
.table-container {
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 20px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin-bottom: 2rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .data-table {
    min-width: 650px;
  }
}

.data-table th, .data-table td {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.data-table th {
  font-family: var(--font-body);
  font-weight: 700;
  color: var(--color-text-muted);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: rgba(255, 255, 255, 0.01);
}

.data-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.015);
}

.pet-profile-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pet-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.pet-avatar-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: #c084fc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}

.pet-name {
  font-weight: 600;
  color: var(--color-text-main);
  font-size: 0.95rem;
}

.pet-id {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.text-muted {
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.text-right {
  text-align: right;
}

/* Badges */
.status-badge {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-rescatado {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: var(--color-accent);
}

.badge-en_transito {
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.3);
  color: var(--color-secondary);
}

.badge-en_adopcion {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--color-warning);
}

.badge-adoptado {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
}

.code-badge {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  color: var(--color-text-main);
  font-family: monospace;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 0.9rem;
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

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-family: var(--font-title);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: var(--color-text-muted);
  max-width: 400px;
  margin: 0 auto;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem;
}

.page-link {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.page-link:hover {
  background: rgba(255, 255, 255, 0.06);
}

.page-link.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.page-link.disabled {
  opacity: 0.4;
  pointer-events: none;
}

/* Buttons */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.6rem 1.4rem;
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

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

.btn-outline {
  background: transparent;
  color: var(--color-text-main);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  background: rgba(139, 92, 246, 0.05);
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.4rem 1rem;
  border-radius: 8px;
}
</style>
