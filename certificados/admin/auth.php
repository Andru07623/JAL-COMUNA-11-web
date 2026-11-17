<?php
/**
 * ============================================================
 *  AUTENTICACIÓN DEL PANEL ADMINISTRATIVO
 * ============================================================
 *  Incluye este archivo AL INICIO de cualquier página que solo
 *  deban ver los funcionarios (dashboard, ver, aprobar, etc.).
 *
 *  Uso:
 *     require_once __DIR__ . '/auth.php';
 *     requerir_login();   // <- si no hay sesión, manda al login
 * ============================================================
 */

require_once __DIR__ . '/../config.php';

// Config de cookies de sesión más seguras (antes de iniciar sesión)
if (session_status() === PHP_SESSION_NONE) {
    $cookie = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookie['path'],
        'domain'   => $cookie['domain'],
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,            // la cookie no es accesible por JavaScript
        'samesite' => 'Lax',
    ]);
    session_name('JALADMIN');
    session_start();
}

/**
 * ¿Hay un funcionario con sesión iniciada?
 */
function esta_logueado() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_usuario']);
}

/**
 * Protege una página: si no hay sesión, redirige al login.
 */
function requerir_login() {
    if (!esta_logueado()) {
        header('Location: login.php');
        exit;
    }
    // Expira la sesión tras 60 min de inactividad
    $limite = 60 * 60;
    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > $limite)) {
        cerrar_sesion();
        header('Location: login.php?expiro=1');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Intenta iniciar sesión con usuario y contraseña.
 * Devuelve true si tuvo éxito; false si las credenciales son incorrectas.
 */
function intentar_login($usuario, $password) {
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE usuario = :u AND activo = 1 LIMIT 1');
    $stmt->execute([':u' => $usuario]);
    $u = $stmt->fetch();

    if (!$u) {
        return false;
    }
    if (!password_verify($password, $u['password_hash'])) {
        return false;
    }

    // Credenciales correctas: regenerar id de sesión (evita fijación de sesión)
    session_regenerate_id(true);
    $_SESSION['admin_id']         = $u['id'];
    $_SESSION['admin_usuario']    = $u['usuario'];
    $_SESSION['admin_nombre']     = $u['nombre'];
    $_SESSION['ultima_actividad'] = time();

    // Actualizar último acceso
    $up = db()->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');
    $up->execute([':id' => $u['id']]);

    return true;
}

/**
 * Cierra la sesión del funcionario.
 */
function cerrar_sesion() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Genera un token CSRF y lo guarda en sesión (para proteger formularios).
 */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Verifica que el token CSRF enviado sea válido.
 */
function csrf_valido($token) {
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
}
