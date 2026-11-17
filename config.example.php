<?php
// ============================================================
//  config.example.php — PLANTILLA DE CREDENCIALES
// ============================================================
//  Cómo usarlo:
//    1. Copia este archivo y renómbralo a:  config.php
//    2. Reemplaza los valores de abajo por tus credenciales reales.
//    3. NUNCA subas config.php a GitHub: el .gitignore ya lo ignora
//       y el .htaccess bloquea su acceso por web.
//
//  Este archivo (config.example.php) SÍ va en el repositorio, pero
//  solo con PLACEHOLDERS, nunca con datos reales.
// ============================================================

$BREVO_LOGIN    = 'TU_LOGIN@smtp-brevo.com';          // Login SMTP de Brevo
$BREVO_SMTP_KEY = 'TU_SMTP_KEY_DE_BREVO';             // SMTP Key de Brevo (xsmtpsib-...)
$CORREO_JAL     = 'correo-destino@ejemplo.com';       // destino: buzón de la JAL
$REMITENTE      = 'notificaciones@tudominio.com';     // remitente verificado en Brevo
$REMITENTE_NOM  = 'JAL Comuna 11';
