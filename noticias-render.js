/* =========================================================================
   MOTOR DE NOTICIAS - NO EDITAR
   Lee el arreglo NOTICIAS de noticias.js y dibuja las tarjetas.
   - En index.html  (contenedor #noticias-inicio): muestra las 3 más recientes (resumen).
   - En informacion.html (contenedor #noticias-info): muestra todas (con "Leer más").
   ========================================================================= */
document.addEventListener("DOMContentLoaded", function () {
  if (typeof NOTICIAS === "undefined") {
    console.error("No se encontró noticias.js. Verifica que esté cargado ANTES de este archivo.");
    return;
  }

  // Dibuja el bloque de imagen o video según el tipo
  function media(n, alto) {
    if (n.tipo === "video") {
      return '<video src="' + n.medio + '" controls autoplay muted loop ' +
             'class="card-img-top" style="height:' + alto + '; object-fit:cover;"></video>';
    }
    return '<img src="' + n.medio + '" class="card-img-top" style="height:' + alto +
           '; object-fit:cover;" alt="' + n.titulo + '">';
  }

  // ---------- INDEX (resumen de 3) ----------
  var cInicio = document.getElementById("noticias-inicio");
  if (cInicio) {
    cInicio.innerHTML = NOTICIAS.slice(0, 3).map(function (n) {
      return '' +
        '<div class="col-md-4">' +
          '<div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden card-hover">' +
            media(n, "300px") +
            '<div class="card-body p-4">' +
              '<span class="text-uppercase text-institucional fw-bold fs-7 mb-2 d-block">' + n.categoria + '</span>' +
              '<h3 class="h5 fw-bold mb-3"><a href="./informacion.html#' + n.id + '" class="text-dark text-decoration-none">' + n.titulo + '</a></h3>' +
              '<p class="card-text text-muted small">' + n.resumen + '</p>' +
            '</div>' +
            '<div class="card-footer bg-transparent border-0 px-4 pb-4 d-flex justify-content-between align-items-center text-muted small">' +
              '<span><i class="fa-regular fa-calendar me-1"></i> ' + n.fecha + '</span>' +
              '<a href="./informacion.html#' + n.id + '" class="text-institucional fw-bold text-decoration-none">Leer más</a>' +
            '</div>' +
          '</div>' +
        '</div>';
    }).join("");
  }

  // ---------- INFORMACION (todas, con Leer más) ----------
  var cInfo = document.getElementById("noticias-info");
  if (cInfo) {
    cInfo.innerHTML = NOTICIAS.map(function (n) {
      return '' +
        '<div class="col-md-6">' +
          '<div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden card-hover" id="' + n.id + '">' +
            media(n, "300px") +
            '<div class="card-body p-4">' +
              '<span class="text-uppercase text-institucional fw-bold mb-2 d-block" style="font-size: .72rem;">' + n.categoria + '</span>' +
              '<h3 class="h6 fw-bold mb-2"><a href="#" class="text-dark text-decoration-none">' + n.titulo + '</a></h3>' +
              '<p class="card-text text-muted small mb-0">' + n.resumen +
                ' <span class="texto-extra"> ' + n.textoCompleto + '</span>' +
              '</p>' +
            '</div>' +
            '<div class="card-footer bg-transparent border-0 px-4 pb-4 d-flex justify-content-between align-items-center text-muted small">' +
              '<span><i class="fa-regular fa-calendar me-1"></i> ' + n.fecha + '</span>' +
              '<a href="#" class="btn-leermas" onclick="toggleLeerMas(event, this)">Leer más</a>' +
            '</div>' +
          '</div>' +
        '</div>';
    }).join("");
  }
});
