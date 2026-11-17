<?php
/**
 * ============================================================
 *  config.example.php — PLANTILLA DE CONFIGURACIÓN
 *  Sistema de Certificados de Residencia - JAL Comuna 11
 * ============================================================
 *
 *  ESTE ES UN ARCHIVO DE EJEMPLO (va al repositorio con placeholders).
 *
 *  Pasos:
 *   1. Copia este archivo y renómbralo a:  config.php
 *   2. Rellena tus datos reales (BD y correo) en config.php
 *   3. NUNCA subas config.php a GitHub ni lo compartas
 *      (contiene contraseñas). El .gitignore ya lo ignora.
 *
 *  Tendrás DOS config.php distintos:
 *   - Uno en tu XAMPP local (para pruebas)
 *   - Uno en InfinityFree (para producción)
 * ============================================================
 */

// ----- Base de datos (MySQL) -----
// En XAMPP local, normalmente:
//   host = 'localhost', usuario = 'root', clave = '' (vacía)
// En InfinityFree, lo ves en el panel: "MySQL Databases"
define('DB_HOST', 'TU_HOST_MYSQL');
define('DB_NAME', 'TU_NOMBRE_DE_BASE_DE_DATOS');
define('DB_USER', 'TU_USUARIO_MYSQL');
define('DB_PASS', 'TU_CONTRASENA_MYSQL');


define('SMTP_HOST',        'smtp-relay.brevo.com');
define('SMTP_PORT',        587);
define('SMTP_USER',        'TU_LOGIN@smtp-brevo.com');
define('SMTP_PASS',        'TU_SMTP_KEY_DE_BREVO');
define('SMTP_SECURE',      'tls');
define('SMTP_REMITENTE',   'notificaciones@tudominio.com');
define('SMTP_FROM_NOMBRE', 'JAL Comuna 11');

// ----- Correo donde el funcionario recibe aviso de nuevas solicitudes -----
define('CORREO_FUNCIONARIO', 'correo-destino@ejemplo.com');

// ----- Rutas / configuración general -----
// Carpeta donde se guardan los adjuntos (cédula, recibo EPM).
// Debe existir y tener permisos de escritura.
define('RUTA_UPLOADS', __DIR__ . '/uploads');

// Tamaño máximo por archivo adjunto (en bytes). 5 MB por defecto.
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Tipos de archivo permitidos para adjuntos
$GLOBALS['TIPOS_PERMITIDOS'] = ['application/pdf', 'image/jpeg', 'image/png'];

// Entorno: 'local' muestra errores detallados; 'produccion' los oculta.
define('ENTORNO', 'produccion');

// ============================================================
//  No toques lo de abajo: conexión a la BD y ajustes de errores
// ============================================================
if (ENTORNO === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

/**
 * Devuelve la conexión PDO a la base de datos.
 * Usa PDO porque es más seguro contra inyección SQL.
 */
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            if (ENTORNO === 'local') {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
            die('No se pudo conectar con la base de datos. Intenta más tarde.');
        }
    }
    return $pdo;
}
