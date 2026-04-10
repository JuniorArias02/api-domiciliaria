CREATE TABLE `aseguradoras` (
  `id_aseguradora` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo_habilitacion` varchar(20) COMMENT 'Código de habilitación ante la Supersalud',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `barrios` (
  `id_barrio` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_comuna` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `cargos` (
  `id_cargo` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `catalogo_equipos` (
  `id_equipo` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `categoria` varchar(80) COMMENT 'Ej: Ventilación, Monitoreo, Movilización',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `comunas` (
  `id_comuna` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_zona` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `cuidadores` (
  `id_cuidador` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `parentesco` varchar(50) COMMENT 'Ej: Hijo, Cónyuge, Hermano',
  `telefono` varchar(50),
  `email` varchar(150),
  `es_principal` tinyint(1) NOT NULL DEFAULT '1',
  `tipo_auxiliar` ENUM ('CUI', 'ENF', 'AU', 'OTRO') COMMENT 'Tipo de auxiliar requerido',
  `horas_diarias` tinyint COMMENT 'Horas requeridas de acompañamiento: 8, 12, 24',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `detalle_solicitud_equipos` (
  `id_detalle` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_solicitud` int NOT NULL,
  `id_equipo` int NOT NULL,
  `cantidad` tinyint NOT NULL DEFAULT '1',
  `observacion` varchar(300)
);

CREATE TABLE `diagnosticos_cie10` (
  `codigo` varchar(10) PRIMARY KEY NOT NULL,
  `descripcion` text NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
);

CREATE TABLE `especialidades` (
  `id_especialidad` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `abreviatura` varchar(10) COMMENT 'Ej: TF, TO, TL, M, N',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `laboratorios` (
  `id_laboratorio` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `id_orden_asociada` int COMMENT 'Orden médica que genera el laboratorio',
  `id_personal_toma` int COMMENT 'Auxiliar que realiza la toma',
  `id_usuario_solicita` int COMMENT 'Madrina que registra la solicitud',
  `fecha_solicitud` date NOT NULL,
  `fecha_toma_programada` datetime,
  `fecha_toma_real` datetime,
  `estado` ENUM ('PENDIENTE', 'PROGRAMADO', 'REALIZADO', 'CANCELADO', 'NO_APLICA') NOT NULL DEFAULT 'PENDIENTE',
  `confirmacion_toma` tinyint(1) COMMENT '1=Confirmado, 0=No confirmado',
  `observaciones` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `logs_acceso` (
  `id_log` bigint PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `accion` varchar(50) NOT NULL DEFAULT 'LOGIN' COMMENT 'LOGIN, LOGOUT, FAILED_LOGIN',
  `ip_origen` varchar(45) COMMENT 'Soporta IPv4 e IPv6',
  `dispositivo` varchar(255),
  `user_agent` varchar(500),
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `migrations` (
  `id` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
);

CREATE TABLE `ordenes_medicas` (
  `id_orden` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `id_especialidad` int NOT NULL,
  `id_personal_ordena` int COMMENT 'Médico o profesional que genera la orden',
  `fecha_orden` date NOT NULL,
  `numero_sesiones` smallint NOT NULL DEFAULT '1',
  `frecuencia_dias` smallint NOT NULL DEFAULT '0' COMMENT 'Cada cuántos días se realiza la sesión',
  `numero_mipres` varchar(100) COMMENT 'Número MIPRES para prescripciones',
  `observacion` text,
  `estado` ENUM ('VIGENTE', 'SUSPENDIDA', 'VENCIDA', 'FINALIZADA', 'CANCELADA') NOT NULL DEFAULT 'VIGENTE',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `paciente_diagnosticos` (
  `id_paciente` int NOT NULL,
  `codigo_cie10` varchar(10) NOT NULL,
  `tipo_diagnostico` ENUM ('DOMICILIARIO', 'RIESGO_CARDIOVASCULAR', 'COMORBILIDAD', 'SECUNDARIO') NOT NULL DEFAULT 'DOMICILIARIO',
  `id_visita` int NOT NULL DEFAULT '0',
  `es_principal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Diagnóstico principal',
  `fecha_registro` date,
  `observacion` varchar(500),
  PRIMARY KEY (`id_paciente`, `codigo_cie10`, `tipo_diagnostico`, `id_visita`)
);

CREATE TABLE `pacientes` (
  `id_paciente` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `tipo_documento` ENUM ('CC', 'TI', 'CE', 'PA', 'RC', 'MS', 'AS') NOT NULL DEFAULT 'CC',
  `identificacion` varchar(20) NOT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` ENUM ('M', 'F', 'N') NOT NULL COMMENT 'M=Masculino, F=Femenino, N=No Binario/No Especificado',
  `telefono` varchar(50) COMMENT 'Puede incluir varios números separados',
  `email` varchar(150),
  `id_aseguradora` int NOT NULL,
  `regimen` varchar(50) NOT NULL DEFAULT 'CONTRIBUTIVO',
  `id_madrina` int COMMENT 'FK a usuarios: madrina responsable del paciente',
  `fecha_ingreso` date,
  `direccion` varchar(255) NOT NULL,
  `id_barrio` int,
  `latitud` decimal(10,8),
  `longitud` decimal(11,8),
  `url_google_maps` text,
  `estado` ENUM ('ACTIVO', 'INACTIVO', 'EGRESADO', 'FALLECIDO', 'SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `personal` (
  `id_personal` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_cargo` int NOT NULL,
  `id_especialidad` int COMMENT 'NULL para personal no clínico (aux, admin)',
  `nombre_completo` varchar(250) NOT NULL,
  `numero_documento` varchar(20),
  `tipo_documento` varchar(10) DEFAULT 'CC',
  `tarjeta_profesional` varchar(50) COMMENT 'Registro profesional ante Min-Salud',
  `telefono` varchar(20),
  `email` varchar(150),
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activo, 0=Inactivo',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `roles` (
  `id_rol` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `servicios` (
  `id_servicio` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `codigo_servicio` varchar(50) NOT NULL,
  `nombre_servicio` varchar(255) NOT NULL,
  `descripcion` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `solicitudes_equipos` (
  `id_solicitud` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `id_usuario_gestiona` int COMMENT 'Madrina que gestiona la solicitud',
  `modalidad` ENUM ('PROPIO', 'ALQUILER', 'MIXTO'),
  `tiempo_requerido` ENUM ('CONTINUO', 'TEMPORAL', 'POR_EVENTO'),
  `estado` ENUM ('PENDIENTE', 'APROBADA', 'ENTREGADA', 'DEVUELTA', 'CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  `fecha_solicitud` date NOT NULL DEFAULT (curdate()),
  `fecha_entrega` date,
  `fecha_devolucion_esperada` date,
  `fecha_devolucion_real` date,
  `observaciones` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `telexperticias` (
  `id_telexperticia` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `id_especialidad` int NOT NULL COMMENT 'Especialidad a la que se solicita la interconsulta',
  `id_usuario_solicita` int,
  `fecha_solicitud` date NOT NULL,
  `frecuencia_dias` smallint,
  `estado` ENUM ('SOLICITADA', 'PROGRAMADA', 'REALIZADA', 'CANCELADA') NOT NULL DEFAULT 'SOLICITADA',
  `observaciones` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `tutelas` (
  `id_tutela` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_paciente` int NOT NULL,
  `numero_tutela` varchar(100) NOT NULL,
  `fecha_tutela` date,
  `prestacion_autorizada` tinyint(1) NOT NULL DEFAULT '0',
  `es_permanente` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Tutela permanente (sin fecha fin)',
  `duracion_dias` int COMMENT 'Solo aplica si es_permanente = 0',
  `observaciones` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `usuarios` (
  `id_usuario` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activo, 0=Inactivo',
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `visitas_domiciliarias` (
  `id_visita` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `codigo_ingreso` varchar(20),
  `id_orden_asociada` int COMMENT 'Orden que originó la visita (nullable para visitas de control)',
  `id_paciente` int NOT NULL,
  `id_personal` int NOT NULL COMMENT 'Profesional asignado para ejecutar la visita',
  `id_especialidad` int NOT NULL COMMENT 'Desnormalización controlada para queries directas por especialidad',
  `fecha_programada` datetime NOT NULL,
  `id_usuario_programa` int COMMENT 'Madrina que programa la visita',
  `fecha_realizada` datetime,
  `latitud_checkin` decimal(10,8),
  `longitud_checkin` decimal(11,8),
  `latitud_checkout` decimal(10,8),
  `longitud_checkout` decimal(11,8),
  `estado` ENUM ('PROGRAMADA', 'COMPLETADA', 'CANCELADA', 'REPROGRAMADA', 'NO_ATENDIDA') NOT NULL DEFAULT 'PROGRAMADA',
  `motivo_cancelacion` varchar(255),
  `observaciones` text,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `tipo_atencion_ext` varchar(100),
  `remitido_a` varchar(255),
  `id_servicio` int
);

CREATE TABLE `zonas` (
  `id_zona` int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE UNIQUE INDEX `nombre` ON `aseguradoras` (`nombre`);

CREATE UNIQUE INDEX `uq_barrio_comuna` ON `barrios` (`id_comuna`, `nombre`);

CREATE UNIQUE INDEX `nombre` ON `cargos` (`nombre`);

CREATE UNIQUE INDEX `nombre` ON `catalogo_equipos` (`nombre`);

CREATE UNIQUE INDEX `uq_comuna_zona` ON `comunas` (`id_zona`, `nombre`);

CREATE INDEX `idx_cuidadores_paciente` ON `cuidadores` (`id_paciente`);

CREATE UNIQUE INDEX `uq_solicitud_equipo` ON `detalle_solicitud_equipos` (`id_solicitud`, `id_equipo`);

CREATE INDEX `fk_dse_equ` ON `detalle_solicitud_equipos` (`id_equipo`);

CREATE UNIQUE INDEX `nombre` ON `especialidades` (`nombre`);

CREATE INDEX `fk_lab_ord` ON `laboratorios` (`id_orden_asociada`);

CREATE INDEX `fk_lab_per` ON `laboratorios` (`id_personal_toma`);

CREATE INDEX `fk_lab_usu` ON `laboratorios` (`id_usuario_solicita`);

CREATE INDEX `idx_lab_paciente` ON `laboratorios` (`id_paciente`);

CREATE INDEX `idx_lab_estado` ON `laboratorios` (`estado`);

CREATE INDEX `idx_logs_usuario` ON `logs_acceso` (`id_usuario`);

CREATE INDEX `idx_logs_fecha` ON `logs_acceso` (`created_at`);

CREATE INDEX `fk_ord_per` ON `ordenes_medicas` (`id_personal_ordena`);

CREATE INDEX `idx_ord_paciente` ON `ordenes_medicas` (`id_paciente`);

CREATE INDEX `idx_ord_especialidad` ON `ordenes_medicas` (`id_especialidad`);

CREATE INDEX `idx_ord_estado` ON `ordenes_medicas` (`estado`);

CREATE INDEX `idx_pdiag_cie10` ON `paciente_diagnosticos` (`codigo_cie10`);

CREATE UNIQUE INDEX `identificacion` ON `pacientes` (`identificacion`);

CREATE INDEX `fk_pac_barrio` ON `pacientes` (`id_barrio`);

CREATE INDEX `idx_pac_identificacion` ON `pacientes` (`identificacion`);

CREATE INDEX `idx_pac_madrina` ON `pacientes` (`id_madrina`);

CREATE INDEX `idx_pac_estado` ON `pacientes` (`estado`);

CREATE INDEX `idx_pac_geo` ON `pacientes` (`latitud`, `longitud`);

CREATE INDEX `idx_pac_aseguradora` ON `pacientes` (`id_aseguradora`);

CREATE UNIQUE INDEX `numero_documento` ON `personal` (`numero_documento`);

CREATE INDEX `fk_per_esp` ON `personal` (`id_especialidad`);

CREATE INDEX `idx_personal_nombre` ON `personal` (`nombre_completo`);

CREATE INDEX `idx_personal_documento` ON `personal` (`numero_documento`);

CREATE INDEX `idx_personal_cargo` ON `personal` (`id_cargo`);

CREATE UNIQUE INDEX `nombre` ON `roles` (`nombre`);

CREATE UNIQUE INDEX `servicios_codigo_servicio_unique` ON `servicios` (`codigo_servicio`);

CREATE INDEX `fk_sol_usu` ON `solicitudes_equipos` (`id_usuario_gestiona`);

CREATE INDEX `idx_sol_paciente` ON `solicitudes_equipos` (`id_paciente`);

CREATE INDEX `idx_sol_estado` ON `solicitudes_equipos` (`estado`);

CREATE INDEX `fk_tel_esp` ON `telexperticias` (`id_especialidad`);

CREATE INDEX `fk_tel_usu` ON `telexperticias` (`id_usuario_solicita`);

CREATE INDEX `idx_tel_paciente` ON `telexperticias` (`id_paciente`);

CREATE INDEX `fk_tut_pac` ON `tutelas` (`id_paciente`);

CREATE UNIQUE INDEX `email` ON `usuarios` (`email`);

CREATE INDEX `idx_usuarios_email` ON `usuarios` (`email`);

CREATE INDEX `idx_usuarios_rol` ON `usuarios` (`id_rol`);

CREATE UNIQUE INDEX `visitas_domiciliarias_codigo_ingreso_unique` ON `visitas_domiciliarias` (`codigo_ingreso`);

CREATE INDEX `fk_vis_per` ON `visitas_domiciliarias` (`id_personal`);

CREATE INDEX `fk_vis_usu` ON `visitas_domiciliarias` (`id_usuario_programa`);

CREATE INDEX `idx_vis_agenda` ON `visitas_domiciliarias` (`fecha_programada`, `id_personal`);

CREATE INDEX `idx_vis_paciente` ON `visitas_domiciliarias` (`id_paciente`);

CREATE INDEX `idx_vis_orden` ON `visitas_domiciliarias` (`id_orden_asociada`);

CREATE INDEX `idx_vis_estado` ON `visitas_domiciliarias` (`estado`);

CREATE INDEX `idx_vis_especialidad` ON `visitas_domiciliarias` (`id_especialidad`);

CREATE INDEX `visitas_domiciliarias_id_servicio_foreign` ON `visitas_domiciliarias` (`id_servicio`);

CREATE UNIQUE INDEX `nombre` ON `zonas` (`nombre`);

ALTER TABLE `aseguradoras` COMMENT = 'EPS y entidades aseguradoras';

ALTER TABLE `barrios` COMMENT = 'División geográfica de tercer nivel';

ALTER TABLE `cargos` COMMENT = 'Cargos del personal clínico y auxiliar (renombrado de pf_cargo)';

ALTER TABLE `catalogo_equipos` COMMENT = 'Catálogo de equipos biomédicos disponibles';

ALTER TABLE `comunas` COMMENT = 'División geográfica de segundo nivel';

ALTER TABLE `cuidadores` COMMENT = 'Familiares y cuidadores del paciente en el domicilio';

ALTER TABLE `detalle_solicitud_equipos` COMMENT = 'Detalle de equipos por solicitud (líneas de pedido)';

ALTER TABLE `diagnosticos_cie10` COMMENT = 'Catálogo CIE-10 internacional de diagnósticos';

ALTER TABLE `especialidades` COMMENT = 'Especialidades clínicas (Terapia Física, Nutrición, Medicina, etc.)';

ALTER TABLE `laboratorios` COMMENT = 'Laboratorios clínicos: solicitud y toma de muestras domiciliarias';

ALTER TABLE `logs_acceso` COMMENT = 'Trazabilidad de accesos al sistema';

ALTER TABLE `ordenes_medicas` COMMENT = 'Órdenes médicas: autorizan sesiones de atención por especialidad';

ALTER TABLE `paciente_diagnosticos` COMMENT = 'Diagnósticos CIE-10 asignados a cada paciente';

ALTER TABLE `pacientes` COMMENT = 'Entidad central: pacientes del programa de atención domiciliaria';

ALTER TABLE `personal` COMMENT = 'Profesionales y auxiliares. NO son usuarios del sistema.';

ALTER TABLE `roles` COMMENT = 'Roles de acceso al sistema (solo personal administrativo)';

ALTER TABLE `solicitudes_equipos` COMMENT = 'Solicitudes de equipos biomédicos para pacientes';

ALTER TABLE `telexperticias` COMMENT = 'Telexperticias: interconsultas virtuales por especialidad (dato del Excel no modelado)';

ALTER TABLE `tutelas` COMMENT = 'Tutelas judiciales del paciente. Campo es_permanente resuelve ambigüedad de duración.';

ALTER TABLE `usuarios` COMMENT = 'Usuarios del sistema: exclusivamente madrinas (personal administrativo)';

ALTER TABLE `visitas_domiciliarias` COMMENT = 'Visitas domiciliarias: unidad mínima de atención ejecutada';

ALTER TABLE `zonas` COMMENT = 'División geográfica de primer nivel';

ALTER TABLE `barrios` ADD CONSTRAINT `fk_bar_com` FOREIGN KEY (`id_comuna`) REFERENCES `comunas` (`id_comuna`);

ALTER TABLE `comunas` ADD CONSTRAINT `fk_com_zon` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);

ALTER TABLE `cuidadores` ADD CONSTRAINT `fk_cui_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE;

ALTER TABLE `detalle_solicitud_equipos` ADD CONSTRAINT `fk_dse_equ` FOREIGN KEY (`id_equipo`) REFERENCES `catalogo_equipos` (`id_equipo`);

ALTER TABLE `detalle_solicitud_equipos` ADD CONSTRAINT `fk_dse_sol` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_equipos` (`id_solicitud`) ON DELETE CASCADE;

ALTER TABLE `laboratorios` ADD CONSTRAINT `fk_lab_ord` FOREIGN KEY (`id_orden_asociada`) REFERENCES `ordenes_medicas` (`id_orden`) ON DELETE SET NULL;

ALTER TABLE `laboratorios` ADD CONSTRAINT `fk_lab_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

ALTER TABLE `laboratorios` ADD CONSTRAINT `fk_lab_per` FOREIGN KEY (`id_personal_toma`) REFERENCES `personal` (`id_personal`) ON DELETE SET NULL;

ALTER TABLE `laboratorios` ADD CONSTRAINT `fk_lab_usu` FOREIGN KEY (`id_usuario_solicita`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

ALTER TABLE `logs_acceso` ADD CONSTRAINT `fk_log_usu` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `ordenes_medicas` ADD CONSTRAINT `fk_ord_esp` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

ALTER TABLE `ordenes_medicas` ADD CONSTRAINT `fk_ord_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

ALTER TABLE `ordenes_medicas` ADD CONSTRAINT `fk_ord_per` FOREIGN KEY (`id_personal_ordena`) REFERENCES `personal` (`id_personal`) ON DELETE SET NULL;

ALTER TABLE `paciente_diagnosticos` ADD CONSTRAINT `fk_pdi_cie` FOREIGN KEY (`codigo_cie10`) REFERENCES `diagnosticos_cie10` (`codigo`);

ALTER TABLE `paciente_diagnosticos` ADD CONSTRAINT `fk_pdi_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE;

ALTER TABLE `pacientes` ADD CONSTRAINT `fk_pac_aseg` FOREIGN KEY (`id_aseguradora`) REFERENCES `aseguradoras` (`id_aseguradora`);

ALTER TABLE `pacientes` ADD CONSTRAINT `fk_pac_barrio` FOREIGN KEY (`id_barrio`) REFERENCES `barrios` (`id_barrio`);

ALTER TABLE `pacientes` ADD CONSTRAINT `fk_pac_madrina` FOREIGN KEY (`id_madrina`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

ALTER TABLE `personal` ADD CONSTRAINT `fk_per_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`);

ALTER TABLE `personal` ADD CONSTRAINT `fk_per_esp` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

ALTER TABLE `solicitudes_equipos` ADD CONSTRAINT `fk_sol_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

ALTER TABLE `solicitudes_equipos` ADD CONSTRAINT `fk_sol_usu` FOREIGN KEY (`id_usuario_gestiona`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

ALTER TABLE `telexperticias` ADD CONSTRAINT `fk_tel_esp` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

ALTER TABLE `telexperticias` ADD CONSTRAINT `fk_tel_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

ALTER TABLE `telexperticias` ADD CONSTRAINT `fk_tel_usu` FOREIGN KEY (`id_usuario_solicita`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

ALTER TABLE `tutelas` ADD CONSTRAINT `fk_tut_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE;

ALTER TABLE `usuarios` ADD CONSTRAINT `fk_usu_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `fk_vis_esp` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `fk_vis_ord` FOREIGN KEY (`id_orden_asociada`) REFERENCES `ordenes_medicas` (`id_orden`) ON DELETE SET NULL;

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `fk_vis_pac` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `fk_vis_per` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`);

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `fk_vis_usu` FOREIGN KEY (`id_usuario_programa`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

ALTER TABLE `visitas_domiciliarias` ADD CONSTRAINT `visitas_domiciliarias_id_servicio_foreign` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON DELETE SET NULL;
