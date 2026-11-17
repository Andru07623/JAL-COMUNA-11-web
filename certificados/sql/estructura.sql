-- ============================================================
--  Sistema de Certificados de Residencia - JAL Comuna 11
--  Estructura de base de datos
-- ============================================================
--  Cómo usarlo:
--   1. En phpMyAdmin (XAMPP o InfinityFree) crea una base de datos,
--      por ejemplo: jal_certificados
--   2. Selecciona esa base de datos.
--   3. Entra a la pestaña "SQL" y pega TODO este archivo. Ejecuta.
-- ============================================================

-- Para que los acentos y la ñ se guarden bien
SET NAMES utf8mb4;

-- ------------------------------------------------------------
--  Tabla de SOLICITUDES de certificado
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS solicitudes (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    consecutivo      VARCHAR(20)  DEFAULT NULL,   -- ej: 280526-06 (se asigna al APROBAR)

    -- Datos que envía el ciudadano desde el formulario
    nombre           VARCHAR(150) NOT NULL,
    tipo_doc         VARCHAR(10)  NOT NULL DEFAULT 'CC',  -- CC, TI, CE, PA
    documento        VARCHAR(30)  NOT NULL,
    direccion        VARCHAR(200) NOT NULL,
    barrio           VARCHAR(100) NOT NULL,
    barrio_otro      VARCHAR(100) DEFAULT NULL,   -- si eligió "Otro"
    fecha_residencia DATE         NOT NULL,       -- desde cuándo reside en la comuna 11
    celular          VARCHAR(30)  NOT NULL,
    correo           VARCHAR(150) NOT NULL,
    razon            TEXT         NOT NULL,        -- para qué solicita el certificado

    -- Adjuntos (se guardan rutas, no los archivos en la BD)
    cedula_path      VARCHAR(255) DEFAULT NULL,   -- cédula (ambos lados, 1 archivo)
    recibo_path      VARCHAR(255) DEFAULT NULL,   -- recibo EPM

    -- Flujo de aprobación
    estado           ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    dirigido_a       VARCHAR(150) DEFAULT NULL,   -- ej: SAPIENCIA (lo pone el funcionario al aprobar)
    motivo_rechazo   TEXT         DEFAULT NULL,
    pdf_path         VARCHAR(255) DEFAULT NULL,   -- ruta del certificado PDF generado
    atendido_por     INT          DEFAULT NULL,   -- id del usuario que aprobó/rechazó

    -- Auditoría (Ley 1581 - trazabilidad)
    ip_solicitante   VARCHAR(45)  DEFAULT NULL,
    creado_en        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resuelto_en      DATETIME     DEFAULT NULL,

    INDEX idx_estado (estado),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla de USUARIOS del panel administrativo (funcionarios)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    usuario        VARCHAR(50)  NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,          -- hash seguro (password_hash de PHP)
    nombre         VARCHAR(150) NOT NULL,
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso  DATETIME     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Contador de consecutivos por día
--  Sirve para generar el NN de "DDMMAA-NN" sin repetir.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS consecutivos (
    dia      DATE NOT NULL PRIMARY KEY,   -- fecha (un registro por día)
    contador INT  NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  NOTA IMPORTANTE SOBRE EL PRIMER USUARIO:
--  No creamos el usuario aquí porque la contraseña debe ir
--  "hasheada". Usa el archivo  admin/crear_usuario.php  (te lo
--  entrego en la Fase 3) para crear el primer funcionario de
--  forma segura. Luego ese archivo se borra.
-- ============================================================
