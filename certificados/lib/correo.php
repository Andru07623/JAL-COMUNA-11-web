<?php
/**
 * ============================================================
 *  ENVÍO DE CORREOS (PHPMailer + SMTP)
 * ============================================================
 *  Requiere la librería PHPMailer en: lib/phpmailer/
 *  (En el chat te explico cómo descargarla y dónde ponerla.)
 *
 *  Función principal: enviar_correo(...)
 *  - Si PHPMailer no está instalado o el SMTP no está configurado,
 *    NO rompe el sistema: solo registra el correo en un log y
 *    devuelve false. Así puedes probar el formulario en XAMPP
 *    aunque todavía no tengas el correo listo.
 * ============================================================
 */

/**
 * Envía un correo HTML. Opcionalmente con un adjunto.
 *
 * @param string $para        Correo destino
 * @param string $asunto      Asunto
 * @param string $cuerpo_html Cuerpo en HTML
 * @param string|null $adjunto_ruta  Ruta a un archivo para adjuntar (opcional)
 * @param string|null $adjunto_nombre Nombre con que se muestra el adjunto
 * @return bool  true si se envió, false si no se pudo (sin romper el flujo)
 */
function enviar_correo($para, $asunto, $cuerpo_html, $adjunto_ruta = null, $adjunto_nombre = null) {

    $autoload = __DIR__ . '/phpmailer/src/PHPMailer.php';

    // Si PHPMailer no está instalado, no rompemos nada: logueamos y seguimos.
    if (!file_exists($autoload)) {
        registrar_correo_en_log($para, $asunto, 'PHPMailer no instalado (lib/phpmailer/)');
        return false;
    }

    // Si el SMTP todavía tiene la contraseña de ejemplo, no intentamos enviar.
    if (SMTP_PASS === 'TU_CONTRASENA_DE_APLICACION' || SMTP_PASS === '') {
        registrar_correo_en_log($para, $asunto, 'SMTP sin configurar (contraseña de ejemplo)');
        return false;
    }

    require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;  // 'tls'
        $mail->Port       = SMTP_PORT;    // 587
        $mail->CharSet    = 'UTF-8';

        $remitente = defined('SMTP_REMITENTE') ? SMTP_REMITENTE : SMTP_USER;
        $mail->setFrom($remitente, SMTP_FROM_NOMBRE);
        $mail->addAddress($para);

        if ($adjunto_ruta && file_exists($adjunto_ruta)) {
            $mail->addAttachment($adjunto_ruta, $adjunto_nombre ?: basename($adjunto_ruta));
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $cuerpo_html));

        $mail->send();
        return true;

    } catch (Exception $e) {
        registrar_correo_en_log($para, $asunto, 'Error PHPMailer: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Guarda un registro cuando un correo no se pudo enviar (para diagnóstico).
 */
function registrar_correo_en_log($para, $asunto, $motivo) {
    $linea = sprintf("[%s] PARA: %s | ASUNTO: %s | NOTA: %s\n",
        date('Y-m-d H:i:s'), $para, $asunto, $motivo);
    @file_put_contents(__DIR__ . '/../correos.log', $linea, FILE_APPEND);
}

/**
 * Plantilla HTML sencilla y con la imagen institucional para los correos.
 */
function plantilla_correo($titulo, $contenido_html) {
    return '
    <div style="font-family: Arial, Helvetica, sans-serif; max-width:600px; margin:0 auto; border:1px solid #e0e0e0; border-radius:8px; overflow:hidden;">
        <div style="background:#1565C0; color:#fff; padding:20px; text-align:center;">
            <h2 style="margin:0; font-size:18px;">JAL Comuna 11 — Laureles-Estadio</h2>
            <p style="margin:4px 0 0; font-size:13px; opacity:.9;">' . e($titulo) . '</p>
        </div>
        <div style="padding:24px; color:#333; font-size:14px; line-height:1.6;">
            ' . $contenido_html . '
        </div>
        <div style="background:#f5f5f5; color:#777; padding:16px; text-align:center; font-size:12px;">
            Junta Administradora Local — Comuna 11 Laureles-Estadio<br>
            Avenida 39 D #73-113 · jal.laureles11@gmail.com<br>
            <span style="color:#aaa;">Este es un mensaje automático, por favor no respondas a este correo.</span>
        </div>
    </div>';
}
