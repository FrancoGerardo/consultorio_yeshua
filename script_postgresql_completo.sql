-- ============================================================================
-- CONSULTORIO MEDICO YESHUA - Esquema PostgreSQL consolidado
-- Refleja el estado FINAL de las 38 migraciones Laravel (2026_05_01 .. 2026_07_02)
-- Generado desde: database/migrations/
-- Nota: No incluye tabla migrations ni datos seed. Para Laravel use: php artisan migrate
-- ============================================================================

BEGIN;

-- ============================================================================
-- DROP (orden inverso de dependencias)
-- ============================================================================

DROP TABLE IF EXISTS configuracion_pagos CASCADE;
DROP TABLE IF EXISTS reportes_generados CASCADE;
DROP TABLE IF EXISTS medico_servicios CASCADE;
DROP TABLE IF EXISTS horarios_medicos CASCADE;
DROP TABLE IF EXISTS medico_especialidad CASCADE;
DROP TABLE IF EXISTS preferencias_tema CASCADE;
DROP TABLE IF EXISTS items_menu CASCADE;
DROP TABLE IF EXISTS rol_tiene_permisos CASCADE;
DROP TABLE IF EXISTS usuario_tiene_roles CASCADE;
DROP TABLE IF EXISTS usuario_tiene_permisos CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS permisos CASCADE;
DROP TABLE IF EXISTS visitas_paginas CASCADE;
DROP TABLE IF EXISTS auditoria CASCADE;
DROP TABLE IF EXISTS pagos CASCADE;
DROP TABLE IF EXISTS metodos_pago CASCADE;
DROP TABLE IF EXISTS planes_cuota CASCADE;
DROP TABLE IF EXISTS historiales_clinicos CASCADE;
DROP TABLE IF EXISTS seguimientos CASCADE;
DROP TABLE IF EXISTS fichas CASCADE;
DROP TABLE IF EXISTS servicios CASCADE;
DROP TABLE IF EXISTS especialidades CASCADE;
DROP TABLE IF EXISTS salas CASCADE;
DROP TABLE IF EXISTS clientes CASCADE;
DROP TABLE IF EXISTS medicos CASCADE;
DROP TABLE IF EXISTS secretarias CASCADE;
DROP TABLE IF EXISTS propietarios CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS personas CASCADE;
DROP TABLE IF EXISTS tokens_recuperacion CASCADE;
DROP TABLE IF EXISTS trabajos_fallidos CASCADE;
DROP TABLE IF EXISTS trabajos CASCADE;

-- ============================================================================
-- CREATE (orden de dependencias)
-- ============================================================================

-- 2026_05_01_000100_create_jobs_table.php
CREATE TABLE trabajos (
    id              BIGSERIAL PRIMARY KEY,
    queue           VARCHAR(255) NOT NULL,
    payload         TEXT NOT NULL,
    attempts        SMALLINT NOT NULL,
    reserved_at     INTEGER NULL,
    available_at    INTEGER NOT NULL,
    created_at      INTEGER NOT NULL
);

CREATE INDEX trabajos_queue_index ON trabajos (queue);

CREATE TABLE trabajos_fallidos (
    id          BIGSERIAL PRIMARY KEY,
    uuid        VARCHAR(255) NOT NULL,
    connection  TEXT NOT NULL,
    queue       TEXT NOT NULL,
    payload     TEXT NOT NULL,
    exception   TEXT NOT NULL,
    failed_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT trabajos_fallidos_uuid_unique UNIQUE (uuid)
);

-- 2026_05_04_000100_create_sessions_and_password_reset_tokens_table.php
-- (sessions no existe: SESSION_DRIVER=file)
CREATE TABLE tokens_recuperacion (
    email       VARCHAR(255) PRIMARY KEY,
    token       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL
);

-- 2026_05_03_000100_create_personas_table.php
CREATE TABLE personas (
    id                  VARCHAR(50) PRIMARY KEY,
    dni                 VARCHAR(20) NOT NULL,
    nombre              VARCHAR(100) NOT NULL,
    apellidos           VARCHAR(100) NOT NULL,
    telefono            VARCHAR(20) NULL,
    direccion           TEXT NULL,
    fecha_nacimiento    DATE NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT personas_dni_unique UNIQUE (dni)
);

-- 2026_05_06_000100_create_usuarios_table.php
-- 2026_05_29_000100_add_two_factor_columns_to_users_table.php
CREATE TABLE usuarios (
    id                          VARCHAR(50) PRIMARY KEY,
    persona_id                  VARCHAR(50) NOT NULL,
    email                       VARCHAR(100) NOT NULL,
    email_verified_at           TIMESTAMP NULL,
    password_hash               VARCHAR(255) NOT NULL,
    two_factor_secret           TEXT NULL,
    two_factor_recovery_codes   TEXT NULL,
    two_factor_confirmed_at     TIMESTAMP NULL,
    foto_url                    VARCHAR(255) NULL,
    tipo_usuario                VARCHAR(20) NOT NULL,
    estado                      BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_registro              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remember_token              VARCHAR(100) NULL,
    current_team_id             VARCHAR(50) NULL,
    profile_photo_path          VARCHAR(2048) NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT usuarios_persona_id_unique UNIQUE (persona_id),
    CONSTRAINT usuarios_email_unique UNIQUE (email),
    CONSTRAINT usuarios_tipo_usuario_check CHECK (
        tipo_usuario IN ('PROPIETARIO', 'SECRETARIA', 'MEDICO', 'CLIENTE')
    ),
    CONSTRAINT usuarios_persona_id_foreign
        FOREIGN KEY (persona_id) REFERENCES personas (id) ON DELETE CASCADE
);

CREATE INDEX usuarios_tipo_usuario_index ON usuarios (tipo_usuario);

-- 2026_05_08_000100_create_propietarios_table.php
CREATE TABLE propietarios (
    usuario_id      VARCHAR(50) PRIMARY KEY,
    nivel_acceso    VARCHAR(50) NOT NULL DEFAULT 'TOTAL',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT propietarios_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_05_09_000100_create_secretarias_table.php
CREATE TABLE secretarias (
    usuario_id  VARCHAR(50) PRIMARY KEY,
    turno       VARCHAR(50) NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    CONSTRAINT secretarias_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_05_11_000100_create_medicos_table.php
CREATE TABLE medicos (
    usuario_id          VARCHAR(50) PRIMARY KEY,
    codigo_cmp          VARCHAR(50) NULL,
    horario_atencion    TEXT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT medicos_codigo_cmp_unique UNIQUE (codigo_cmp),
    CONSTRAINT medicos_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_05_13_000100_create_clientes_table.php
CREATE TABLE clientes (
    usuario_id      VARCHAR(50) PRIMARY KEY,
    antecedentes    TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT clientes_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_05_14_000100_create_salas_table.php
CREATE TABLE salas (
    id              VARCHAR(50) PRIMARY KEY,
    numero          VARCHAR(20) NOT NULL,
    categoria       VARCHAR(50) NOT NULL,
    equipamiento    TEXT NULL,
    estado          VARCHAR(20) NOT NULL,
    capacidad       INTEGER NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT salas_numero_unique UNIQUE (numero),
    CONSTRAINT salas_estado_check CHECK (
        estado IN ('DISPONIBLE', 'OCUPADA', 'MANTENIMIENTO', 'INACTIVA')
    )
);

-- 2026_06_09_000100_create_especialidades_table.php
CREATE TABLE especialidades (
    id              VARCHAR(50) PRIMARY KEY,
    nombre          VARCHAR(50) NOT NULL,
    descripcion     VARCHAR(256) NULL,
    estado          VARCHAR(20) NOT NULL DEFAULT 'ACTIVA',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);

ALTER TABLE especialidades
    ADD CONSTRAINT especialidades_estado_check
    CHECK (estado IN ('ACTIVA', 'INACTIVA'));

-- 2026_05_16_000100_create_servicios_table.php
-- 2026_06_14_000100_add_especialidad_id_to_servicios_table.php
-- 2026_06_19_000100_add_meedico_id_to_servicios_table.php
CREATE TABLE servicios (
    id                  VARCHAR(50) PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL,
    descripcion         TEXT NULL,
    categoria           VARCHAR(20) NOT NULL,
    especialidad_id     VARCHAR(50) NULL,
    meedico_id          VARCHAR(50) NULL,
    costo               DECIMAL(10, 2) NOT NULL,
    duracion_minutos    INTEGER NULL,
    estado              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT servicios_categoria_check CHECK (
        categoria IN ('INTERNACION', 'ESPECIALIDAD', 'ENFERMERIA')
    ),
    CONSTRAINT servicios_especialidad_id_foreign
        FOREIGN KEY (especialidad_id) REFERENCES especialidades (id) ON DELETE SET NULL,
    CONSTRAINT servicios_meedico_id_foreign
        FOREIGN KEY (meedico_id) REFERENCES usuarios (id) ON DELETE SET NULL
);

-- 2026_05_18_000100_create_fichas_table.php
-- 2026_06_25_000100_agregar_control_flujo_fichas.php
-- 2026_06_29_000100_agregar_estados_pago_fichas.php
CREATE TABLE fichas (
    id                      VARCHAR(50) PRIMARY KEY,
    cliente_id              VARCHAR(50) NOT NULL,
    servicio_id             VARCHAR(50) NOT NULL,
    medico_id               VARCHAR(50) NOT NULL,
    sala_id                 VARCHAR(50) NULL,
    fecha                   DATE NOT NULL,
    hora                    TIME NOT NULL,
    estado                  VARCHAR(20) NOT NULL,
    fecha_confirmacion      TIMESTAMP NULL,
    fecha_llegada           TIMESTAMP NULL,
    fecha_inicio_atencion   TIMESTAMP NULL,
    fecha_fin_atencion      TIMESTAMP NULL,
    tiempo_espera_minutos   INTEGER NULL,
    tiempo_atencion_minutos INTEGER NULL,
    motivo_consulta         TEXT NULL,
    observaciones_internas  TEXT NULL,
    created_at              TIMESTAMP NULL,
    updated_at              TIMESTAMP NULL,
    CONSTRAINT fichas_estado_check CHECK (
        estado IN (
            'PENDIENTE_PAGO',
            'ANTICIPO_PAGADO',
            'PAGADA_COMPLETA',
            'EN_ESPERA',
            'EN_ATENCION',
            'ATENDIDA',
            'CANCELADA',
            'NO_ASISTIO',
            'PENDIENTE',
            'CONFIRMADA'
        )
    ),
    CONSTRAINT fichas_cliente_id_foreign
        FOREIGN KEY (cliente_id) REFERENCES clientes (usuario_id) ON DELETE CASCADE,
    CONSTRAINT fichas_servicio_id_foreign
        FOREIGN KEY (servicio_id) REFERENCES servicios (id) ON DELETE CASCADE,
    CONSTRAINT fichas_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES medicos (usuario_id) ON DELETE CASCADE,
    CONSTRAINT fichas_sala_id_foreign
        FOREIGN KEY (sala_id) REFERENCES salas (id) ON DELETE SET NULL
);

-- 2026_05_19_000100_create_seguimientos_table.php
-- 2026_06_27_000100_agregar_auditoria_seguimientos.php
CREATE TABLE seguimientos (
    id                          VARCHAR(50) PRIMARY KEY,
    ficha_id                    VARCHAR(50) NOT NULL,
    medico_id                   VARCHAR(50) NULL,
    tipo                        VARCHAR(20) NOT NULL,
    estado                      VARCHAR(20) NOT NULL DEFAULT 'ACTIVO',
    fecha                       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    signos_vitales              JSON NULL,
    motivo_consulta             TEXT NULL,
    nivel_urgencia              VARCHAR(20) NULL,
    diagnostico                 TEXT NULL,
    codigo_cie10                VARCHAR(20) NULL,
    observaciones               TEXT NULL,
    tratamiento_prescrito       TEXT NULL,
    examenes_solicitados        JSON NULL,
    interconsultas              JSON NULL,
    proxima_cita                DATE NULL,
    indicaciones_proxima_cita   TEXT NULL,
    medicamentos                JSON NULL,
    ip_registro                 VARCHAR(45) NULL,
    navegador                   VARCHAR(255) NULL,
    firma_digital               TEXT NULL,
    fecha_firma                 TIMESTAMP NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT seguimientos_tipo_check CHECK (
        tipo IN ('TRIAGE', 'CONSULTA', 'TRATAMIENTO')
    ),
    CONSTRAINT seguimientos_nivel_urgencia_check CHECK (
        nivel_urgencia IS NULL OR nivel_urgencia IN ('BAJA', 'MEDIA', 'ALTA', 'URGENTE')
    ),
    CONSTRAINT seguimientos_ficha_id_foreign
        FOREIGN KEY (ficha_id) REFERENCES fichas (id) ON DELETE CASCADE,
    CONSTRAINT seguimientos_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES medicos (usuario_id) ON DELETE SET NULL
);

-- 2026_05_21_000100_create_historiales_clinicos_table.php
-- 2026_06_24_000100_agregar_campos_criticos_historiales_clinicos.php
CREATE TABLE historiales_clinicos (
    id                          VARCHAR(50) PRIMARY KEY,
    cliente_id                  VARCHAR(50) NOT NULL,
    grupo_sanguineo             VARCHAR(5) NULL,
    factor_rh                   VARCHAR(10) NULL,
    alergias                    TEXT NULL,
    enfermedades_cronicas       TEXT NULL,
    antecedentes_quirurgicos    TEXT NULL,
    antecedentes_familiares     TEXT NULL,
    antecedentes_personales     TEXT NULL,
    peso_habitual               DECIMAL(5, 2) NULL,
    estatura                    DECIMAL(5, 2) NULL,
    habitos                     JSON NULL,
    vacunas                     JSON NULL,
    transfusiones_previas       TEXT NULL,
    hospitalizaciones_previas   TEXT NULL,
    notas_importantes           TEXT NULL,
    medicamentos_habituales     TEXT NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT historiales_clinicos_cliente_id_unique UNIQUE (cliente_id),
    CONSTRAINT historiales_clinicos_cliente_id_foreign
        FOREIGN KEY (cliente_id) REFERENCES clientes (usuario_id) ON DELETE CASCADE
);

-- 2026_05_23_000100_create_planes_cuota_table.php
CREATE TABLE planes_cuota (
    id              VARCHAR(50) PRIMARY KEY,
    ficha_id        VARCHAR(50) NOT NULL,
    numero_cuotas   INTEGER NOT NULL,
    monto_total     DECIMAL(10, 2) NOT NULL,
    monto_cuota     DECIMAL(10, 2) NOT NULL,
    fecha_inicio    DATE NOT NULL,
    intervalo_dias  INTEGER NOT NULL DEFAULT 30,
    estado          VARCHAR(20) NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT planes_cuota_estado_check CHECK (
        estado IN ('ACTIVO', 'PAGADO', 'MOROSO', 'CANCELADO')
    ),
    CONSTRAINT planes_cuota_ficha_id_foreign
        FOREIGN KEY (ficha_id) REFERENCES fichas (id) ON DELETE CASCADE
);

-- 2026_06_05_000100_create_metodo_pagos_table.php
CREATE TABLE metodos_pago (
    id                          VARCHAR(50) PRIMARY KEY,
    usuario_id                  VARCHAR(50) NOT NULL,
    tipo                        VARCHAR(50) NOT NULL,
    titular                     VARCHAR(100) NULL,
    numero_tarjeta_enmascarado  VARCHAR(50) NULL,
    banco                       VARCHAR(100) NULL,
    numero_cuenta               VARCHAR(50) NULL,
    datos_adicionales           JSON NULL,
    activo                      BOOLEAN NOT NULL DEFAULT TRUE,
    predeterminado              BOOLEAN NOT NULL DEFAULT FALSE,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT metodos_pago_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

CREATE INDEX metodos_pago_usuario_id_index ON metodos_pago (usuario_id);
CREATE INDEX metodos_pago_activo_index ON metodos_pago (activo);

-- 2026_05_24_000100_create_pagos_table.php
-- 2026_06_07_000100_add_metodo_pago_id_to_pagos_table.php
-- 2026_06_20_000100_add_pagofacil_fields_to_pagos_table.php
-- 2026_06_30_000100_agregar_concepto_pagos.php
CREATE TABLE pagos (
    id                          VARCHAR(50) PRIMARY KEY,
    plan_cuota_id               VARCHAR(50) NULL,
    ficha_id                    VARCHAR(50) NOT NULL,
    metodo_pago_id              VARCHAR(50) NULL,
    monto                       DECIMAL(10, 2) NOT NULL,
    tipo                        VARCHAR(20) NOT NULL,
    concepto                    VARCHAR(20) NOT NULL DEFAULT 'TOTAL',
    numero_cuota                INTEGER NULL,
    fecha_pago                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metodo_pago                 VARCHAR(20) NOT NULL,
    comprobante_url             VARCHAR(255) NULL,
    pagofacil_transaction_id    VARCHAR(100) NULL,
    company_transaction_id      VARCHAR(100) NULL,
    qr_base64                   TEXT NULL,
    qr_status                   VARCHAR(20) NULL DEFAULT 'PENDING',
    qr_expiration               TIMESTAMP NULL,
    estado                      VARCHAR(20) NOT NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT pagos_tipo_check CHECK (
        tipo IN ('CONTADO', 'CUOTA', 'ABONO')
    ),
    CONSTRAINT pagos_concepto_check CHECK (
        concepto IN ('ANTICIPO', 'SALDO', 'CUOTA', 'ABONO', 'TOTAL')
    ),
    CONSTRAINT pagos_metodo_pago_check CHECK (
        metodo_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'QR')
    ),
    CONSTRAINT pagos_estado_check CHECK (
        estado IN ('PENDIENTE', 'PAGADO', 'ANULADO')
    ),
    CONSTRAINT pagos_plan_cuota_id_foreign
        FOREIGN KEY (plan_cuota_id) REFERENCES planes_cuota (id) ON DELETE SET NULL,
    CONSTRAINT pagos_ficha_id_foreign
        FOREIGN KEY (ficha_id) REFERENCES fichas (id) ON DELETE CASCADE,
    CONSTRAINT pagos_metodo_pago_id_foreign
        FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago (id) ON DELETE SET NULL
);

-- 2026_05_26_000100_create_auditoria_table.php
CREATE TABLE auditoria (
    id                  BIGSERIAL PRIMARY KEY,
    tabla_afectada      VARCHAR(50) NOT NULL,
    registro_id         VARCHAR(50) NOT NULL,
    accion              VARCHAR(10) NOT NULL,
    usuario_id          VARCHAR(50) NULL,
    datos_anteriores    JSON NULL,
    datos_nuevos        JSON NULL,
    fecha               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT auditoria_accion_check CHECK (
        accion IN ('INSERT', 'UPDATE', 'DELETE')
    ),
    CONSTRAINT auditoria_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
);

CREATE INDEX auditoria_tabla_afectada_registro_id_index
    ON auditoria (tabla_afectada, registro_id);

-- 2026_05_28_000100_create_visitas_paginas_table.php
CREATE TABLE visitas_paginas (
    id              BIGSERIAL PRIMARY KEY,
    ruta            VARCHAR(255) NOT NULL,
    nombre_pagina   VARCHAR(255) NULL,
    usuario_id      VARCHAR(50) NULL,
    ip              VARCHAR(45) NULL,
    user_agent      VARCHAR(255) NULL,
    fecha_visita    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT visitas_paginas_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
);

CREATE INDEX visitas_paginas_ruta_index ON visitas_paginas (ruta);
CREATE INDEX visitas_paginas_fecha_visita_index ON visitas_paginas (fecha_visita);

-- 2026_05_31_000100_create_permission_tables.php + config/permission.php (teams=false)
CREATE TABLE permisos (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    guard_name  VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    CONSTRAINT permisos_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE roles (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    guard_name  VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE usuario_tiene_permisos (
    permission_id   BIGINT NOT NULL,
    model_type      VARCHAR(255) NOT NULL,
    model_id        VARCHAR(50) NOT NULL,
    CONSTRAINT usuario_tiene_permisos_permission_model_type_primary
        PRIMARY KEY (permission_id, model_id, model_type),
    CONSTRAINT usuario_tiene_permisos_permission_id_foreign
        FOREIGN KEY (permission_id) REFERENCES permisos (id) ON DELETE CASCADE
);

CREATE INDEX usuario_tiene_permisos_model_id_model_type_index
    ON usuario_tiene_permisos (model_id, model_type);

CREATE TABLE usuario_tiene_roles (
    role_id     BIGINT NOT NULL,
    model_type  VARCHAR(255) NOT NULL,
    model_id    VARCHAR(50) NOT NULL,
    CONSTRAINT usuario_tiene_roles_role_model_type_primary
        PRIMARY KEY (role_id, model_id, model_type),
    CONSTRAINT usuario_tiene_roles_role_id_foreign
        FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

CREATE INDEX usuario_tiene_roles_model_id_model_type_index
    ON usuario_tiene_roles (model_id, model_type);

CREATE TABLE rol_tiene_permisos (
    permission_id   BIGINT NOT NULL,
    role_id         BIGINT NOT NULL,
    CONSTRAINT rol_tiene_permisos_permission_id_role_id_primary
        PRIMARY KEY (permission_id, role_id),
    CONSTRAINT rol_tiene_permisos_permission_id_foreign
        FOREIGN KEY (permission_id) REFERENCES permisos (id) ON DELETE CASCADE,
    CONSTRAINT rol_tiene_permisos_role_id_foreign
        FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

-- 2026_06_02_000100_create_item_menus_table.php
CREATE TABLE items_menu (
    id                  VARCHAR(50) PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL,
    ruta                VARCHAR(255) NOT NULL,
    icono               VARCHAR(100) NULL,
    orden               INTEGER NOT NULL DEFAULT 0,
    permiso_requerido   VARCHAR(100) NULL,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    item_padre_id       VARCHAR(50) NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT items_menu_item_padre_id_foreign
        FOREIGN KEY (item_padre_id) REFERENCES items_menu (id) ON DELETE CASCADE
);

CREATE INDEX items_menu_orden_index ON items_menu (orden);
CREATE INDEX items_menu_activo_index ON items_menu (activo);

-- 2026_06_04_000100_create_preferencia_temas_table.php -> tabla: preferencias_tema
CREATE TABLE preferencias_tema (
    id              VARCHAR(50) PRIMARY KEY,
    usuario_id      VARCHAR(50) NOT NULL,
    tema            VARCHAR(20) NOT NULL DEFAULT 'adultos',
    modo            VARCHAR(20) NOT NULL DEFAULT 'dia',
    "tamaño_fuente" VARCHAR(20) NOT NULL DEFAULT 'normal',
    contraste       VARCHAR(20) NOT NULL DEFAULT 'normal',
    modo_auto       BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT preferencias_tema_usuario_id_unique UNIQUE (usuario_id),
    CONSTRAINT preferencias_tema_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_06_10_000100_create_medico_especialidad_table.php
-- 2026_06_17_000100_fix_medico_especialidad_foreign_key.php
CREATE TABLE medico_especialidad (
    id                  VARCHAR(50) PRIMARY KEY,
    medico_id           VARCHAR(50) NOT NULL,
    especialidad_id   VARCHAR(50) NOT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT medico_especialidad_medico_id_especialidad_id_unique
        UNIQUE (medico_id, especialidad_id),
    CONSTRAINT medico_especialidad_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT medico_especialidad_especialidad_id_foreign
        FOREIGN KEY (especialidad_id) REFERENCES especialidades (id) ON DELETE CASCADE
);

-- 2026_06_12_000100_create_horarios_medicos_table.php
CREATE TABLE horarios_medicos (
    id              VARCHAR(50) PRIMARY KEY,
    medico_id       VARCHAR(50) NOT NULL,
    dia_semana      VARCHAR(20) NOT NULL,
    hora_inicio     TIME NOT NULL,
    hora_fin        TIME NOT NULL,
    activo          BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT horarios_medicos_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES medicos (usuario_id) ON DELETE CASCADE
);

ALTER TABLE horarios_medicos
    ADD CONSTRAINT horarios_medicos_dia_semana_check
    CHECK (dia_semana IN ('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'));

-- 2026_06_15_000100_create_medico_servicios_table.php
CREATE TABLE medico_servicios (
    id              VARCHAR(50) PRIMARY KEY,
    medico_id       VARCHAR(50) NOT NULL,
    servicio_id     VARCHAR(50) NOT NULL,
    activo          BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT medico_servicios_medico_id_servicio_id_unique
        UNIQUE (medico_id, servicio_id),
    CONSTRAINT medico_servicios_medico_id_foreign
        FOREIGN KEY (medico_id) REFERENCES medicos (usuario_id) ON DELETE CASCADE,
    CONSTRAINT medico_servicios_servicio_id_foreign
        FOREIGN KEY (servicio_id) REFERENCES servicios (id) ON DELETE CASCADE
);

-- 2026_06_22_000100_create_reportes_generados_table.php
CREATE TABLE reportes_generados (
    id                  UUID PRIMARY KEY,
    nombre              VARCHAR(255) NOT NULL,
    tipo                VARCHAR(255) NOT NULL,
    filtros             JSON NULL,
    formato             VARCHAR(255) NOT NULL,
    archivo_path        VARCHAR(255) NULL,
    estado              VARCHAR(255) NOT NULL DEFAULT 'generando',
    usuario_id          VARCHAR(50) NOT NULL,
    fecha_generacion    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    CONSTRAINT reportes_generados_usuario_id_foreign
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

-- 2026_07_02_000100_create_configuracion_pagos_table.php
CREATE TABLE configuracion_pagos (
    id                          VARCHAR(50) PRIMARY KEY,
    servicio_id                 VARCHAR(50) NOT NULL,
    porcentaje_anticipo_minimo  INTEGER NOT NULL DEFAULT 50,
    permite_pago_total          BOOLEAN NOT NULL DEFAULT TRUE,
    descuento_pago_total        DECIMAL(5, 2) NOT NULL DEFAULT 5.00,
    permite_plan_cuotas         BOOLEAN NOT NULL DEFAULT FALSE,
    monto_minimo_cuotas         DECIMAL(10, 2) NULL,
    porcentaje_anticipo_cuotas  INTEGER NOT NULL DEFAULT 30,
    max_cuotas                  INTEGER NOT NULL DEFAULT 12,
    intervalo_dias_cuota        INTEGER NOT NULL DEFAULT 30,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    CONSTRAINT configuracion_pagos_servicio_id_foreign
        FOREIGN KEY (servicio_id) REFERENCES servicios (id) ON DELETE CASCADE
);

CREATE INDEX configuracion_pagos_servicio_id_index ON configuracion_pagos (servicio_id);

COMMIT;

-- ============================================================================
-- RESUMEN: 32 tablas
-- ============================================================================
-- trabajos, trabajos_fallidos, tokens_recuperacion, personas, usuarios,
-- propietarios, secretarias, medicos, clientes, salas, especialidades, servicios,
-- fichas, seguimientos, historiales_clinicos, planes_cuota, metodos_pago, pagos,
-- auditoria, visitas_paginas, permisos, roles, usuario_tiene_permisos,
-- usuario_tiene_roles, rol_tiene_permisos, items_menu, preferencias_tema,
-- medico_especialidad, horarios_medicos, medico_servicios, reportes_generados,
-- configuracion_pagos
--
-- NO incluidas (por diseño del proyecto):
-- - sessions (SESSION_DRIVER=file)
-- - job_batches
-- - reportes (tabla antigua eliminada)
-- ============================================================================
