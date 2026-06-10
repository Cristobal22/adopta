# Ecosistema Adopta 🐾

Ecosistema Adopta es una plataforma tecnológica integral diseñada para interconectar de forma segura, eficiente y escalable a adoptantes, rescatistas independientes, hogares temporales y donantes. 

El proyecto combina un algoritmo de match etológico inteligente, seguimiento post-adopción automatizado, pasaporte digital QR SOS para mascotas perdidas, logística colaborativa y gamificación comunitaria.

---

## 🛠️ Arquitectura y Stack Tecnológico

La aplicación está estructurada como un sistema **SPA (Single Page Application)** moderno utilizando:

* **Backend:** [Laravel 11](file:///c:/wamp64/www/adopta/app) (PHP 8.2+) con Eloquent ORM.
* **Frontend:** [Vue 3](file:///c:/wamp64/www/adopta/resources/js) (Composition API, Setup script, Vite).
* **Puente (Inertia.js):** Conecta Laravel con Vue de manera transparente, permitiendo renderizado dinámico sin necesidad de APIs separadas.
* **Mapas (Leaflet.js):** Utilizado para el Radar Pet-Friendly, geolocalización de mascotas y SOS.
* **Pasarela de Pagos:** Simulación integrada de pasarela segura MercadoPago Sandbox para donaciones, bazar premium y apadrinamiento.

---

## 🌟 Funcionalidades Core

### 1. Match Inteligente y Postulaciones
* **Algoritmo de Compatibilidad:** Evalúa variables de estilo de vida del adoptante (tiempo, espacio, presencia de niños/otras mascotas) contra la personalidad y ficha clínica del animal.
* **Advertencias Administrativas:** Las incompatibilidades no bloquean automáticamente al adoptante (para evitar falsificación de datos en los formularios), sino que reducen la puntuación de match y exponen alertas visuales en el panel del rescatista para evaluación manual.
* **Lista Negra (Blacklist):** Bloqueo automático e inviolable basado en correos electrónicos e identificadores (RUT/DNI) para personas con registros de maltrato previos.

### 2. Padrino Virtual (Suscripción Recurrente)
* Permite apadrinar mascotas activas mediante tres niveles de aportes (Bronce, Plata, Oro).
* Integración fluida con simulación de MercadoPago.
* Los padrinos acumulan **Huellas** (puntos) en el Club de Huellas por cada suscripción activa.

### 3. QR Pet Pass y Botón SOS
* **Ficha Médica Digital:** Cada mascota en adopción o adoptada cuenta con un pasaporte público accesible vía código QR.
* **Botón SOS de Geolocalización:** Si alguien encuentra una mascota extraviada, puede escanear su placa QR y reportarla. 
* **Envío con Contingencia:** El reporte SOS solicita la ubicación GPS del navegador. Si el informante rechaza el acceso GPS por privacidad, la app envía un reporte de contingencia con coordenadas `(0,0)` capturando de todas formas un teléfono de contacto y un mensaje de texto libre (ej. *"Está retenido en el local de la esquina"*).

### 4. Seguimiento Post-Adopción y Alertas de IA
* **Diario en 3 Pasos:** Las familias adoptantes envían reportes periódicos (Emoji de estado de ánimo, Comentario de texto y Foto).
* **Alertas Predictivas de Bienestar:** Heurísticas y reglas de texto analizan el estado de adaptación del animal. Diarios con índices críticos gatillan de forma automática alertas en el backoffice de la fundación y activan manuales de adiestramiento de emergencia.
* **Compromiso de Esterilización:** Alerta contractual y de tenencia responsable que notifica si una mascota cumple más de 6 meses desde su adopción definitiva sin registrar su certificado de esterilización clínica.

### 5. Logística Colaborativa (Uber Solidario)
* Módulo donde rescatistas publican solicitudes de transporte de animales o alimentos, y conductores comunitarios registrados pueden aceptarlas, ganando 50 puntos para el Club de Huellas al completarlas.

### 6. Bazar Animal y Club de Huellas
* Canje de Huellas ganadas por realizar buenas acciones en la plataforma (diarios a tiempo, traslados) por cupones de descuento patrocinados por comercios de la red.

### 7. Radar Pet-Friendly y Paseos en Manada
* Mapa interactivo colaborativo de plazas, cafeterías y clínicas Pet-Friendly.
* Coordinación de paseos grupales entre vecinos con un formulario que exige la firma electrónica del adoptante declarando que la mascota cuenta con vacunas al día y no presenta conductas reactivas.

---

## 🚀 Instalación y Configuración Local (WampServer/Local)

1. **Requisitos Previos:** PHP 8.2+, Composer, Node.js y MySQL.
2. **Descarga y dependencias de PHP:**
   ```bash
   composer install
   ```
3. **Dependencias de Frontend:**
   ```bash
   npm install
   ```
4. **Configuración de Variables de Entorno:**
   * Copia `.env.example` a `.env` y configura tus credenciales de base de datos MySQL local.
5. **Migraciones y Semillas (Seeders):**
   * Configura las tablas iniciales e inyecta las mascotas iniciales (**Kira, Milo y Thor**) junto a sus fotos de estudio en alta resolución:
   ```bash
   php artisan migrate:fresh --seed
   ```
6. **Compilación de Assets:**
   * Para desarrollo en tiempo real: `npm run dev`
   * Para compilar la producción: `npm run build`
7. **Iniciar Servidor Local:**
   ```bash
   php artisan serve
   ```
   * Accede a la aplicación en: `http://127.0.0.1:8000`

---

## 🧪 Pruebas Unitarias y de Integración

La plataforma cuenta con un robusto set de pruebas automatizadas que validan la lógica de match, firmas electrónicas, checkout y alertas de geolocalización. 

* Para ejecutar toda la suite de pruebas de PHPUnit:
  ```bash
  php artisan test
  ```

---

## 📦 Despliegue en SiteGround (Plan Económico StartUp)

Dado que los planes económicos de SiteGround tienen límites de disco (10GB) y de procesos simultáneos, el proyecto cuenta con un sistema optimizado de preparación:

1. **Compilación de Producción:** Ejecuta `npm run build` localmente.
2. **Preparar Estructura:** Ejecuta el script de empaquetamiento:
   ```bash
   php prepare_deploy.php
   ```
   * Esto creará un directorio `deploy_siteground/` que separa los archivos core del framework (`proyecto_laravel`, a subir fuera de la raíz pública por seguridad) y la carpeta pública (`public_html`). Modifica automáticamente los enlaces relativos de `public_html/index.php`.
3. **Paquete Listo:** El archivo **`deploy_siteground.zip`** en la raíz del proyecto ya contiene todo este directorio comprimido listo para subir al Administrador de Archivos de SiteGround y descomprimir.
4. **Instrucciones en el Servidor:**
   * Renombra `.env.production` a `.env` dentro de `proyecto_laravel` y rellena las credenciales de la base de datos de producción en SiteGround.
   * Ejecuta `php artisan migrate --force --seed` mediante SSH.
   * Crea el enlace simbólico para imágenes: `ln -s /home/customer/user/proyecto_laravel/storage/app/public /home/customer/user/public_html/storage`.
   * Cachea las configuraciones para optimizar la velocidad: `php artisan config:cache` y `php artisan route:cache`.
