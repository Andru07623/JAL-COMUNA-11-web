/* =========================================================================
   NOTICIAS DE LA JAL COMUNA 11
   -------------------------------------------------------------------------
   👉 CÓMO AGREGAR UNA NOTICIA NUEVA:
      1. Copia un bloque completo desde la llave {  hasta la llave }
      2. Pégalo ARRIBA de los demás (la primera es la más reciente).
      3. Acuérdate de dejar una COMA después de la llave } de cada noticia.
      4. Cambia los textos (entre comillas) por los de tu noticia.
      5. Sube la foto/video a la carpeta ASSETS y pon su ruta en "medio".

   ⚠️ REGLAS IMPORTANTES (para no romper la página):
      - NO borres las llaves { } ni los corchetes [ ]
      - Cada dato va entre "comillas dobles"
      - Si tu texto lleva comillas, usa comillas simples ' adentro
      - "tipo" solo puede ser:  "imagen"  o  "video"
   ========================================================================= */

const NOTICIAS = [

  {
    id: "noticia1",
    categoria: "Territorio",
    titulo: "Escuela de Barrismo: arte, cultura y trabajo comunitario en la Comuna 11",
    fecha: "01 Nov 2026",
    tipo: "video",
    medio: "./ASSETS/VIDEOS/Videobarrismo.mp4",
    resumen: "Estuvimos compartiendo con la Escuela de Barrismo de nuestra Comuna 11, viendo de cerca cómo transforman los espacios con su arte, su cultura y su trabajo comunitario.",
    textoCompleto: "Creemos en menos estigmatización y más apoyo real para quienes construyen barrio todos los días, aportando desde sus talentos a la convivencia y a la identidad de nuestro territorio. Los invitamos a ver este video y a conocer más de cerca esta gran labor que fortalece el tejido social de Laureles-Estadio. ¡Seguimos sumando por Laureles-Estadio!"
  },

  {
    id: "noticia2",
    categoria: "Sesiones",
    titulo: "Mesa de Salud Mental: un espacio para cuidarnos entre todos",
    fecha: "15 Oct 2026",
    tipo: "imagen",
    medio: "./ASSETS/IMAGES/NOTICIAS/Saludmental.jpeg",
    resumen: "Con gran éxito se llevó a cabo la Mesa de Salud Mental en nuestra Comuna 11, un encuentro que reunió a la comunidad, profesionales y líderes en torno al bienestar emocional de todos.",
    textoCompleto: "Durante la jornada se abrieron espacios de diálogo, escucha y reflexión, donde los asistentes compartieron experiencias, inquietudes y propuestas para fortalecer el acompañamiento en salud mental dentro del territorio. El resultado fue excelente: una comunidad más unida, consciente y comprometida con cuidar de sí misma y de los demás. Estos espacios reafirman que hablar de salud mental también es construir barrio. ¡Gracias a todas las personas que participaron y aportaron a esta valiosa jornada. Seguimos sumando por Laureles-Estadio!"
  },

  {
    id: "noticia3",
    categoria: "Comunidad",
    titulo: "Así se vivió la Rendición de Cuentas 2025",
    fecha: "26 Mar 2026",
    tipo: "imagen",
    medio: "./ASSETS/IMAGES/NOTICIAS/rendincioncuentas.jpg",
    resumen: "El pasado jueves 26 de marzo se llevó a cabo nuestra Rendición de Cuentas 2025, un espacio de transparencia y diálogo abierto con la comunidad de la Comuna 11.",
    textoCompleto: "Durante la jornada compartimos los avances, logros y gestiones realizadas, reafirmando nuestro compromiso con una administración cercana, responsable y al servicio del territorio. Agradecemos a todos los vecinos y vecinas que nos acompañaron, pues su participación es la que fortalece la confianza y el trabajo conjunto. ¡Seguimos sumando por Laureles-Estadio!"
  },

  {
    id: "noticia4",
    categoria: "Gestión",
    titulo: "Primera Feria de Emprendimiento: talento y oportunidades en la Comuna 11",
    fecha: "02 Oct 2026",
    tipo: "imagen",
    medio: "./ASSETS/IMAGES/NOTICIAS/feriaemprendimiento.jpg",
    resumen: "Con gran éxito se realizó la Primera Feria de Emprendimiento de nuestra Comuna 11, un espacio creado para visibilizar, impulsar y celebrar el talento de los emprendedores de Laureles-Estadio.",
    textoCompleto: "Durante la jornada, vecinos y vecinas tuvieron la oportunidad de dar a conocer sus productos, servicios e ideas de negocio, conectando con la comunidad y abriendo nuevas oportunidades de crecimiento. El ambiente estuvo lleno de creatividad, esfuerzo y espíritu emprendedor. Este tipo de encuentros fortalecen la economía local, promueven el trabajo colaborativo y reafirman que en nuestra comuna hay un enorme potencial que merece ser apoyado e impulsado. Agradecemos a todos los emprendedores que participaron y a la comunidad que nos acompañó en esta primera edición. ¡Seguimos sumando por Laureles-Estadio!"
  }

];
