<?php
/**
 * ============================================================
 *  FUNCIONES DE UTILIDAD COMPARTIDAS
 * ============================================================
 *  Funciones que usan varios archivos del sistema:
 *   - limpieza/seguridad de texto
 *   - validaciones
 *   - manejo seguro de archivos subidos
 *   - lista oficial de barrios de la Comuna 11
 * ============================================================
 */

// Lista oficial de barrios de la Comuna 11 (Laureles-Estadio)
$GLOBALS['BARRIOS_COMUNA11'] = [
    'Carlos E. Restrepo',
    'Suramericana',
    'Naranjal',
    'San Joaquín',
    'Los Conquistadores',
    'Bolivariana',
    'Laureles',
    'Las Acacias',
    'La Castellana',
    'Lorena',
    'El Velódromo',
    'Estadio',
    'Los Colores',
    'La Cuarta Brigada',
    'Florida Nueva',
];

/**
 * Escapa texto para mostrarlo en HTML de forma segura (evita XSS).
 */
function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Limpia un valor de entrada: quita espacios sobrantes.
 */
function limpiar($valor) {
    return trim((string)$valor);
}

/**
 * Valida que un correo tenga formato correcto.
 */
function correo_valido($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Obtiene la IP del solicitante (para auditoría / Ley 1581).
 */
function ip_cliente() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Genera un nombre de archivo seguro y no adivinable.
 * Conserva solo la extensión original.
 */
function nombre_archivo_seguro($nombre_original, $prefijo = '') {
    $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext); // solo letras/números
    $aleatorio = bin2hex(random_bytes(16));       // 32 caracteres hex
    $fecha = date('Ymd');
    return trim($prefijo . '_' . $fecha . '_' . $aleatorio . '.' . $ext, '_');
}

/**
 * Valida y mueve un archivo subido a la carpeta de uploads.
 * Devuelve el nombre guardado, o lanza Exception si algo falla.
 *
 * @param array  $archivo   Entrada de $_FILES['campo']
 * @param string $prefijo   Prefijo para el nombre (ej. 'cedula', 'recibo')
 * @return string Nombre del archivo guardado (no la ruta completa)
 */
function guardar_adjunto($archivo, $prefijo) {
    // ¿Se subió algo?
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('No se adjuntó el archivo requerido: ' . $prefijo);
    }
    // ¿Error del servidor al subir?
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo (' . $prefijo . '). Código: ' . $archivo['error']);
    }
    // ¿Tamaño dentro del límite?
    if ($archivo['size'] > MAX_FILE_SIZE) {
        throw new Exception('El archivo ' . $prefijo . ' supera el tamaño máximo permitido (5 MB).');
    }
    // ¿Tipo MIME real permitido? (no confiamos en la extensión que dice el navegador)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    if (!in_array($mime, $GLOBALS['TIPOS_PERMITIDOS'], true)) {
        throw new Exception('El archivo ' . $prefijo . ' debe ser PDF, JPG o PNG.');
    }
    // Asegura que la carpeta exista
    if (!is_dir(RUTA_UPLOADS)) {
        mkdir(RUTA_UPLOADS, 0755, true);
    }
    // Nombre seguro + mover
    $nombre = nombre_archivo_seguro($archivo['name'], $prefijo);
    $destino = RUTA_UPLOADS . '/' . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new Exception('No se pudo guardar el archivo ' . $prefijo . ' en el servidor.');
    }
    return $nombre;
}

/**
 * Formatea una fecha "desde hace cuánto" en texto humano.
 * Ej: hace 6 años / hace 8 meses / hace 1 año y 3 meses
 */
function tiempo_residiendo($fecha_desde) {
    $desde = new DateTime($fecha_desde);
    $hoy   = new DateTime('today');
    if ($desde > $hoy) {
        return 'menos de un mes';
    }
    $diff = $hoy->diff($desde);
    $anios = $diff->y;
    $meses = $diff->m;

    $partes = [];
    if ($anios > 0) {
        $partes[] = $anios . ($anios === 1 ? ' año' : ' años');
    }
    if ($meses > 0) {
        $partes[] = $meses . ($meses === 1 ? ' mes' : ' meses');
    }
    if (empty($partes)) {
        return 'menos de un mes';
    }
    return implode(' y ', $partes);
}
