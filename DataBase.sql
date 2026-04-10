-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS CasaHogarDB;
USE CasaHogarDB;

-- ==========================================
-- 1. SEGURIDAD Y AUDITORÍA
-- ==========================================

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    estado TINYINT DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    estado TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_usuarios_estado ON usuarios(estado);

CREATE TABLE bitacora_sistema (
    id_bitacora INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT, -- Puede ser nulo si la acción fue del sistema
    accion VARCHAR(50) NOT NULL, -- Ej: INSERT, UPDATE, SOFT_DELETE, LOGIN
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT, -- ID del registro afectado
    descripcion TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bitacora_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE INDEX idx_bitacora_fecha ON bitacora_sistema(fecha_hora);
CREATE INDEX idx_bitacora_tabla ON bitacora_sistema(tabla_afectada);

-- ==========================================
-- 2. GESTIÓN DE PACIENTES Y PERSONAL
-- ==========================================

CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) DEFAULT 'DNI',
    numero_documento VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    contacto_emergencia VARCHAR(255),
    telefono_emergencia VARCHAR(20),
    condicion_medica TEXT,
    estado TINYINT DEFAULT 1, -- 1: Activo/Residente, 0: Inactivo/Alta/Fallecido
    fecha_ingreso DATE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_pacientes_documento ON pacientes(numero_documento);
CREATE INDEX idx_pacientes_estado ON pacientes(estado);

CREATE TABLE personal (
    id_personal INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(20) DEFAULT 'DNI',
    numero_documento VARCHAR(20) NOT NULL UNIQUE,
    cargo VARCHAR(100) NOT NULL,
    modalidad_contrato VARCHAR(50) NOT NULL, 
    telefono VARCHAR(20),
    estado TINYINT DEFAULT 1, -- 1: Activo, 0: Inactivo/Cesado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_personal_documento ON personal(numero_documento);
CREATE INDEX idx_personal_estado ON personal(estado);

-- ==========================================
-- 3. GESTIÓN DE TURNOS
-- ==========================================

CREATE TABLE asignacion_turnos (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    id_personal INT NOT NULL,
    id_usuario_asigno INT NOT NULL,
    fecha_turno DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado TINYINT DEFAULT 1, -- 1: Programado/Vigente, 0: Anulado/Cancelado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_turnos_personal FOREIGN KEY (id_personal) REFERENCES personal(id_personal) ON DELETE RESTRICT,
    CONSTRAINT fk_turnos_usuarios FOREIGN KEY (id_usuario_asigno) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_turnos_fecha ON asignacion_turnos(fecha_turno);
CREATE INDEX idx_turnos_personal ON asignacion_turnos(id_personal);

-- ==========================================
-- 4. CATÁLOGO DE SERVICIOS
-- ==========================================

CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    codigo_servicio VARCHAR(20) UNIQUE, -- Ej: SRV-001
    nombre_servicio VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio_referencial DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado TINYINT DEFAULT 1, -- 1: Disponible, 0: No disponible
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_servicios_estado ON servicios(estado);

-- ==========================================
-- 5. FACTURACIÓN
-- ==========================================

CREATE TABLE facturas (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_usuario_registro INT NOT NULL,
    serie VARCHAR(10),
    correlativo VARCHAR(20),
    fecha_emision DATETIME NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    impuestos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado TINYINT DEFAULT 1, -- 1: Emitida/Valida, 0: Anulada
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_facturas_pacientes FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE RESTRICT,
    CONSTRAINT fk_facturas_usuarios FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_facturas_paciente ON facturas(id_paciente);
CREATE INDEX idx_facturas_fecha ON facturas(fecha_emision);

CREATE TABLE detalle_factura (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_factura INT NOT NULL,
    id_servicio INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    estado TINYINT DEFAULT 1, -- 1: Activo, 0: Anulado (hereda de la factura general)
    CONSTRAINT fk_detalle_factura FOREIGN KEY (id_factura) REFERENCES facturas(id_factura) ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_detalle_factura ON detalle_factura(id_factura);

-- ==========================================
-- 6. CONTROL FINANCIERO (INGRESOS Y EGRESOS)
-- ==========================================

CREATE TABLE ingresos (
    id_ingreso INT AUTO_INCREMENT PRIMARY KEY,
    id_factura INT NULL, -- Puede ser NULL si es un ingreso no facturado directamente por el sistema
    id_usuario_registro INT NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL, -- Efectivo, Transferencia, etc.
    comprobante_referencia VARCHAR(100),
    fecha_ingreso DATETIME NOT NULL,
    estado TINYINT DEFAULT 1, -- 1: Válido, 0: Extornado/Anulado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ingresos_factura FOREIGN KEY (id_factura) REFERENCES facturas(id_factura) ON DELETE RESTRICT,
    CONSTRAINT fk_ingresos_usuarios FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_ingresos_fecha ON ingresos(fecha_ingreso);

CREATE TABLE egresos (
    id_egreso INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_registro INT NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    categoria VARCHAR(100), -- Ej: Planilla, Servicios Básicos, Insumos
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    comprobante_referencia VARCHAR(100),
    fecha_egreso DATETIME NOT NULL,
    estado TINYINT DEFAULT 1, -- 1: Válido, 0: Extornado/Anulado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_egresos_usuarios FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE INDEX idx_egresos_fecha ON egresos(fecha_egreso);

-- ==========================================
-- 7. MARKETING
-- ==========================================

CREATE TABLE prospectos_marketing (
    id_prospecto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_contacto VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    medio_contacto VARCHAR(50) DEFAULT 'Redes Sociales',
    interes_mostrado TEXT,
    estado_seguimiento VARCHAR(50) DEFAULT 'Pendiente',
    estado TINYINT DEFAULT 1, -- 1: Activo, 0: Inactivo/Eliminado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_prospectos_estado ON prospectos_marketing(estado);