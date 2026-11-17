<?php
/**
 * ============================================================
 *  PANEL (DASHBOARD) — Fase 4
 * ============================================================
 *  Lista todas las solicitudes con filtro por estado y buscador.
 *  Desde aquí el funcionario entra al detalle de cada una.
 * ============================================================
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/utilidades.php';
requerir_login();

// ----- Filtros -----
$estado = $_GET['estado'] ?? 'todos';
$buscar = trim($_GET['q'] ?? '');
$estados_validos = ['todos', 'pendiente', 'aprobado', 'rechazado'];
if (!in_array($estado, $estados_validos, true)) { $estado = 'todos'; }

// ----- Conteos para las tarjetas -----
$conteos = ['pendiente' => 0, 'aprobado' => 0, 'rechazado' => 0, 'total' => 0];
foreach (db()->query("SELECT estado, COUNT(*) n FROM solicitudes GROUP BY estado") as $f) {
    $conteos[$f['estado']] = (int)$f['n'];
    $conteos['total'] += (int)$f['n'];
}

// ----- Consulta de la lista (con filtros) -----
$where = [];
$params = [];
if ($estado !== 'todos') {
    $where[] = 'estado = :estado';
    $params[':estado'] = $estado;
}
if ($buscar !== '') {
    $where[] = '(nombre LIKE :q OR documento LIKE :q OR consecutivo LIKE :q)';
    $params[':q'] = '%' . $buscar . '%';
}
$sql = "SELECT id, consecutivo, nombre, tipo_doc, documento, barrio, barrio_otro,
               estado, creado_en
        FROM solicitudes";
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY (estado = "pendiente") DESC, creado_en DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$solicitudes = $stmt->fetchAll();

// Helper para pintar el badge de estado
function badge_estado($e) {
    $map = [
        'pendiente' => ['warning text-dark', 'fa-clock', 'Pendiente'],
        'aprobado'  => ['success', 'fa-circle-check', 'Aprobada'],
        'rechazado' => ['danger', 'fa-circle-xmark', 'Rechazada'],
    ];
    $x = $map[$e] ?? ['secondary', 'fa-question', $e];
    return '<span class="badge bg-' . $x[0] . '"><i class="fa-solid ' . $x[1] . ' me-1"></i>' . $x[2] . '</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel — JAL Comuna 11</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background:#f4f6f9; }
        .stat-card { cursor:pointer; transition:transform .1s; text-decoration:none; }
        .stat-card:hover { transform:translateY(-2px); }
        .navbar-jal { background:#1565C0; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-jal">
    <div class="container-fluid">
        <span class="navbar-brand mb-0"><i class="fa-solid fa-building-shield me-2"></i>Panel JAL Comuna 11</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small d-none d-sm-inline">Hola, <?= e($_SESSION['admin_nombre']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-right-from-bracket me-1"></i>Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4">

    <!-- Tarjetas resumen (funcionan como filtros) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="?estado=todos" class="stat-card card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="h3 mb-0"><?= $conteos['total'] ?></div>
                    <div class="text-muted small">Total</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?estado=pendiente" class="stat-card card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-warning"><?= $conteos['pendiente'] ?></div>
                    <div class="text-muted small">Pendientes</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?estado=aprobado" class="stat-card card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-success"><?= $conteos['aprobado'] ?></div>
                    <div class="text-muted small">Aprobadas</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?estado=rechazado" class="stat-card card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-danger"><?= $conteos['rechazado'] ?></div>
                    <div class="text-muted small">Rechazadas</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Barra de filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form class="row g-2 align-items-center" method="GET">
                <input type="hidden" name="estado" value="<?= e($estado) ?>">
                <div class="col">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, documento o consecutivo..." value="<?= e($buscar) ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                <?php if ($buscar !== '' || $estado !== 'todos'): ?>
                <div class="col-auto">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Limpiar</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabla de solicitudes -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N.º / Consecutivo</th>
                        <th>Solicitante</th>
                        <th>Documento</th>
                        <th>Barrio</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($solicitudes)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-regular fa-folder-open me-1"></i>No hay solicitudes para mostrar.
                        </td></tr>
                    <?php else: foreach ($solicitudes as $s): ?>
                        <tr>
                            <td>
                                <strong><?= e($s['consecutivo'] ?: 'SOL-' . str_pad($s['id'], 5, '0', STR_PAD_LEFT)) ?></strong>
                            </td>
                            <td><?= e($s['nombre']) ?></td>
                            <td><?= e($s['tipo_doc']) ?> <?= e($s['documento']) ?></td>
                            <td><?= e($s['barrio'] === 'Otro' ? $s['barrio_otro'] : $s['barrio']) ?></td>
                            <td class="small text-muted"><?= e(date('d/m/Y', strtotime($s['creado_en']))) ?></td>
                            <td><?= badge_estado($s['estado']) ?></td>
                            <td class="text-end">
                                <a href="ver.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-eye me-1"></i>Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0"><?= count($solicitudes) ?> solicitud(es) mostrada(s).</p>
</div>
</body>
</html>
