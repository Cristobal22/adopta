/**
 * Utilidades geográficas y de validación para Chile.
 */

export const chileRegions = [
  {
    name: "Arica y Parinacota",
    comunas: ["Arica", "Camarones", "Putre", "General Lagos"]
  },
  {
    name: "Tarapacá",
    comunas: ["Iquique", "Alto Hospicio", "Pozo Almonte", "Camiña", "Colchane", "Huara", "Pica"]
  },
  {
    name: "Antofagasta",
    comunas: ["Antofagasta", "Mejillones", "Sierra Gorda", "Taltal", "Calama", "Ollagüe", "San Pedro de Atacama", "Tocopilla", "María Elena"]
  },
  {
    name: "Atacama",
    comunas: ["Copiapó", "Caldera", "Tierra Amarilla", "Chañaral", "Diego de Almagro", "Vallenar", "Alto del Carmen", "Freirina", "Huasco"]
  },
  {
    name: "Coquimbo",
    comunas: ["La Serena", "Coquimbo", "Andacollo", "La Higuera", "Paiguano", "Vicuña", "Illapel", "Canela", "Los Vilos", "Salamanca", "Ovalle", "Combarbalá", "Monte Patria", "Punitaqui", "Río Hurtado"]
  },
  {
    name: "Valparaíso",
    comunas: [
      "Valparaíso", "Casablanca", "Concón", "Juan Fernández", "Puchuncaví", "Quintero", "Viña del Mar", 
      "Isla de Pascua", "Los Andes", "Calle Larga", "Rinconada", "San Esteban", "La Ligua", "Cabildo", 
      "Papudo", "Petorca", "Zapallar", "Quillota", "Calera", "Hijuelas", "La Cruz", "Nogales", "San Antonio", 
      "Algarrobo", "Cartagena", "El Quisco", "El Tabo", "Santo Domingo", "San Felipe", "Catemu", "Llaillay", 
      "Panquehue", "Putaendo", "Santa María", "Quilpué", "Villa Alemana"
    ]
  },
  {
    name: "Metropolitana de Santiago",
    comunas: [
      "Santiago", "Cerrillos", "Cerro Navia", "Conchalí", "El Bosque", "Estación Central", "Huechuraba", 
      "Independencia", "La Cisterna", "La Florida", "La Granja", "La Pintana", "La Reina", "Las Condes", 
      "Lo Barnechea", "Lo Espejo", "Lo Prado", "Macul", "Maipú", "Ñuñoa", "Pedro Aguirre Cerda", "Peñalolén", 
      "Providencia", "Pudahuel", "Quilicura", "Quinta Normal", "Recoleta", "Renca", "San Joaquín", "San Miguel", 
      "San Ramón", "Vitacura", "Puente Alto", "Pirque", "San José de Maipo", "San Bernardo", "Calera de Tango", 
      "Buin", "Paine", "Melipilla", "Alhué", "Curacaví", "María Pinto", "San Pedro", "Talagante", "El Monte", 
      "Isla de Maipo", "Padre Hurtado", "Peñaflor", "Colina", "Lampa", "Til Til"
    ]
  },
  {
    name: "O'Higgins",
    comunas: [
      "Rancagua", "Codegua", "Coinco", "Coltauco", "Doñihue", "Graneros", "Las Cabras", "Machalí", "Malloa", 
      "Mostazal", "Olivar", "Peumo", "Pichidegua", "Quinta de Tilcoco", "Rengo", "Requínoa", "San Vicente", 
      "Pichilemu", "La Estrella", "Litueche", "Navidad", "Marchihue", "Paredones", "San Fernando", "Chépica", 
      "Chimbarongo", "Lolol", "Nancagua", "Palmilla", "Peralillo", "Placilla", "Pumanque", "Santa Cruz"
    ]
  },
  {
    name: "Maule",
    comunas: [
      "Talca", "Constitución", "Curepto", "Empedrado", "Maule", "Pelarco", "Pencahue", "Río Claro", 
      "San Clemente", "San Rafael", "Cauquenes", "Chanco", "Pelluhue", "Curicó", "Hualañé", "Licantén", 
      "Molina", "Rauco", "Romeral", "Sagrada Familia", "Teno", "Vichuquén", "Linares", "Colbún", "Longaví", 
      "Parral", "Retiro", "San Javier", "Villa Alegre", "Yerbas Buenas"
    ]
  },
  {
    name: "Ñuble",
    comunas: [
      "Chillán", "Bulnes", "Cobquecura", "Coelemu", "Coihueco", "El Carmen", "Ninhue", "Ñiquén", 
      "Pemuco", "Pinto", "Portezuelo", "Quirihue", "Ránquil", "San Carlos", "San Fabián", "San Ignacio", 
      "San Nicolás", "Treguaco", "Yungay"
    ]
  },
  {
    name: "Biobío",
    comunas: [
      "Concepción", "Coronel", "Chiguayante", "Florida", "Hualpén", "Hualqui", "Lota", "Penco", 
      "San Pedro de la Paz", "Talcahuano", "Tomé", "Los Ángeles", "Antuco", "Cabrero", "Laja", "Mulchén", 
      "Nacimiento", "Negrete", "Quilaco", "Quilleco", "San Rosendo", "Santa Bárbara", "Tucapel", "Yumbel", 
      "Alto Biobío", "Lebu", "Arauco", "Cañete", "Contulmo", "Curanilahue", "Los Álamos", "Tirúa"
    ]
  },
  {
    name: "Araucanía",
    comunas: [
      "Temuco", "Carahue", "Cholchol", "Cunco", "Curarrehue", "Freire", "Galvarino", "Gorbea", "Lautaro", 
      "Loncoche", "Melipeuco", "Nueva Imperial", "Padre Las Casas", "Perquenco", "Pitrufquén", "Pucón", 
      "Saavedra", "Teodoro Schmidt", "Toltén", "Vilcún", "Villarrica", "Angol", "Collipulli", "Curacautín", 
      "Ercilla", "Lonquimay", "Los Sauces", "Lumaco", "Purén", "Renaico", "Traiguén", "Victoria"
    ]
  },
  {
    name: "Los Ríos",
    comunas: ["Valdivia", "Corral", "Lanco", "Los Lagos", "Máfil", "Mariquina", "Paillaco", "Panguipulli", "La Unión", "Futrono", "Lago Ranco", "Río Bueno"]
  },
  {
    name: "Los Lagos",
    comunas: [
      "Puerto Montt", "Calbuco", "Cochamó", "Fresia", "Frutillar", "Los Muermos", "Llanquihue", "Maullín", 
      "Puerto Varas", "Castro", "Ancud", "Chonchi", "Curaco de Vélez", "Dalcahue", "Puqueldón", "Queilén", 
      "Quellón", "Quemchi", "Quinchao", "Osorno", "Puerto Octay", "Purranque", "Puyehue", "Río Negro", 
      "San Juan de la Costa", "San Pablo", "Chaitén", "Futaleufú", "Hualaihué", "Palena"
    ]
  },
  {
    name: "Aysén",
    comunas: ["Coyhaique", "Lago Verde", "Aysén", "Cisnes", "Guaitecas", "Cochrane", "O'Higgins", "Tortel", "Chile Chico", "Río Ibáñez"]
  },
  {
    name: "Magallanes",
    comunas: ["Punta Arenas", "Laguna Blanca", "Río Verde", "San Gregorio", "Cabo de Hornos", "Antártica", "Porvenir", "Primavera", "Timaukel", "Natales", "Torres del Paine"]
  }
];

/**
 * Valida un RUT chileno utilizando el algoritmo Módulo 11.
 * Soporta formatos con o sin puntos y guion.
 * @param {string} rut - El RUT a validar
 * @returns {boolean}
 */
export function validateRut(rut) {
  if (!rut || typeof rut !== 'string') return false;

  // Limpiar caracteres no permitidos (dejar sólo números y K/k)
  const cleanRut = rut.replace(/[^0-9kK]/g, '');

  if (cleanRut.length < 8 || cleanRut.length > 9) return false;

  const body = cleanRut.slice(0, -1);
  const dv = cleanRut.slice(-1).toUpperCase();

  // Calcular checksum
  let sum = 0;
  let multiplier = 2;

  for (let i = body.length - 1; i >= 0; i--) {
    sum += parseInt(body[i], 10) * multiplier;
    multiplier = multiplier === 7 ? 2 : multiplier + 1;
  }

  const remainder = sum % 11;
  let expectedDv = 11 - remainder;

  if (expectedDv === 11) {
    expectedDv = '0';
  } else if (expectedDv === 10) {
    expectedDv = 'K';
  } else {
    expectedDv = expectedDv.toString();
  }

  return expectedDv === dv;
}

/**
 * Da formato de RUT chileno (XX.XXX.XXX-X) a un string.
 * @param {string} rut - El RUT crudo a formatear
 * @returns {string} El RUT formateado o el original si está vacío
 */
export function formatRut(rut) {
  if (!rut) return '';
  
  // Limpiar caracteres
  let value = rut.replace(/[^0-9kK]/g, '');
  if (value.length === 0) return '';
  
  value = value.toUpperCase();
  
  if (value.length === 1) return value;
  
  const dv = value.slice(-1);
  const body = value.slice(0, -1);
  
  // Formatear cuerpo con puntos
  let formattedBody = '';
  let count = 0;
  for (let i = body.length - 1; i >= 0; i--) {
    formattedBody = body[i] + formattedBody;
    count++;
    if (count % 3 === 0 && i !== 0) {
      formattedBody = '.' + formattedBody;
    }
  }
  
  return `${formattedBody}-${dv}`;
}
