<?php
/**
 * ============================================================
 *  PROCESAR ACCIÓN DEL FUNCIONARIO (aprobar / rechazar)
 * ============================================================
 *  - Verifica login y token CSRF.
 *  - Aprobar: asigna consecutivo, cambia estado, genera el PDF
 *    (Fase 5) y envía el certificado por correo al ciudadano.
 *  - Rechazar: cambia estado, guarda el motivo y notifica al
 *    ciudadano por correo.
 * ============================================================
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/utilidades.php';
require_once __DIR__ . '/../lib/correo.php';
requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$id     = (int)($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

// CSRF
if (!csrf_valido($_POST['csrf'] ?? '')) {
    header('Location: ver.php?id=' . $id . '&error=' . urlencode('Sesión expirada, intenta de nuevo.'));
    exit;
}

// Cargar la solicitud
$stmt = db()->prepare('SELECT * FROM solicitudes WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();
if (!$s) { header('Location: dashboard.php'); exit; }

// Solo se pueden procesar solicitudes pendientes
if ($s['estado'] !== 'pendiente') {
    header('Location: ver.php?id=' . $id . '&error=' . urlencode('Esta solicitud ya fue resuelta.'));
    exit;
}

$barrio_mostrar = $s['barrio'] === 'Otro' ? $s['barrio_otro'] : $s['barrio'];

/* ======================= RECHAZAR ======================= */
if ($accion === 'rechazar') {
    $motivo = limpiar($_POST['motivo_rechazo'] ?? '');
    if ($motivo === '') {
        header('Location: ver.php?id=' . $id . '&error=' . urlencode('Debes indicar el motivo del rechazo.'));
        exit;
    }

    $up = db()->prepare("UPDATE solicitudes
                         SET estado='rechazado', motivo_rechazo=:m, atendido_por=:u, resuelto_en=NOW()
                         WHERE id=:id");
    $up->execute([':m' => $motivo, ':u' => $_SESSION['admin_id'], ':id' => $id]);

    // Notificar al ciudadano
    $cuerpo = plantilla_correo(
        'Resultado de tu solicitud de certificado',
        '<p>Hola <strong>' . e($s['nombre']) . '</strong>,</p>
         <p>Lamentamos informarte que tu solicitud de certificado de residencia
         <strong>no pudo ser aprobada</strong> por el siguiente motivo:</p>
         <blockquote style="border-left:4px solid #dc3545; padding-left:12px; color:#555;">'
         . nl2br(e($motivo)) . '</blockquote>
         <p>Puedes volver a presentar la solicitud corrigiendo lo indicado. Si tienes dudas,
         escríbenos a jal.laureles11@gmail.com.</p>'
    );
    enviar_correo($s['correo'], 'Resultado de tu solicitud de certificado', $cuerpo);

    header('Location: ver.php?id=' . $id . '&ok=rechazado');
    exit;
}

/* ======================= APROBAR ======================= */
if ($accion === 'aprobar') {
    $dirigido_a = limpiar($_POST['dirigido_a'] ?? '');
    if ($dirigido_a === '') {
        header('Location: ver.php?id=' . $id . '&error=' . urlencode('Indica a quién va dirigido el certificado.'));
        exit;
    }

    // --- Generar consecutivo DDMMAA-NN de forma segura (transacción) ---
    $hoy = date('Y-m-d');
    $consecutivo = generar_consecutivo($hoy);

    // --- Generar el PDF del certificado (Fase 5) ---
    // La función generar_certificado_pdf() vive en lib/certificado.php.
    // Si todavía no existe (Fase 5 no construida), no rompemos: se aprueba sin PDF.
    $pdf_nombre = null;
    $pdf_ruta = null;
    $archivo_cert = __DIR__ . '/../lib/certificado.php';
    if (file_exists($archivo_cert)) {
        require_once $archivo_cert;
        try {
            $pdf_nombre = generar_certificado_pdf($s, $consecutivo, $dirigido_a);
            $pdf_ruta   = __DIR__ . '/../certificados_generados/' . $pdf_nombre;
        } catch (Exception $ex) {
            $pdf_nombre = null;
        }
    }

    // --- Actualizar la solicitud ---
    $up = db()->prepare("UPDATE solicitudes
                         SET estado='aprobado', consecutivo=:c, dirigido_a=:d,
                             pdf_path=:p, atendido_por=:u, resuelto_en=NOW()
                         WHERE id=:id");
    $up->execute([
        ':c' => $consecutivo,
        ':d' => $dirigido_a,
        ':p' => $pdf_nombre,
        ':u' => $_SESSION['admin_id'],
        ':id'=> $id,
    ]);

    // --- Enviar el certificado al ciudadano ---
    $cuerpo = plantilla_correo(
        'Tu certificado de residencia está listo',
        '<p>Hola <strong>' . e($s['nombre']) . '</strong>,</p>
         <p>Tu solicitud de certificado de residencia fue <strong>aprobada</strong>.
         Adjuntamos tu certificado en formato PDF.</p>
         <ul>
            <li><strong>Consecutivo:</strong> ' . e($consecutivo) . '</li>
            <li><strong>Dirigido a:</strong> ' . e($dirigido_a) . '</li>
         </ul>
         <p>Recuerda que el certificado tiene una vigencia de 30 días.</p>
         <p>Gracias por usar los servicios de la JAL Comuna 11.</p>'
    );
    $correo_ok = enviar_correo(
        $s['correo'],
        'Certificado de residencia — ' . $consecutivo,
        $cuerpo,
        $pdf_ruta,
        'Certificado_Residencia_' . $consecutivo . '.pdf'
    );

    $destino = $correo_ok ? 'ok=aprobado' : 'ok=aprobado_sincorreo';
    header('Location: ver.php?id=' . $id . '&' . $destino);
    exit;
}

// Acción desconocida
header('Location: ver.php?id=' . $id);
exit;


/**
 * Genera el siguiente consecutivo del día en formato DDMMAA-NN.
 * Usa una transacción para que dos aprobaciones simultáneas no choquen.
 */
function generar_consecutivo($dia) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Bloquea/crea la fila del día
        $pdo->prepare("INSERT INTO consecutivos (dia, contador) VALUES (:d, 0)
                       ON DUPLICATE KEY UPDATE dia = dia")
            ->execute([':d' => $dia]);

        $sel = $pdo->prepare("SELECT contador FROM consecutivos WHERE dia = :d FOR UPDATE");
        $sel->execute([':d' => $dia]);
        $n = (int)$sel->fetchColumn() + 1;

        $pdo->prepare("UPDATE consecutivos SET contador = :n WHERE dia = :d")
            ->execute([':n' => $n, ':d' => $dia]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    // DDMMAA-NN  (ej: 091126-01)
    return date('dmy', strtotime($dia)) . '-' . str_pad($n, 2, '0', STR_PAD_LEFT);
}
