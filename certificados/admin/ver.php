<?php
/**
 * ============================================================
 *  DETALLE DE UNA SOLICITUD — Fase 4
 * ============================================================
 *  Muestra todos los datos, los adjuntos (cédula + recibo) y,
 *  si está pendiente, los formularios de aprobar / rechazar.
 * ============================================================
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/utilidades.php';
requerir_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: dashboard.php'); exit; }

$stmt = db()->prepare('SELECT * FROM solicitudes WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();
if (!$s) { header('Location: dashboard.php'); exit; }

// Datos del funcionario que atendió (si aplica)
$atendio = null;
if (!empty($s['atendido_por'])) {
    $q = db()->prepare('SELECT nombre FROM usuarios WHERE id = :id');
    $q->execute([':id' => $s['atendido_por']]);
    $atendio = $q->fetchColumn();
}

$ok      = $_GET['ok'] ?? '';
$error   = $_GET['error'] ?? '';
$barrio_mostrar = $s['barrio'] === 'Otro' ? $s['barrio_otro'] : $s['barrio'];
$radicado = $s['consecutivo'] ?: 'SOL-' . str_pad($s['id'], 5, '0', STR_PAD_LEFT);

function fila($label, $valor, $icono = '') {
    $ic = $icono ? '<i class="fa-solid ' . $icono . ' text-primary me-2"></i>' : '';
    echo '<div class="col-md-6 mb-3"><div class="text-muted small">' . $ic . e($label) . '</div>'
       . '<div class="fw-semibold">' . e($valor) . '</div></div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud <?= e($radicado) ?> — JAL Comuna 11</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style> body{background:#f4f6f9;} .navbar-jal{background:#1565C0;} </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-jal">
    <div class="container-fluid">
        <a class="navbar-brand mb-0" href="dashboard.php"><i class="fa-solid fa-arrow-left me-2"></i>Volver al panel</a>
        <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-right-from-bracket me-1"></i>Salir</a>
    </div>
</nav>

<div class="container py-4" style="max-width:900px;">

    <?php if ($ok === 'aprobado'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>Solicitud aprobada. El certificado fue generado y enviado al ciudadano.</div>
    <?php elseif ($ok === 'aprobado_sincorreo'): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Solicitud aprobada y certificado generado, pero el correo al ciudadano no se pudo enviar. Puedes descargar el PDF abajo y enviarlo manualmente.</div>
    <?php elseif ($ok === 'rechazado'): ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info me-1"></i>Solicitud rechazada. Se notificó al ciudadano por correo.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Solicitud <?= e($radicado) ?></h1>
        <?php
            $map = ['pendiente'=>['warning text-dark','fa-clock','Pendiente'],'aprobado'=>['success','fa-circle-check','Aprobada'],'rechazado'=>['danger','fa-circle-xmark','Rechazada']];
            $b = $map[$s['estado']];
            echo '<span class="badge fs-6 bg-' . $b[0] . '"><i class="fa-solid ' . $b[1] . ' me-1"></i>' . $b[2] . '</span>';
        ?>
    </div>

    <!-- Datos de la solicitud -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h5 class="text-primary mb-3"><i class="fa-solid fa-user me-2"></i>Datos del solicitante</h5>
            <div class="row">
                <?php
                fila('Nombre completo', $s['nombre'], 'fa-user');
                fila('Documento', $s['tipo_doc'] . ' ' . $s['documento'], 'fa-id-card');
                fila('Dirección', $s['direccion'], 'fa-location-dot');
                fila('Barrio', $barrio_mostrar, 'fa-map');
                fila('Reside desde', date('d/m/Y', strtotime($s['fecha_residencia'])) . ' (hace ' . tiempo_residiendo($s['fecha_residencia']) . ')', 'fa-calendar');
                fila('Celular', $s['celular'], 'fa-phone');
                fila('Correo', $s['correo'], 'fa-envelope');
                fila('Solicitado el', date('d/m/Y h:i a', strtotime($s['creado_en'])), 'fa-clock');
                ?>
                <div class="col-12 mb-2">
                    <div class="text-muted small"><i class="fa-solid fa-comment-dots text-primary me-2"></i>Razón de la solicitud</div>
                    <div class="fw-semibold"><?= e($s['razon']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjuntos -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h5 class="text-primary mb-3"><i class="fa-solid fa-paperclip me-2"></i>Documentos de soporte</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="adjunto.php?id=<?= $id ?>&tipo=cedula" target="_blank" class="btn btn-outline-dark">
                    <i class="fa-solid fa-id-card me-1"></i>Ver cédula
                </a>
                <a href="adjunto.php?id=<?= $id ?>&tipo=recibo" target="_blank" class="btn btn-outline-dark">
                    <i class="fa-solid fa-file-invoice me-1"></i>Ver recibo EPM
                </a>
            </div>
            <p class="text-muted small mt-2 mb-0">Verifica que la dirección del recibo EPM coincida con la dirección declarada.</p>
        </div>
    </div>

    <?php if ($s['estado'] === 'pendiente'): ?>
        <!-- Acciones: aprobar / rechazar -->
        <div class="row g-3">
            <!-- APROBAR -->
            <div class="col-md-6">
                <div class="card shadow-sm border-success">
                    <div class="card-body">
                        <h6 class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Aprobar y emitir certificado</h6>
                        <form method="POST" action="procesar.php" onsubmit="return confirm('¿Confirmas aprobar esta solicitud? Se generará el certificado y se enviará al ciudadano.');">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="accion" value="aprobar">
                            <label class="form-label small mt-2">Dirigido a:</label>
                            <input type="text" name="dirigido_a" class="form-control mb-3" value="SAPIENCIA" required>
                            <button class="btn btn-success w-100"><i class="fa-solid fa-file-signature me-1"></i>Aprobar y enviar</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- RECHAZAR -->
            <div class="col-md-6">
                <div class="card shadow-sm border-danger">
                    <div class="card-body">
                        <h6 class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Rechazar solicitud</h6>
                        <form method="POST" action="procesar.php" onsubmit="return confirm('¿Confirmas rechazar esta solicitud? Se notificará al ciudadano.');">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="accion" value="rechazar">
                            <label class="form-label small mt-2">Motivo del rechazo (se envía al ciudadano):</label>
                            <textarea name="motivo_rechazo" class="form-control mb-3" rows="3" required placeholder="Ej: El recibo de EPM no coincide con la dirección declarada."></textarea>
                            <button class="btn btn-danger w-100"><i class="fa-solid fa-ban me-1"></i>Rechazar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Ya resuelta: mostrar resultado -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="text-primary mb-3"><i class="fa-solid fa-clipboard-check me-2"></i>Resolución</h5>
                <div class="row">
                    <?php
                    if ($s['estado'] === 'aprobado') {
                        fila('Dirigido a', $s['dirigido_a'], 'fa-building');
                        fila('Consecutivo', $s['consecutivo'], 'fa-hashtag');
                    } else {
                        echo '<div class="col-12 mb-3"><div class="text-muted small">Motivo del rechazo</div><div class="fw-semibold text-danger">' . e($s['motivo_rechazo']) . '</div></div>';
                    }
                    fila('Resuelto por', $atendio ?: '—', 'fa-user-tie');
                    fila('Fecha de resolución', $s['resuelto_en'] ? date('d/m/Y h:i a', strtotime($s['resuelto_en'])) : '—', 'fa-calendar-check');
                    ?>
                </div>
                <?php if ($s['estado'] === 'aprobado' && !empty($s['pdf_path'])): ?>
                    <a href="descargar_pdf.php?id=<?= $id ?>" target="_blank" class="btn btn-primary mt-2">
                        <i class="fa-solid fa-file-pdf me-1"></i>Descargar certificado PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
