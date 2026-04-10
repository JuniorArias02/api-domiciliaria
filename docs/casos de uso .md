# Casos de Uso y Métodos del Sistema



Este documento describe los casos de uso y métodos principales para el sistema, agrupados por dominio. Los atributos se derivan directamente del esquema de la base de datos.

## 1. Autenticación y Autorización
**Tablas involucradas:** `roles`, `usuarios`, `logs_acceso`

### Casos de Uso
* **Autenticación de Usuario:** Validar credenciales y permitir el acceso al sistema.
* **Gestión de Roles:** Asignar niveles de acceso a diferentes usuarios (ej. Administrador, Médico, Coordinador).
* **Registro de Auditoría (Logs):** Rastrear quién inicia sesión, desde dónde y en qué dispositivo.

### Métodos
* `iniciarSesion(email, password)`
* `cerrarSesion(idUsuario)`
* `registrarUsuario(datosUsuario)`
* `asignarRol(idUsuario, idRol)`
* `registrarAcceso(idUsuario, accion, ip, dispositivo, agenteUsuario)`
* `obtenerLogsAccesoPorUsuario(idUsuario)`

---

## 2. Gestión de Pacientes
**Tablas involucradas:** `pacientes`, `cuidadores`, `tutelas`, `paciente_diagnosticos`, `diagnosticos_cie10`, `aseguradoras`

### Casos de Uso
* **Registro de Pacientes:** Registrar nuevos pacientes con su información principal y datos de geolocalización.
* **Asignación de Cuidadores:** Vincular cuidadores principales y secundarios a un paciente.
* **Perfil Clínico:** Asociar diagnósticos CIE-10 con el paciente.
* **Protecciones Legales (Tutelas):** Gestionar y rastrear protecciones legales activas para la prestación de servicios.

### Métodos
* `crearPaciente(datosPaciente)`
* `actualizarUbicacionPaciente(idPaciente, latitud, longitud)`
* `obtenerPerfilPaciente(idPaciente)`
* `agregarCuidador(datosCuidador)`
* `obtenerCuidadoresPorPaciente(idPaciente)`
* `agregarDiagnosticoPaciente(idPaciente, codigoCie10, tipo, esPrincipal)`
* `registrarTutela(datosTutela)`
* `obtenerAseguradorasActivas()`

--- 

## 3. Rutas y Geolocalización en Mapa
**Tablas involucradas:** `zonas`, `comunas`, `barrios`, `visitas_domiciliarias`, `pacientes`



### Casos de Uso
* **Mapeo Geográfico:** Mostrar todos los pacientes activos en un mapa basándose en sus coordenadas.
* **Planificación de Rutas:** Organizar las rutas diarias para el personal médico basándose en zonas y ubicaciones de pacientes.
* **Optimización de Rutas:** Calcular el camino más eficiente entre múltiples visitas domiciliarias programadas.
* **Gestión de Territorio:** Agrupar barrios en comunas y zonas para la logística.

### Métodos
* `obtenerDatosMapaPacientesActivos()`
* `planearRutasDiarias(idPersonal, fecha)`
* `optimizarRuta(listaVisitas)`
* `obtenerBarriosPorZona(idZona)`
* `obtenerHistorialUbicacionPaciente(idPaciente)`

---

## 4. Personal Médico y Órdenes
**Tablas involucradas:** `personal`, `cargos`, `especialidades`, `ordenes_medicas`

### Casos de Uso
* **Directorio de Personal:** Gestionar profesionales de la salud, sus especialidades y disponibilidad.
* **Formulación de Órdenes Médicas:** Crear órdenes médicas activas (ej. 10 sesiones de terapia).
* **Seguimiento MIPRES:** Asociar órdenes médicas estándar con números MIPRES del gobierno.

### Métodos
* `registrarPersonal(datosPersonal)`
* `actualizarEstadoPersonal(idPersonal, estado)`
* `obtenerPersonalPorEspecialidad(idEspecialidad)`
* `crearOrdenMedica(datosOrden)`
* `obtenerOrdenesActivasPorPaciente(idPaciente)`

---

## 5. Visitas Domiciliarias y Check-ins
**Tablas involucradas:** `visitas_domiciliarias`

### Casos de Uso
* **Programación de Visitas:** Asignar un profesional para visitar a un paciente en una fecha y hora específicas.
* **Check-in / Check-out en Terreno:** Registrar la geolocalización exacta y la marca de tiempo cuando un profesional llega y sale de la casa de un paciente.
* **Cancelación de Visitas:** Registrar las razones de las visitas perdidas o canceladas.

### Métodos
* `programarVisitaDomiciliaria(datosVisita)`
* `registrarCheckInVisita(idVisita, latitud, longitud)`
* `registrarCheckOutVisita(idVisita, latitud, longitud)`
* `cancelarVisita(idVisita, motivo)`
* `obtenerVisitasPendientesPorPersonal(idPersonal, fecha)`

---

## 6. Logística de Equipos
**Tablas involucradas:** `solicitudes_equipos`, `detalle_solicitud_equipos`, `catalogo_equipos`

### Casos de Uso
* **Catálogo de Equipos:** Mantener una lista actualizada de equipos médicos disponibles (oxígeno, camas, sillas de ruedas, etc.).
* **Solicitud de Equipos:** Solicitar equipos específicos para ser entregados en la casa de un paciente.
* **Seguimiento de Entrega y Devolución:** Monitorear las fechas esperadas y reales de entrega y recogida de los equipos.

### Métodos
* `obtenerCatalogoEquipos()`
* `solicitarEquipo(datosSolicitud, items)`
* `actualizarEstadoSolicitud(idSolicitud, estado)`
* `registrarEntregaEquipo(idSolicitud, fechaEntrega)`
* `registrarDevolucionEquipo(idSolicitud, fechaDevolucion)`

---

## 7. Laboratorios y Telemedicina (Telexperticias)
**Tablas involucradas:** `laboratorios`, `telexperticias`

### Casos de Uso
* **Programación de Muestras de Laboratorio:** Programar visitas domiciliarias específicamente para la toma de muestras.
* **Confirmación de Muestra:** Confirmar en el sistema que una muestra fue tomada exitosamente.
* **Consulta de Telemedicina:** Solicitar consultas virtuales (telexperticias) entre el personal de campo y los especialistas.

### Métodos
* `programarExamenLaboratorio(datosLaboratorio)`
* `confirmarTomaMuestraLaboratorio(idLaboratorio)`
* `solicitarConsultaTelexperticia(datosTelemedicina)`
* `actualizarEstadoTelexperticia(idTelexperticia, estado)`