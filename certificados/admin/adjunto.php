<?php
/**
 * ============================================================
 *  VISOR SEGURO DE ADJUNTOS
 * ============================================================
 *  Entrega un adjunto (cédula o recibo EPM) SOLO a funcionarios
 *  logueados. Los archivos están en uploads/ (protegida por
 *  .htaccess), así que esta es la única forma de verlos.
 *
 *  Uso:  adjunto.php?id=12&tipo=cedula
 *        adjunto.php?id=12&tipo=recibo
 * ============================================================
 */
require_once __DIR__ . '/auth.php';
requerir_login();

$id   = (int)($_GET['id'] ?? 0);
$tipo = $_GET['tipo'] ?? '';

if ($id <= 0 || !in_array($tipo, ['cedula', 'recibo'], true)) {
    http_response_code(400);
    exit('Solicitud inválida.');
}

// Buscar el nombre del archivo en la BD
$col = $tipo === 'cedula' ? 'cedula_path' : 'recibo_path';
$stmt = db()->prepare("SELECT $col AS archivo FROM solicitudes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$fila = $stmt->fetch();

if (!$fila || empty($fila['archivo'])) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

// Construir la ruta real y validar que esté dentro de uploads/ (evita path traversal)
$nombre = basename($fila['archivo']);
$ruta   = RUTA_UPLOADS . '/' . $nombre;

if (!is_file($ruta)) {
    http_response_code(404);
    exit('El archivo ya no está disponible en el servidor.');
}

// Detectar tipo real y entregarlo inline (para verlo en el navegador)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($ruta) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: inline; filename="' . $tipo . '_' . $id . '"');
header('X-Content-Type-Options: nosniff');
readfile($ruta);
exit;
