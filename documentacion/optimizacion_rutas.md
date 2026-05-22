# Documentación: Optimización de Rutas y Proyección de Visitas Virtuales

Este documento describe el funcionamiento de la optimización de rutas y la predicción de visitas domiciliarias virtuales dentro de la aplicación, siguiendo la arquitectura de Diseño Guiado por el Dominio (DDD).

---

## 1. Concepto General

El módulo de mapas permite optimizar las rutas de atención domiciliaria bajo dos modalidades (controladas desde el frontend mediante el switch `ver_agendados`):

1. **Visitas Agendadas (`ver_agendados = true`)**:
   Optimiza únicamente las rutas de aquellas visitas físicas que ya están programadas manualmente en el sistema en estado `PROGRAMADA` para el mes y año seleccionados.

2. **Visitas Proyectadas/Virtuales (`ver_agendados = false`)**:
   Genera dinámicamente visitas "virtuales" en memoria para aquellos pacientes activos que requieran atención en el mes seleccionado, basándose en la fecha de su última visita completada y la frecuencia de atención configurada. Esto evita poblar la base de datos de registros futuros que podrían ser modificados, cancelados o reprogramados.

---

## 2. Estructura de la Base de Datos Utilizada

La lógica de proyección consume y relaciona las siguientes tablas:

* **`pacientes`**: Contiene la ubicación geográfica (`latitud`, `longitud`), datos personales y estado de afiliación.
* **`ingresos`**: Vincula a los pacientes con sus autorizaciones y admisiones actuales.
* **`ordenes_medicas`**: Contiene la orden general emitida por el médico.
* **`ordenes_servicios`**: Define los servicios específicos asignados a la orden, incluyendo:
  * `frecuencia_dias`: Cada cuántos días debe visitarse al paciente.
  * `numero_sesiones`: Cantidad total de visitas contratadas.
  * `fecha_inicio`: Fecha de inicio del tratamiento.
* **`visitas_domiciliarias`**: Registros reales de las visitas físicas de atención domiciliaria (`estado = 'COMPLETADA'`, `'PROGRAMADA'`, etc.).

---

## 3. Arquitectura del Código (DDD)

El caso de uso consume los repositorios e interfaces siguiendo principios DDD:

* **`ServicioRepositoryInterface`**: Inyectado en el Caso de Uso para validar la existencia del servicio médico seleccionado en los filtros antes de procesar la lógica de optimización.
* **`MapaRepositoryInterface`**: Define los contratos de extracción de datos para aislar la capa de persistencia (SQL) de la capa de aplicación.

---

## 4. Flujo detallado de Proyección Virtual (`ver_agendados = false`)

Cuando se solicitan visitas proyectadas, el proceso se divide en dos fases:

### Fase A: Consulta a la Base de Datos (`MapaRepository::obtenerDatosBasePrediccion`)
Se realiza una consulta SQL optimizada para obtener los datos base de cada paciente:
1. **Última Orden de Servicio**: Mediante una subconsulta, se obtiene la orden de servicio más reciente (`MAX(id_orden_servicio)`) para cada combinación de paciente y servicio, asegurando que no se duplique la información si existen registros históricos.
2. **Última Visita Real Realizada**: Se obtiene la fecha máxima de realización (`fecha_realizada`) de las visitas en estado `COMPLETADA` asociadas a esa orden.
3. **Conteo de Sesiones**: Se cuenta cuántas sesiones han sido completadas hasta la fecha para calcular las restantes.

### Fase B: Algoritmo de Proyección en Memoria (`RutaOptimizationService::predecirFechasPorFrecuencia`)
Para cada paciente, se calcula cuándo corresponde su siguiente atención utilizando las siguientes reglas de negocio:

1. **Determinación de la Fecha Base**:
   * Si el paciente tiene una última visita realizada, esta se utiliza como fecha de partida.
   * Si no tiene visitas previas, se utiliza la `fecha_inicio` de la orden de servicio (o la `fecha_orden` como fallback).
2. **Límite de Proyección para Evitar Bucle Infinito**:
   * Si el paciente ya completó todas las sesiones asignadas en su orden (`sesiones_completadas >= numero_sesiones`), el sistema proyecta **exactamente una única sesión futura** (la sesión inmediata posterior). Esto evita que el paciente aparezca proyectado de forma redundante mes a mes de manera infinita si no se ha registrado una nueva orden.
   * Si el paciente aún tiene sesiones pendientes por realizar, el sistema proyecta la cantidad restante (`numero_sesiones - sesiones_completadas`).
3. **Validación Temporal**:
   * A partir de la fecha base, se incrementa de forma sucesiva sumando la `frecuencia_dias`.
   * Si la fecha resultante cae dentro del mes y año consultados por el usuario, se añade la visita virtual a la lista de candidatos (`virtual = true` y `estado = 'PENDIENTE'`).

---

## 5. API y Parámetros

El endpoint expuesto es:
`GET /api/v1/mapas/optimizar`

### Parámetros aceptados:
* `mes` (int, obligatorio): Mes a proyectar (1-12).
* `anio` (int, opcional): Año a proyectar (por defecto, el actual).
* `ver_agendados` (bool, opcional): `true` para programadas, `false` para predictivas (por defecto `false`).
* `id_servicio` (int, opcional): Permite filtrar la ruta optimizada por un servicio médico específico (p. ej. Fisioterapia, Medicina General). Valida DDD contra el repositorio de servicios.
* `id_profesional` (int, opcional): Permite filtrar la ruta asignada a un profesional específico.
* `tipo_filtro` (string, opcional): `pacientes` (optimización global por cercanía) o `profesional` (agrupada por el profesional asignado).
