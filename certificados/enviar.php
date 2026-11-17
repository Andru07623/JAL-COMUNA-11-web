<?php
/**
 * ============================================================
 *  PROCESA EL FORMULARIO DE SOLICITUD
 * ============================================================
 *  1. Valida todos los campos.
 *  2. Guarda los adjuntos (cédula + recibo EPM) de forma segura.
 *  3. Inserta la solicitud en la base de datos (estado "pendiente").
 *  4. Envía correo de confirmación al ciudadano.
 *  5. Avisa por correo al funcionario que hay una nueva solicitud.
 * ============================================================
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/utilidades.php';
require_once __DIR__ . '/lib/correo.php';

session_start();

// Solo se accede por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/** Redirige de vuelta al formulario con un error y conserva lo escrito. */
function volver_con_error($mensaje) {
    $_SESSION['old'] = [
        'nombre'           => $_POST['nombre'] ?? '',
        'tipo_doc'         => $_POST['tipo_doc'] ?? '',
        'documento'        => $_POST['documento'] ?? '',
        'direccion'        => $_POST['direccion'] ?? '',
        'barrio'           => $_POST['barrio'] ?? '',
        'barrio_otro'      => $_POST['barrio_otro'] ?? '',
        'fecha_residencia' => $_POST['fecha_residencia'] ?? '',
        'celular'          => $_POST['celular'] ?? '',
        'correo'           => $_POST['correo'] ?? '',
        'razon'            => $_POST['razon'] ?? '',
    ];
    header('Location: index.php?error=' . urlencode($mensaje));
    exit;
}

// ---------- 1. Recoger y limpiar datos ----------
$nombre      = limpiar($_POST['nombre'] ?? '');
$tipo_doc    = limpiar($_POST['tipo_doc'] ?? '');
$documento   = limpiar($_POST['documento'] ?? '');
$direccion   = limpiar($_POST['direccion'] ?? '');
$barrio      = limpiar($_POST['barrio'] ?? '');
$barrio_otro = limpiar($_POST['barrio_otro'] ?? '');
$fecha_res   = limpiar($_POST['fecha_residencia'] ?? '');
$celular     = limpiar($_POST['celular'] ?? '');
$correo      = limpiar($_POST['correo'] ?? '');
$razon       = limpiar($_POST['razon'] ?? '');
$consent     = isset($_POST['consentimiento']);

// ---------- 2. Validaciones ----------
if ($nombre === '' || $documento === '' || $direccion === '' || $barrio === ''
    || $fecha_res === '' || $celular === '' || $correo === '' || $razon === '') {
    volver_con_error('Por favor completa todos los campos obligatorios.');
}
if (!in_array($tipo_doc, ['CC','TI','CE','PA'], true)) {
    volver_con_error('Tipo de documento no válido.');
}
if (!preg_match('/^[0-9]{5,15}$/', $documento)) {
    volver_con_error('El número de documento debe contener solo números (entre 5 y 15 dígitos).');
}
if (!correo_valido($correo)) {
    volver_con_error('El correo electrónico no tiene un formato válido.');
}
if (!preg_match('/^[0-9]{7,15}$/', preg_replace('/\s+/', '', $celular))) {
    volver_con_error('El número de celular no es válido.');
}
// Validar fecha de residencia
$d = DateTime::createFromFormat('Y-m-d', $fecha_res);
if (!$d || $d->format('Y-m-d') !== $fecha_res) {
    volver_con_error('La fecha de residencia no es válida.');
}
if ($d > new DateTime('today')) {
    volver_con_error('La fecha de residencia no puede ser en el futuro.');
}
// Barrio "Otro"
if ($barrio === 'Otro') {
    if ($barrio_otro === '') {
        volver_con_error('Especifica el nombre del barrio.');
    }
} else {
    if (!in_array($barrio, $GLOBALS['BARRIOS_COMUNA11'], true)) {
        volver_con_error('El barrio seleccionado no es válido.');
    }
    $barrio_otro = null;
}
if (!$consent) {
    volver_con_error('Debes aceptar el tratamiento de datos personales para continuar.');
}

// ---------- 3. Guardar adjuntos ----------
try {
    $cedula_path = guardar_adjunto($_FILES['cedula'] ?? null, 'cedula');
    $recibo_path = guardar_adjunto($_FILES['recibo'] ?? null, 'recibo');
} catch (Exception $ex) {
    volver_con_error($ex->getMessage());
}

// ---------- 4. Insertar en la base de datos ----------
try {
    $sql = "INSERT INTO solicitudes
            (nombre, tipo_doc, documento, direccion, barrio, barrio_otro,
             fecha_residencia, celular, correo, razon,
             cedula_path, recibo_path, estado, ip_solicitante, creado_en)
            VALUES
            (:nombre, :tipo_doc, :documento, :direccion, :barrio, :barrio_otro,
             :fecha_residencia, :celular, :correo, :razon,
             :cedula_path, :recibo_path, 'pendiente', :ip, NOW())";
    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':nombre'           => $nombre,
        ':tipo_doc'         => $tipo_doc,
        ':documento'        => $documento,
        ':direccion'        => $direccion,
        ':barrio'           => $barrio,
        ':barrio_otro'      => $barrio_otro,
        ':fecha_residencia' => $fecha_res,
        ':celular'          => $celular,
        ':correo'           => $correo,
        ':razon'            => $razon,
        ':cedula_path'      => $cedula_path,
        ':recibo_path'      => $recibo_path,
        ':ip'               => ip_cliente(),
    ]);
    $id_solicitud = db()->lastInsertId();
} catch (PDOException $ex) {
    // Si falla la BD, borramos los adjuntos que ya habíamos guardado
    @unlink(RUTA_UPLOADS . '/' . $cedula_path);
    @unlink(RUTA_UPLOADS . '/' . $recibo_path);
    volver_con_error('Ocurrió un error al guardar tu solicitud. Intenta de nuevo más tarde.');
}

// Número de radicado visible para el ciudadano (mientras no se aprueba, usamos el id)
$radicado = 'SOL-' . str_pad($id_solicitud, 5, '0', STR_PAD_LEFT);

// ---------- 5. Correos (no bloquean si el SMTP no está listo) ----------
$barrio_mostrar = ($barrio === 'Otro') ? $barrio_otro : $barrio;

// 5a. Confirmación al ciudadano
$cuerpo_ciudadano = plantilla_correo(
    'Solicitud de certificado de residencia recibida',
    '<p>Hola <strong>' . e($nombre) . '</strong>,</p>
     <p>Hemos recibido tu solicitud de certificado de residencia. Estos son los datos registrados:</p>
     <ul>
        <li><strong>N.º de solicitud:</strong> ' . e($radicado) . '</li>
        <li><strong>Documento:</strong> ' . e($tipo_doc) . ' ' . e($documento) . '</li>
        <li><strong>Dirección:</strong> ' . e($direccion) . ', barrio ' . e($barrio_mostrar) . '</li>
     </ul>
     <p>Nuestro equipo revisará tu información y los documentos adjuntos. Si todo está correcto,
     recibirás el certificado en este mismo correo. Si falta algo, te contactaremos.</p>
     <p>Gracias por usar los servicios de la JAL Comuna 11.</p>'
);
enviar_correo($correo, 'Solicitud de certificado recibida — ' . $radicado, $cuerpo_ciudadano);

// 5b. Aviso al funcionario
$cuerpo_funcionario = plantilla_correo(
    'Nueva solicitud de certificado',
    '<p>Se recibió una nueva solicitud de certificado de residencia:</p>
     <ul>
        <li><strong>Solicitud:</strong> ' . e($radicado) . '</li>
        <li><strong>Nombre:</strong> ' . e($nombre) . '</li>
        <li><strong>Documento:</strong> ' . e($tipo_doc) . ' ' . e($documento) . '</li>
        <li><strong>Barrio:</strong> ' . e($barrio_mostrar) . '</li>
        <li><strong>Correo:</strong> ' . e($correo) . '</li>
        <li><strong>Celular:</strong> ' . e($celular) . '</li>
     </ul>
     <p>Ingresa al panel administrativo para revisarla, verificar los adjuntos y aprobar o rechazar.</p>'
);
enviar_correo(CORREO_FUNCIONARIO, 'Nueva solicitud de certificado — ' . $radicado, $cuerpo_funcionario);

// ---------- Listo: redirigir con mensaje de éxito ----------
header('Location: index.php?ok=1&radicado=' . urlencode($radicado));
exit;
