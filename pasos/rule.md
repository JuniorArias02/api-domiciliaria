Table: ingresos

Columns:
	id_ingreso	int AI PK
	ingreso	int
	id_paciente	int
	autorizacion	varchar(45)
	fecha_ingreso	datetime
	created_at	timestamp
	update_at	timestamp



Table: ordenes_medicas

Columns:
	id_orden	int AI PK
	id_ingreso	int
	creado_por	int
	fecha_orden	date
	observacion	text
	estado	enum('VIGENTE','SUSPENDIDA','VENCIDA','FINALIZADA','CANCELADA')
	created_at	timestamp
	updated_at	timestamp

-----------------------------

Table: ordenes_servicios

Columns:
	id_orden_servicio	int AI PK
	id_orden	int
	id_servicio	int
	id_profesional_asignado	int
	numero_sesiones	int
	frecuencia_dias	int
	fecha_inicio	datetime
	estado	varchar(50)
	created_at	timestamp
	updated_at	timestamp

----------------------------------
table: visitas_domiciliarias

Columns:
	id_visita	int AI PK
	codigo_ingreso	varchar(20)
	id_orden_servicio	int
	id_paciente	int
	id_personal	int
	fecha_programada	datetime
	id_usuario_programa	int
	fecha_realizada	datetime
	latitud_checkin	decimal(10,8)
	longitud_checkin	decimal(11,8)
	latitud_checkout	decimal(10,8)
	longitud_checkout	decimal(11,8)
	estado	enum('PROGRAMADA','COMPLETADA','CANCELADA','REPROGRAMADA','NO_ATENDIDA')
	motivo_cancelacion	varchar(255)
	observaciones	text
	created_at	timestamp
	updated_at	timestamp
	tipo_atencion_ext	varchar(100)
	remitido_a	varchar(255)

-----------------------------------------
