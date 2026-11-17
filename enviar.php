<?php
// ============================================================
//  enviar.php — Formulario de Comentarios y Sugerencias
//  JAL Comuna 11 — envío vía Brevo (SMTP) con PHPMailer
// ============================================================

// ====== CONFIGURACIÓN ======
// Las credenciales viven en config.php (que NO se sube a GitHub)
require __DIR__ . '/config.php';
// =====================================================

// Cargar PHPMailer
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sugerencias.html');
    exit;
}

// ---- Recoger y limpiar datos ----
function limpiar($v) {
    return trim(htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'));
}

$nombre    = limpiar($_POST['nombre'] ?? '');
$apellido  = limpiar($_POST['apellido'] ?? '');
$tipo      = limpiar($_POST['tipo'] ?? '');
$documento = limpiar($_POST['documento'] ?? '');
$telefono1 = limpiar($_POST['telefono1'] ?? '');
$telefono2 = limpiar($_POST['telefono2'] ?? '');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$barrio    = limpiar($_POST['barrio'] ?? '');
$otroTexto = limpiar($_POST['otroTexto'] ?? '');
$mensaje   = limpiar($_POST['mensaje'] ?? '');

// Si eligió "otro", usar el texto especificado
if ($barrio === 'otro' && $otroTexto !== '') {
    $barrio = $otroTexto;
}

// ---- Validación básica ----
$errores = [];
if ($nombre === '')    $errores[] = 'nombre';
if ($apellido === '')  $errores[] = 'apellido';
if ($documento === '') $errores[] = 'documento';
if ($telefono1 === '') $errores[] = 'telefono1';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'email';
if ($barrio === '')    $errores[] = 'barrio';
if ($mensaje === '')   $errores[] = 'mensaje';

if (!empty($errores)) {
    header('Location: sugerencias.html?error=1');
    exit;
}

// ---- Correo para la JAL ----
$cuerpoJAL = "
<h2 style='color:#0033a0;'>Nuevo comentario / sugerencia</h2>
<p>Se ha recibido un nuevo mensaje desde el sitio web de la JAL Comuna 11:</p>
<table style='border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;'>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Nombre:</td><td style='padding:6px 12px;'>{$nombre} {$apellido}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Documento:</td><td style='padding:6px 12px;'>{$tipo} {$documento}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Teléfono 1:</td><td style='padding:6px 12px;'>{$telefono1}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Teléfono 2:</td><td style='padding:6px 12px;'>{$telefono2}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Correo:</td><td style='padding:6px 12px;'>{$email}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;'>Barrio:</td><td style='padding:6px 12px;'>{$barrio}</td></tr>
  <tr><td style='padding:6px 12px;font-weight:bold;vertical-align:top;'>Mensaje:</td><td style='padding:6px 12px;'>" . nl2br($mensaje) . "</td></tr>
</table>
<p style='color:#888;font-size:12px;margin-top:20px;'>Mensaje enviado automáticamente desde el formulario de comentarios y sugerencias.</p>
";

// ---- Correo de confirmación para el ciudadano ----
$cuerpoCiudadano = "
<div style='font-family:Arial,sans-serif;font-size:15px;color:#333;'>
  <h2 style='color:#0033a0;'>¡Hemos recibido tu mensaje!</h2>
  <p>Hola <strong>{$nombre}</strong>,</p>
  <p>Gracias por comunicarte con la <strong>Junta Administradora Local de la Comuna 11 (Laureles-Estadio)</strong>.
  Tu comentario o sugerencia fue recibido correctamente y será leído por nuestro equipo.</p>
  <p style='background:#f1f5ff;border-left:4px solid #0033a0;padding:12px 16px;border-radius:6px;'>
     <strong>Tu mensaje:</strong><br>" . nl2br($mensaje) . "
  </p>
  <p>Si tu solicitud requiere respuesta, te contactaremos a través del correo o teléfono que nos indicaste.</p>
  <p style='margin-top:24px;'>Cordialmente,<br><strong>JAL Comuna 11 - Medellín</strong></p>
  <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
  <p style='color:#999;font-size:12px;'>Este es un mensaje automático, por favor no respondas a este correo.</p>
</div>
";

// ---- Enviar con PHPMailer + Brevo ----
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $BREVO_LOGIN;
    $mail->Password   = $BREVO_SMTP_KEY;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // --- Correo 1: a la JAL ---
    $mail->setFrom($REMITENTE, $REMITENTE_NOM);
    $mail->addAddress($CORREO_JAL, 'JAL Comuna 11');
    $mail->addReplyTo($email, "{$nombre} {$apellido}"); // responder va directo al ciudadano
    $mail->isHTML(true);
    $mail->Subject = "Nuevo comentario/sugerencia - {$nombre} {$apellido}";
    $mail->Body    = $cuerpoJAL;
    $mail->send();

    // --- Correo 2: confirmación al ciudadano ---
    $mail->clearAddresses();
    $mail->clearReplyTos();
    $mail->setFrom($REMITENTE, $REMITENTE_NOM);
    $mail->addAddress($email, "{$nombre} {$apellido}");
    $mail->addReplyTo($CORREO_JAL, 'JAL Comuna 11'); // si el ciudadano responde, llega a la JAL
    $mail->Subject = "Hemos recibido tu mensaje - JAL Comuna 11";
    $mail->Body    = $cuerpoCiudadano;
    $mail->send();

    header('Location: gracias.html');
    exit;

} catch (Exception $e) {
    header('Location: sugerencias.html?error=envio');
    exit;
}
