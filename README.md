# Sitio web — JAL Comuna 11 (Laureles-Estadio, Medellín)

Sitio web oficial de la **Junta Administradora Local (JAL) de la Comuna 11 — Laureles-Estadio**, en Medellín, Colombia.

🔗 **En línea:** [jalcomuna11.com](https://jalcomuna11.com)

---

## ¿Qué hace el sitio?

Es el portal informativo y de servicios de la JAL para los habitantes de la Comuna 11. Incluye:

- **Información institucional** — qué es la JAL, los ediles, normatividad, preguntas frecuentes.
- **Noticias y boletines** — publicaciones de la Junta.
- **Presupuesto Participativo (P.P.)** — explicación del proceso y cómo participar.
- **Convocatorias** — oportunidades y procesos abiertos a la comunidad.
- **Mesa de Salud Mental** — información del programa.
- **Comentarios y sugerencias** — formulario de contacto que envía los mensajes por correo a la JAL y manda una confirmación automática al ciudadano (vía SMTP, sin base de datos).
- **Certificado de Residencia** — sistema para que un ciudadano solicite su certificado en línea (adjuntando cédula y recibo EPM), con un panel administrativo donde un funcionario revisa, aprueba o rechaza la solicitud y el sistema genera el PDF del certificado y lo envía por correo.

---

## Tecnologías

| Área | Tecnología |
|------|-----------|
| Frontend | HTML5, CSS3, **Bootstrap 5**, JavaScript, Font Awesome |
| Backend | **PHP** |
| Base de datos | **MySQL** (solo para el sistema de certificados de residencia) |
| Correo | SMTP vía [Brevo](https://www.brevo.com/) + [PHPMailer](https://github.com/PHPMailer/PHPMailer) |
| PDF | [FPDF](http://www.fpdf.org/) (generación del certificado) |
| Hosting | InfinityFree (PHP + MySQL) |

> El sitio informativo (páginas `.html`) y el formulario de sugerencias (`enviar.php`) funcionan **sin base de datos**. Solo el módulo de **certificados de residencia** (`/certificados`) usa MySQL.

---

## Estructura del proyecto

```
.
├── index.html, LaJal.html, informacion.html, ...   # Páginas del sitio
├── enviar.php                 # Formulario de comentarios/sugerencias -> correo
├── noticias.js / noticias-render.js                # Render de noticias
├── config.example.php         # Plantilla de credenciales (copiar a config.php)
├── ASSETS/                     # Imágenes, videos, banners
├── PLANES/                     # PLAN.md y ROADMAP.md
├── PHPMailer/                  # Librería PHPMailer (envío de correo)
└── certificados/              # Sistema de Certificado de Residencia
    ├── index.php               # Formulario público de solicitud
    ├── enviar.php              # Recibe la solicitud y guarda adjuntos
    ├── config.example.php      # Plantilla de config (BD + SMTP)
    ├── admin/                  # Panel del funcionario (login, aprobar/rechazar)
    ├── lib/                    # FPDF, PHPMailer y lógica propia
    ├── sql/estructura.sql      # Estructura de la base de datos (sin datos)
    ├── uploads/               # Adjuntos de ciudadanos (vacía; no se versiona)
    └── certificados_generados/ # PDFs generados (vacía; no se versiona)
```

---

## Cómo correrlo en local

Necesitas un entorno con **PHP + MySQL** (por ejemplo [XAMPP](https://www.apachefriends.org/)).

### 1. Clonar el repositorio
```bash
git clone https://github.com/Andru07623/JAL-COMUNA-11-web.git
```
Copia la carpeta dentro de `htdocs` (XAMPP) o la raíz web de tu servidor.

### 2. El sitio informativo
Abre `index.html` en el navegador. No requiere configuración.

### 3. El formulario de sugerencias (`enviar.php`)
1. Copia `config.example.php` y renómbralo a `config.php`.
2. Rellena tus credenciales SMTP de Brevo en `config.php`.
3. Sírvelo con PHP (XAMPP, o `php -S localhost:8000`) y abre `sugerencias.html`.

### 4. El sistema de certificados (`/certificados`)
1. Crea una base de datos MySQL (p. ej. `jal_certificados`).
2. Importa la estructura:
   ```bash
   mysql -u root jal_certificados < certificados/sql/estructura.sql
   ```
3. Copia `certificados/config.example.php` a `certificados/config.php` y rellena los datos de la base de datos y el SMTP.
4. Crea el primer usuario funcionario (ver nota en `estructura.sql`), luego borra ese script.
5. Asegúrate de que `certificados/uploads` y `certificados/certificados_generados` tengan permisos de escritura.
6. Abre `certificados/index.php` (formulario público) y `certificados/admin/login.php` (panel del funcionario).

---

## Configuración y seguridad

- Las credenciales reales **nunca** van en el repositorio. Se guardan en `config.php` (ignorado por git); solo se versiona la plantilla `config.example.php` con valores de ejemplo.
- El `.gitignore` excluye `config.php`, los adjuntos de ciudadanos (`certificados/uploads`), los PDFs generados (`certificados/certificados_generados`) y los logs.
- Los datos personales de los ciudadanos (cédulas, recibos EPM, certificados) se tratan conforme a la **Ley 1581 de 2012 (Habeas Data)** de Colombia y no se publican.

---

## Licencia

Proyecto de servicio social para la comunidad de la Comuna 11. Uso institucional de la JAL Comuna 11.
