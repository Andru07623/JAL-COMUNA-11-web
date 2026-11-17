<?php
/**
 * ============================================================
 *  LOGIN DEL FUNCIONARIO
 * ============================================================
 */
require_once __DIR__ . '/auth.php';

// Si ya está logueado, va directo al panel
if (esta_logueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$expiro = isset($_GET['expiro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $error = 'La sesión expiró. Intenta de nuevo.';
    } elseif ($usuario === '' || $password === '') {
        $error = 'Ingresa usuario y contraseña.';
    } elseif (intentar_login($usuario, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        // Mensaje genérico (no revelamos si el usuario existe o no)
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Panel JAL Comuna 11</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1565C0 0%, #0d47a1 100%);
            min-height:100vh; display:flex; align-items:center;
        }
        .card-login { border:none; border-radius:16px; box-shadow:0 10px 40px rgba(0,0,0,.25); }
        .btn-institucional { background:#1565C0; border-color:#1565C0; }
        .btn-institucional:hover { background:#0d47a1; border-color:#0d47a1; }
        .login-logo { width:70px; height:70px; object-fit:contain; }
        .login-icon {
            width:70px; height:70px; border-radius:50%; background:#1565C0;
            display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:28px;
        }
    </style>
</head>
<body>
<div class="container" style="max-width:420px;">
    <div class="card card-login">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <?php if (file_exists(__DIR__ . '/../assets/logo.png')): ?>
                    <img src="../assets/logo.png" alt="JAL Comuna 11" class="login-logo mb-2">
                <?php else: ?>
                    <div class="login-icon mb-2"><i class="fa-solid fa-building-shield"></i></div>
                <?php endif; ?>
                <h1 class="h5 mb-0" style="color:#1565C0;">Panel Administrativo</h1>
                <p class="text-muted small mb-0">JAL Comuna 11 — Certificados</p>
            </div>

            <?php if ($expiro): ?>
                <div class="alert alert-warning py-2 small">Tu sesión expiró por inactividad. Inicia sesión de nuevo.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-1"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="usuario" class="form-control" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-institucional text-white">
                        <i class="fa-solid fa-right-to-bracket me-1"></i>Ingresar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-white-50 small mt-3 mb-0">Acceso exclusivo para funcionarios autorizados.</p>
</div>
</body>
</html>
