<?php
/**
 * ============================================================
 *  DESCARGA SEGURA DEL CERTIFICADO PDF (solo funcionarios)
 * ============================================================
 */
require_once __DIR__ . '/auth.php';
requerir_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Solicitud inválida.'); }

$stmt = db()->prepare('SELECT pdf_path, consecutivo FROM solicitudes WHERE id = :id AND estado = "aprobado" LIMIT 1');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();

if (!$s || empty($s['pdf_path'])) {
    http_response_code(404);
    exit('El certificado PDF aún no está disponible. (La generación automática se activa en la Fase 5.)');
}

$ruta = __DIR__ . '/../certificados_generados/' . basename($s['pdf_path']);
if (!is_file($ruta)) {
    http_response_code(404);
    exit('El archivo del certificado no se encuentra en el servidor.');
}

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: inline; filename="Certificado_' . $s['consecutivo'] . '.pdf"');
header('X-Content-Type-Options: nosniff');
readfile($ruta);
exit;
