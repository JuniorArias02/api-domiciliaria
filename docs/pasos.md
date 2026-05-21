{
  "autorizacion": "4292224",
  "id_paciente": 16,
  "observacion": "pruebas",
  "servicios": [
    {
      "id_servicio": 1,
      "id_profesional": 1,
      "numero_sesiones": 1,
      "frecuencia_dias": 1,
      "fecha_inicio": "2026-05-21 13:35",
      "fecha_programada": "2026-05-21 13:35"
    },
    {
      "id_servicio": 1,
      "id_profesional": 1,
      "numero_sesiones": 1,
      "frecuencia_dias": 1,
      "fecha_inicio": "2026-05-21 13:51",
      "fecha_programada": "2026-05-23 13:51"
    }
  ]
}


necesito que me ayudes a crear casos de uso para crear ingreso, y de una ves crear las sesiones

NOTA: ESTO SE CRE PRIMERO
Table: ingresos

Columns:
	id_ingreso	int AI PK
	ingreso	int (este ingreso se genera segun la secuencia de ingresos, de la tabla de ingresos)
	id_paciente	int
	autorizacion	varchar(45)
	fecha_ingreso	datetime (se toma el momento qeu se crea en el sistema)
	created_at	timestamp
	update_at	timestamp

------------------------------------------------
NOTA: ESTO SE CRE SEGUNDO Y ACA SE CREA EL ORDEN CON EL ID_INGRESO
Table: ordenes_medicas

Columns:
	id_orden	int AI PK
	id_ingreso	int
	creado_por	int (el token se decifra quien lo creo)
	fecha_orden	date (se toma el momento qeu se crea en el sistema)
	observacion	text (toma la observacion del front)
	estado	enum('VIGENTE','SUSPENDIDA','VENCIDA','FINALIZADA','CANCELADA')
	created_at	timestamp
	updated_at	timestamp

------------------------------------------------
NOTA: ESTO SE CRE TERCERO Y ACA SE CREA EL SERVICIO CON EL ID_ORDEN QUE ME DIO ANTERIORMENTE
Table: ordenes_servicios

Columns:
	id_orden_servicio	int AI PK
	id_orden	int
	id_servicio	int (toma el servicio del front)
	id_profesional_asignado	int (toma el profesional del front)
	numero_sesiones	int (toma el numero de sesiones del front)
	frecuencia_dias	int (toma la frecuencia de dias del front)
	fecha_inicio	datetime (toma la fecha de inicio del front)
	estado	varchar(50) (siempre sera programada)
	created_at	timestamp
	updated_at	timestamp

------------------------------------------------
NOTA: ESTO SE CRE CUARTO Y ACA SE CREA LA VISITA CON EL ID_ORDEN_SERVICIO Y LA FECHA PROGRAMADA
en esta tabla solo se crea la primera visita de servicio , es decir la fecha programada (solo se crea una visita nada mas) 

Table: visitas_domiciliarias

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


segun las reglas de DDD Y L jerarquia que llevamos, me debes crear el caso de uso corresoondiente para crear la integracion de crear 