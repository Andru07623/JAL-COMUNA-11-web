<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/utilidades.php';

// Mensajes de resultado (se pasan por la URL tras enviar el formulario)
$ok       = isset($_GET['ok']);
$error    = $_GET['error'] ?? '';
$radicado = $_GET['radicado'] ?? '';

// Para repoblar el formulario si hubo error (guardado en sesión)
session_start();
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
function old($campo, $old) { return isset($old[$campo]) ? e($old[$campo]) : ''; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Certificado de Residencia - JAL Comuna 11</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="./assets/logo.png">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .bg-institucional { background-color: #0033a0 !important; }
        .text-institucional { color: #0033a0 !important; }
        .btn-institucional { background-color: #0033a0; color: #fff; border: none; }
        .btn-institucional:hover { background-color: #001f66; color: #fff; }

       .page-hero {
            background: linear-gradient(rgba(0, 51, 160, 0.3), rgba(0, 31, 102, 0.3)),
                url('./assets/tramites.jpeg');
            background-size: cover;
            background-position: center 40%;
            min-height: 500px;
            display: flex;
            align-items: center;
        }

        .fa-solid, .fas { font-weight: 900 !important; }
        .fa-regular, .far { font-weight: 400 !important; }

        /* ====== NAVBAR ANIMADA ====== */
        .navbar { transition: box-shadow 0.3s ease, padding 0.3s ease, background-color 0.3s ease; }
        .navbar .nav-link { position: relative; transition: color 0.3s ease; }
        .navbar .nav-link:hover, .navbar .nav-link:focus { color: #0033a0 !important; }
        .navbar .nav-link:not(.dropdown-toggle)::after {
            content: ""; position: absolute; left: 0.5rem; right: 0.5rem; bottom: 0.35rem;
            height: 2px; background-color: #0033a0; transform: scaleX(0);
            transform-origin: left center; transition: transform 0.3s ease;
        }
        .navbar .nav-link:not(.dropdown-toggle):hover::after,
        .navbar .nav-link:not(.dropdown-toggle):focus::after { transform: scaleX(1); }
        .navbar .dropdown-item {
            display: flex; align-items: center; gap: 0.65rem; font-weight: 500; color: #334155;
            padding: 0.6rem 0.9rem; border-radius: 0.6rem;
            transition: background-color 0.2s ease, color 0.2s ease, padding-left 0.2s ease;
        }
        .navbar .dropdown-item i { width: 20px; text-align: center; color: #0033a0; font-size: 0.95rem; transition: transform 0.2s ease; }
        .navbar .dropdown-item:hover, .navbar .dropdown-item:focus { background-color: #eef2ff; color: #0033a0; padding-left: 1.2rem; }
        .navbar .dropdown-item:hover i { transform: scale(1.15); }
        @media (min-width: 992px) {
            .navbar .dropdown-menu {
                display: block; opacity: 0; visibility: hidden; transform: translateY(12px);
                transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s; pointer-events: none;
                margin-top: 0.6rem; padding: 0.5rem; min-width: 15rem; border: 1px solid #eef2ff;
                border-radius: 0.9rem; box-shadow: 0 0.75rem 1.75rem rgba(0, 51, 160, 0.15); background-color: #fff;
            }
            .navbar .dropdown-menu::before {
                content: ""; display: block; height: 3px; width: 40px; background-color: #0033a0;
                border-radius: 999px; margin: 0.15rem 0 0.5rem 0.75rem;
            }
            .navbar .dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0); pointer-events: auto; }
        }
        .navbar .btn-institucional { transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease; }
        .navbar .btn-institucional:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 51, 160, 0.3); }

        /* ====== ZONA DE SUBIDA DE ARCHIVOS (estilo recuadro) ====== */
        .upload-box {
            border: 2px dashed #b9c4e6;
            border-radius: 0.8rem;
            background-color: #f6f8ff;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .upload-box:hover { border-color: #0033a0; background-color: #eef2ff; }
        .upload-box i { font-size: 1.8rem; color: #0033a0; }
        .upload-box .upload-text { font-size: 0.9rem; color: #334155; margin-top: 0.4rem; }
        .upload-box .upload-filename { font-size: 0.82rem; color: #0a7d2c; font-weight: 600; margin-top: 0.3rem; word-break: break-all; }
        .upload-box input[type="file"] { display: none; }
        .upload-box.has-file { border-color: #0a7d2c; background-color: #f0fbf3; }
        .upload-box.dragover { border-color: #0033a0; background-color: #e3ebff; transform: scale(1.01); }

        .btn-salud {
            background-color: #0d9488;
            color: #fff;
        }
        .btn-salud:hover {
            background-color: #0f766e;
            color: #fff;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- BARRA DE NAVEGACIÓN -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="../index.html">
                <img src="./assets/logo.png" alt="Logo JAL Comuna 11 Medellín" height="50" width="60">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarJAL" aria-controls="navbarJAL" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarJAL">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="../index.html">Inicio</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">La JAL</a>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="../LaJal.html">¿Qué es la JAL?</a></li>
                            <li><a class="dropdown-item" href="../LaJal.html#funciones">Funciones</a></li>
                            <li><a class="dropdown-item" href="../LaJal.html#normatividad">Normatividad</a></li>
                            <li><a class="dropdown-item" href="../LaJal.html#faq">Preguntas frecuentes</a></li>
                            <li><a class="dropdown-item" href="../LaJal.html#integrantes">Integrantes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Información</a>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="../informacion.html#noticias">Noticias</a></li>
                            <li><a class="dropdown-item" href="../informacion.html#decretos">Decretos</a></li>
                            <li><a class="dropdown-item" href="../informacion.html#pot">POT</a></li>
                            <li><a class="dropdown-item" href="../informacion.html#pdl">Plan de Desarrollo Local</a></li>
                            <li><a class="dropdown-item" href="../informacion.html#sesiones">Sesiones en vivo</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Presupuesto Participativo</a>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="../pp.html#que-es">¿Qué es el P.P?</a></li>
                            <li><a class="dropdown-item" href="../pp.html#fases">¿Cómo funciona?</a></li>
                            <li><a class="dropdown-item" href="../pp.html#votar">¿Cómo participar?</a></li>
                            <li><a class="dropdown-item" href="../pp.html#documentos">Documentos</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-semibold" href="#" id="tramitesDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Trámites
                        </a>
                        <ul class="dropdown-menu shadow border-0" aria-labelledby="tramitesDropdown">
                            <li><a class="dropdown-item" href="index.php">
                                <i class="fa-solid fa-file-signature"></i> Certificado de residencia
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="../convocatorias.html">Convocatorias</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-salud rounded-pill px-4 fw-bold shadow-sm" href="mental.html">
                            <i class="fa-solid fa-heart-pulse me-1"></i>Mesa de Salud Mental
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-institucional rounded-pill px-4 fw-bold shadow-sm" href="../sugerencias.html">Comentarios y Sugerencias</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-grow-1" style="padding-top: 76px;">

        <!-- HERO INTERNO -->
        <section class="page-hero text-white py-5 text-center">
            <div class="container py-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-3">
                        <li class="breadcrumb-item"><a href="../index.html" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Certificado de residencia</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-2">Certificado de residencia</h1>
                <p class="lead text-white-50 mb-0 mx-auto" style="max-width: 650px;">
                    Solicita en línea tu certificado de residencia en la Comuna 11 Laureles-Estadio
                </p>
            </div>
        </section>

        <!-- SECCIÓN: FORMULARIO -->
        <section id="certificado" class="py-5">
            <div class="container py-4">
                <div class="row g-5 justify-content-center">

                    <!-- Columna informativa -->
                    <div class="col-lg-5">
                        <h2 class="fw-bold text-institucional mb-3">¿Cómo funciona?</h2>
                        <div class="bg-institucional rounded-pill mb-4" style="width: 60px; height: 3px;"></div>
                        <p class="text-muted mb-4">
                            Completa el formulario con tus datos y adjunta los documentos de soporte.
                            El equipo de la JAL verificará tu información y, si todo está correcto,
                            recibirás tu certificado de residencia directamente en tu correo electrónico.
                        </p>

                        <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start gap-3">
                                <i class="fa-solid fa-file-pen text-institucional fs-5 mt-1"></i>
                                <span class="text-muted"><strong class="text-dark">Diligencia:</strong> completa tus datos personales y de residencia.</span>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="fa-solid fa-paperclip text-institucional fs-5 mt-1"></i>
                                <span class="text-muted"><strong class="text-dark">Adjunta:</strong> tu documento de identidad y un recibo de EPM reciente.</span>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="fa-solid fa-user-check text-institucional fs-5 mt-1"></i>
                                <span class="text-muted"><strong class="text-dark">Verificación:</strong> revisamos que la dirección coincida con el recibo.</span>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="fa-solid fa-envelope-circle-check text-institucional fs-5 mt-1"></i>
                                <span class="text-muted"><strong class="text-dark">Entrega:</strong> recibes tu certificado por correo (vigencia de 30 días).</span>
                            </li>
                        </ul>

                        <!-- AVISO: alcance del certificado (solo educativo / Sapiencia) -->
                        <div class="card border-0 rounded-3 shadow-sm mb-4" style="border-left: 5px solid #ffc107 !important; background:#fffbea;">
                            <div class="card-body p-4">
                                <h3 class="h6 fw-bold mb-2 d-flex align-items-center gap-2" style="color:#8a6d00;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Importante antes de solicitar
                                </h3>
                                <p class="small text-muted mb-3">
                                    Este certificado se expide <strong class="text-dark">únicamente para fines educativos</strong>
                                    (trámites ante <strong class="text-dark">Sapiencia</strong>, becas y procesos de educación superior).
                                </p>
                                <p class="small text-muted mb-3">
                                    Si necesitas un certificado de residencia para <strong class="text-dark">otros trámites</strong>,
                                    debes solicitarlo directamente ante la Alcaldía de Medellín:
                                </p>
                                <a href="https://www.medellin.gov.co/es/tramites-y-servicios/certificado-de-residencia/"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-institucional fw-bold rounded w-100">
                                    <i class="fa-solid fa-up-right-from-square me-2"></i>Ir al trámite en la Alcaldía
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 bg-light rounded-3 shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="h6 fw-bold mb-3"><i class="fa-solid fa-circle-info text-institucional me-2"></i> Datos de contacto directo</h3>
                                <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                                    <li><i class="fa-solid fa-location-dot text-institucional me-2"></i> Cl. 39d #73-113, Primer Parque de Laureles, Laureles-Estadio (Comuna 11), Medellín.</li>
                                    <li><i class="fa-solid fa-envelope text-institucional me-2"></i> jal.laureles11@gmail.com</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Columna del formulario -->
                    <div class="col-lg-6">
                        <div class="card border-0 rounded-3 shadow-lg p-3">
                            <div class="card-body">
                                <h2 class="h4 fw-bold mb-4 text-dark">Formulario de Solicitud</h2>

                                <?php if ($ok): ?>
                                    <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
                                        <i class="fa-solid fa-circle-check fa-lg mt-1"></i>
                                        <div>
                                            <strong>¡Solicitud recibida!</strong><br>
                                            Tu solicitud N.º <strong><?= e($radicado) ?></strong> fue registrada.
                                            Revisaremos tu información y te responderemos al correo indicado.
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="index.php" class="btn btn-institucional px-4 fw-bold rounded"><i class="fa-solid fa-plus me-1"></i>Hacer otra solicitud</a>
                                    </div>
                                <?php else: ?>

                                    <?php if ($error): ?>
                                        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                                            <i class="fa-solid fa-triangle-exclamation fa-lg mt-1"></i>
                                            <div><?= e($error) ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <form action="enviar.php" method="POST" enctype="multipart/form-data" novalidate>

                                        <div class="mb-3">
                                            <label for="nombre" class="form-label fw-medium">Nombre completo <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <input type="text" id="nombre" name="nombre" required class="form-control" value="<?= old('nombre', $old) ?>">
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-sm-4">
                                                <label for="tipo_doc" class="form-label fw-medium">Tipo documento <span class="text-danger" aria-label="obligatorio">*</span></label>
                                                <select id="tipo_doc" name="tipo_doc" class="form-select" required>
                                                    <?php $td = $old['tipo_doc'] ?? 'CC'; ?>
                                                    <option value="CC" <?= $td==='CC'?'selected':'' ?>>CC</option>
                                                    <option value="TI" <?= $td==='TI'?'selected':'' ?>>TI</option>
                                                    <option value="CE" <?= $td==='CE'?'selected':'' ?>>CE</option>
                                                    <option value="PA" <?= $td==='PA'?'selected':'' ?>>PA</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-8">
                                                <label for="documento" class="form-label fw-medium">Documento <span class="text-danger" aria-label="obligatorio">*</span></label>
                                                <input type="text" id="documento" name="documento" class="form-control" required inputmode="numeric" value="<?= old('documento', $old) ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="celular" class="form-label fw-medium">Teléfono <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <input type="tel" id="celular" name="celular" class="form-control" required value="<?= old('celular', $old) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="correo" class="form-label fw-medium">Correo electrónico <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <input type="email" id="correo" name="correo" required class="form-control" value="<?= old('correo', $old) ?>">
                                            <div class="form-text">Aquí te enviaremos el certificado o la respuesta.</div>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-sm-6">
                                                <label for="barrio" class="form-label fw-medium">Barrio <span class="text-danger" aria-label="obligatorio">*</span></label>
                                                <select id="barrio" name="barrio" class="form-select" required>
                                                    <option value="" <?= empty($old['barrio'])?'selected':'' ?> disabled>Selecciona tu barrio</option>
                                                    <?php foreach ($GLOBALS['BARRIOS_COMUNA11'] as $b): ?>
                                                        <option value="<?= e($b) ?>" <?= (($old['barrio']??'')===$b)?'selected':'' ?>><?= e($b) ?></option>
                                                    <?php endforeach; ?>
                                                    <option value="Otro" <?= (($old['barrio']??'')==='Otro')?'selected':'' ?>>Otro</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="barrio_otro" class="form-label fw-medium">Especifica cuál</label>
                                                <input type="text" id="barrio_otro" name="barrio_otro" class="form-control" value="<?= old('barrio_otro', $old) ?>" disabled>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="direccion" class="form-label fw-medium">Dirección de residencia <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <input type="text" id="direccion" name="direccion" class="form-control" required placeholder="Ej: Carrera ..." value="<?= old('direccion', $old) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="fecha_residencia" class="form-label fw-medium">¿Desde qué fecha resides en la Comuna 11? <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <input type="date" id="fecha_residencia" name="fecha_residencia" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= old('fecha_residencia', $old) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="razon" class="form-label fw-medium">Razón por la que solicitas el certificado <span class="text-danger" aria-label="obligatorio">*</span></label>
                                            <textarea id="razon" name="razon" rows="2" required class="form-control" placeholder="Ej: Trámite ante Sapiencia para solicitud de beca"><?= old('razon', $old) ?></textarea>
                                        </div>

                                        <!-- ZONAS DE SUBIDA estilo dibujo -->
                                        <label class="form-label fw-medium">Documentos de soporte <span class="text-danger" aria-label="obligatorio">*</span></label>
                                        <div class="row g-3 mb-3">
                                            <div class="col-sm-6">
                                                <label class="upload-box d-block" id="box-cedula" for="cedula">
                                                    <i class="fa-solid fa-id-card"></i>
                                                    <div class="upload-text">Documento de identidad<br><small class="text-muted">(ambos lados)</small></div>
                                                    <div class="upload-filename" id="file-cedula"></div>
                                                    <input type="file" id="cedula" name="cedula" required accept=".pdf,.jpg,.jpeg,.png">
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="upload-box d-block" id="box-recibo" for="recibo">
                                                    <i class="fa-solid fa-file-invoice"></i>
                                                    <div class="upload-text">Recibo de EPM<br><small class="text-muted">(servicios públicos)</small></div>
                                                    <div class="upload-filename" id="file-recibo"></div>
                                                    <input type="file" id="recibo" name="recibo" required accept=".pdf,.jpg,.jpeg,.png">
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-text mb-3">Formatos: PDF, JPG o PNG. Máximo 5 MB por archivo.</div>

                                        <!-- Aviso de privacidad (Ley 1581) -->
                                        <div class="p-3 mb-3 rounded-3" style="background:#f1f6fd; border-left:4px solid #0033a0; font-size:.85rem;">
                                            <strong><i class="fa-solid fa-shield-halved me-1 text-institucional"></i> Tratamiento de datos personales</strong>
                                            <p class="mb-0 mt-1 text-muted">
                                                Al enviar este formulario autorizas a la JAL Comuna 11 a tratar tus datos y documentos
                                                con la única finalidad de verificar tu residencia y expedir el certificado, conforme a la
                                                Ley 1581 de 2012. Puedes solicitar su consulta o eliminación a jal.laureles11@gmail.com.
                                            </p>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" name="consentimiento" id="consentimiento" required>
                                            <label class="form-check-label" for="consentimiento">
                                                He leído y acepto el tratamiento de mis datos personales. <span class="text-danger">*</span>
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-institucional w-100 py-2 fw-bold rounded">
                                            <i class="fa-solid fa-paper-plane me-2"></i>Enviar Solicitud
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="bg-dark text-white pt-5 pb-4 border-top border-4 border-primary mt-auto">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-landmark fs-3 text-white"></i>
                        <span class="fw-bold fs-5">JAL Comuna 11</span>
                    </div>
                    <p class="text-secondary small leading-relaxed">
                        Sitio web oficial de la Junta Administradora Local de la Comuna 11. Trabajando por la transparencia, la gestión eficiente y el desarrollo de nuestro territorio.
                    </p>
                </div>
                <div class="col-md-4">
                    <h3 class="h6 fw-bold mb-3 text-uppercase border-bottom border-secondary pb-2">Enlaces Institucionales</h3>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
                        <li><a href="https://www.medellin.gov.co" target="_blank" class="text-secondary text-decoration-none">Alcaldía de Medellín</a></li>
                        <li><a href="https://www.concejodemedellin.gov.co" target="_blank" class="text-secondary text-decoration-none">Concejo de Medellín</a></li>
                        <li><a href="../LaJal.html#integrantes" class="text-secondary text-decoration-none">Directorio de Ediles</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3 class="h6 fw-bold mb-3 text-uppercase border-bottom border-secondary pb-2">Atención al Ciudadano</h3>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
                        <li><i class="fa-solid fa-map-location-dot me-2"></i>Cl. 39d #73-113, Primer Parque de Laureles, Laureles-Estadio (Comuna 11), Medellín.</li>
                        <li><i class="fa-solid fa-envelope me-2"></i> jal.laureles11@gmail.com</li>
                    </ul>
                    <h3 class="h6 fw-bold mb-3 mt-4 text-uppercase border-bottom border-secondary pb-2">Síguenos</h3>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
                        <li><a href="https://chat.whatsapp.com/KkKgIqyjIi62TdYUuVUghY" target="_blank" rel="noopener" class="text-secondary text-decoration-none"><i class="fa-brands fa-whatsapp me-2"></i> Canal de WhatsApp</a></li>
                        <li><a href="https://www.instagram.com/jal11med/" target="_blank" class="text-secondary text-decoration-none"><i class="fa-brands fa-instagram me-2"></i> Instagram</a></li>
                        <li><a href="https://www.facebook.com/p/Jal-Comuna-Once-Medell%C3%ADn-100092500651921/" target="_blank" class="text-secondary text-decoration-none"><i class="fa-brands fa-facebook me-2"></i> Facebook</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary pt-3 text-center text-secondary small">
                <p class="mb-0">&copy; 2026 JAL Comuna 11 - Medellín. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- SCRIPTS DE BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Barrio "Otro"
        const barrio = document.getElementById('barrio');
        const otroTexto = document.getElementById('barrio_otro');
        if (barrio) {
            function toggleOtro() {
                if (barrio.value === 'Otro') { otroTexto.disabled = false; otroTexto.required = true; }
                else { otroTexto.disabled = true; otroTexto.required = false; otroTexto.value = ''; }
            }
            barrio.addEventListener('change', toggleOtro);
            toggleOtro();
        }

        // Zonas de subida: clic + arrastrar y soltar (drag & drop)
        function conectarUpload(inputId, boxId, fileId) {
            const input = document.getElementById(inputId);
            const box   = document.getElementById(boxId);
            const file  = document.getElementById(fileId);
            if (!input || !box) return;

            // Tipos permitidos (igual que el backend)
            const permitidos = ['application/pdf', 'image/jpeg', 'image/png'];
            const MAX = 5 * 1024 * 1024; // 5 MB

            function mostrar() {
                if (input.files.length > 0) {
                    file.textContent = '✓ ' + input.files[0].name;
                    box.classList.add('has-file');
                } else {
                    file.textContent = '';
                    box.classList.remove('has-file');
                }
            }

            function validar(archivo) {
                if (!permitidos.includes(archivo.type)) {
                    alert('El archivo "' + archivo.name + '" debe ser PDF, JPG o PNG.');
                    return false;
                }
                if (archivo.size > MAX) {
                    alert('El archivo "' + archivo.name + '" supera los 5 MB.');
                    return false;
                }
                return true;
            }

            // Clic normal → selector de archivos (ya lo hace el <label>), solo refrescar al elegir
            input.addEventListener('change', mostrar);

            // Evitar que el navegador abra el archivo si se suelta fuera
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (ev) {
                box.addEventListener(ev, function (e) { e.preventDefault(); e.stopPropagation(); });
            });

            // Resaltar la caja mientras se arrastra encima
            ['dragenter', 'dragover'].forEach(function (ev) {
                box.addEventListener(ev, function () { box.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                box.addEventListener(ev, function () { box.classList.remove('dragover'); });
            });

            // Soltar el archivo → asignarlo al input
            box.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (!dt || dt.files.length === 0) return;
                const archivo = dt.files[0]; // tomamos solo el primero
                if (!validar(archivo)) return;

                // Transferir el archivo soltado al <input type=file>
                const data = new DataTransfer();
                data.items.add(archivo);
                input.files = data.files;
                mostrar();
            });
        }
        conectarUpload('cedula', 'box-cedula', 'file-cedula');
        conectarUpload('recibo', 'box-recibo', 'file-recibo');
    </script>
</body>
</html>
