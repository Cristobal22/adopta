<template>
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-brand">
        <Link href="/" class="logo-footer">
          <span class="logo-icon">🐾</span>
          <span class="logo-text">Adopta<span class="logo-dot">.</span></span>
        </Link>
        <p>Ecosistema tecnológico y solidario para la tenencia responsable.</p>
      </div>
      <div class="footer-links">
        <h3>Plataforma</h3>
        <Link href="/pets">Adoptar</Link>
        <Link href="/bazar">Bazar Animal</Link>
        <Link href="/nosotros">Nosotros</Link>
      </div>
      <div class="footer-links">
        <h3>Legal</h3>
        <a href="#">Términos de Servicio</a>
        <a href="#">Política de Privacidad</a>
        <a href="#">Licencias Contractuales</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Adopta.cl. Todos los derechos reservados. Creado para la protección animal y tenencia responsable.</p>
    </div>
  </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
.footer {
  width: 100%;
  max-width: 1200px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  margin-top: 7rem;
  padding: 4rem 2rem 2rem 2rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 3rem;
  position: relative;
  z-index: 10;
  margin-left: auto;
  margin-right: auto;
}

.footer-content {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 4rem;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

.footer-brand {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.logo-footer {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
}

.logo-icon {
  font-size: 1.75rem;
}

.logo-text {
  font-family: var(--font-title);
  font-weight: 800;
  font-size: 1.5rem;
  letter-spacing: -0.5px;
  color: var(--color-text-main);
}

.logo-dot {
  color: var(--color-primary);
}

.footer-brand p {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  line-height: 1.5;
  max-width: 300px;
  margin: 0;
}

.footer-links {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.footer-links h3 {
  font-family: var(--font-title);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-main);
}

.footer-links a {
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.footer-links a:hover {
  color: var(--color-text-main);
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.03);
  padding-top: 2rem;
  text-align: center;
}

.footer-bottom p {
  color: #64748b;
  font-size: 0.85rem;
  margin: 0;
}
</style>
