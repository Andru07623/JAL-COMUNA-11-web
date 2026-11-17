# Plan de trabajo — Sitio web JAL Comuna 11 (Medellín)

> Plan derivado de la reunión del usuario con su directora (notas manuscritas del
> usuario, transcritas aquí). Es la lista maestra de lo que falta en el sitio.
> Se irá marcando a medida que se complete. Última actualización: sesión 1 (3-nov-2026).
>
> **Leyenda:** `[ ]` pendiente · `[~]` en progreso · `[x]` hecho · `(?)` por confirmar con el usuario.

---

## ✅ Sesión 1 — COMPLETADA (3-nov-2026)
Pulido del sitio estático. Todo entregado como código en el chat (el usuario lo aplica
en su copia local). Lo hecho:
- [x] Creada `transparencia.html` (hero interno + pestañas laterales, mismo estilo que
      `Significadojal.html`) con 3 secciones: **Noticias**, **Decretos** y
      **Sesiones en vivo** (bloque "Próximamente" + iframe de YouTube comentado, listo
      para activar cuando exista el canal).
- [x] Desplegable "Transparencia" del navbar actualizado en las 4 páginas →
      Noticias / Decretos / Sesiones en vivo (antes era Presupuesto + Normatividad).
- [x] **Favicon** con `IMAGES/logo.png` en las 4 páginas.
- [x] **Mapa real** de Google Maps (embed sin API key) en el contacto de `index.html`.
- [x] **Font Awesome unificado**: jsDelivr `@fortawesome/fontawesome-free@6.5.2` +
      regla `font-weight:900` para `.fa-solid` en todas las páginas.
- [x] **Dirección real** de la sede en footers y sección de contacto.
- [x] **Redes sociales** (Instagram + Facebook, `fa-brands`) en el footer (lista) y en
      "Contáctanos" de `index.html` (íconos circulares, con `text-decoration-none`).
- [x] `ROADMAP.md` actualizado (Sesión 1 marcada como completada).

> Pendiente del usuario: reemplazar los `href="#"` de Instagram/Facebook por las URLs
> reales cuando las tenga.

---

## Cómo trabajar este proyecto (recordatorio)
- Proyecto REAL de servicio social para la JAL de la Comuna 11, Medellín.
- Stack: HTML + CSS + Bootstrap 5 + Font Awesome 6. Se hospeda en InfinityFree.
- Mantener SIMPLE y de bajo mantenimiento. Evitar bases de datos (MySQL) salvo que
  una función no pueda funcionar sin ellas (y avisar primero el costo de mantenimiento).
- Manejo de datos de ciudadanos → respetar Ley 1581 de 2012 (Habeas Data): aviso de
  privacidad y consentimiento.
- NO editar los archivos del proyecto directamente: el usuario mantiene su copia local.
  Entregar el código en el chat y explicar qué cambiar/reemplazar en cada archivo.
- Archivos del sitio: `index.html`, `Significadojal.html`, `pqrs.html`,
  `transparencia.html`, `ROADMAP.md`, `IMAGES/logo.png`.
- Dirección de la sede: Cl. 39d #73-113, Primer Parque de Laureles, Laureles-Estadio
  (Comuna 11), Medellín.

---

## 1. Navegación y contacto
- [x] Agregar **Canal de WhatsApp** (enlace / botón de contacto).
- [x] **Logo JAL** (reemplazar placeholders por el logo real donde falte).

## 2. Sección "La JAL" (Significadojal.html)
- [x] Crear parte **Normatividad** dentro de "La JAL".
- [x] Crear **FAQ** (preguntas frecuentes) dentro de "La JAL":
    - [x] Explicar la **diferencia entre JAL y JAC**.
    - [x] Agregar más preguntas.
- [ ] **Integrantes:** cuadrar el organigrama; fotos de los ediles y sus datos
      (**son 8 ediles**).

## 3. Inicio (index.html)
- [x] **Carrusel** en el banner de inicio (hero).
- [ ] **Actualidad y Boletines:** cuadrar fotos y el botón "Leer más".

## 4. PQRS (pqrs.html) — "Cambiar todo PQRS"
- [x] Renombrar "PQRS" por un sinónimo. Motivo: la directora NO quiere llamarlo "PQRS"
      porque ese término la obliga legalmente a cumplir todo el trámite/tiempos formales
      de una PQRS. Se busca un nombre más general (ej. "Contáctanos" / "Atención al
      ciudadano" / "Solicitudes"). (?) Palabra exacta AÚN SIN DEFINIR por el usuario.
- [x] Agregar **campo "Barrio"** al formulario.
- [x] **Quitar** el campo "Tipo de solicitud".

## 5. Transparencia (transparencia.html)
- [ ] **Noticias y Boletines:** mismo tratamiento que "Actualidad y Boletines"
      (cuadrar fotos y botón "Leer más").
- [ ] **Decretos:** agregar los archivos (PDF) reales.
- [ ] **Sesiones en vivo / Podcast:** sacar fotos y redes para el podcast; va en
      "Sesión de la JAL" y cambiar el texto. (Relacionado con el espacio de YouTube
      "Próximamente" ya dejado.)

## 6. Contenido institucional (ver dónde colocar)
- [x] **POT** (Plan de Ordenamiento Territorial) y **Plan de Desarrollo Local**
      → decidir en qué sección ubicarlos.
- [x] Label **P.P** (**Presupuesto Participativo**) → agregar todo lo relacionado:
      link, definición, etc.
- [x] Label **Convocatorias** → estilo Sapiencia; agregar las convocatorias activas.

## 7. Formulario Certificado de Residencia (grande / backend)
- [ ] Formulario **funcional** de Certificado de Residencia.
    - [ ] Adjuntar **recibo EPM** y **CC** (cédula).
    - [ ] Alcance confirmado: el certificado aplica **SOLO para trámites de Sapiencia y
          educación superior** (becas/estudios). No es un certificado de residencia de
          uso general.
- Nota: esta es la feature más compleja; probablemente requiera backend (PHP en
  InfinityFree) y cuidado especial con Habeas Data.

---

## Dudas resueltas (sesión 1)
1. "PQRS → sinónimo": la directora NO quiere el término "PQRS" por la carga legal que
   implica; se cambiará por un nombre más general. Palabra exacta AÚN por definir.
2. "P.P" = **Presupuesto Participativo** (confirmado).
3. Certificado de residencia: **solo para trámites de Sapiencia y educación superior**
   (confirmado).
4. "POT" = **Plan de Ordenamiento Territorial** (confirmado).

---

> ℹ️ Nota: este archivo (`PLAN.md`) es una **copia visible** del plan maestro que Kiro
> guarda internamente en `.kiro/steering/plan-jal.md`. Si editas este, avísale a Kiro
> para mantener ambos sincronizados.
