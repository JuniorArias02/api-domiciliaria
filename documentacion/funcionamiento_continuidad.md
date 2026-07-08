# Documentación del Módulo de Continuidad (Alertas de Agendamiento)

## 1. Propósito
El objetivo principal de este módulo es identificar de forma proactiva a los pacientes en atención domiciliaria que tienen planes de manejo recurrentes, pero que, por alguna falla operativa, no se les ha programado su próximo ciclo de visitas ("pacientes olvidados").

## 2. Reglas de Negocio Principales
El sistema evalúa el universo total de pacientes bajo las siguientes reglas estrictas para incluirlos en el monitor de alertas:
1. **Orden Activa:** El paciente debe tener una orden médica en curso cuyo `estado` sea `'VIGENTE'`.
2. **Ciclo Recurrente:** La orden de servicio (`ordenes_servicios`) debe tener una `frecuencia_dias > 0` (ej. cada 30 días). Las órdenes con frecuencia 0 o nula se consideran eventos únicos y no entran en este monitor.

## 3. Lógica de Cálculo de Fechas (Backend)
Para determinar si un paciente está al día o retrasado, el sistema hace lo siguiente en la capa de Casos de Uso (`ObtenerAlertasContinuidad.php`):

1. **Fecha Base:** Identifica la última visita realizada o programada (`ultima_atencion`). Si el paciente es nuevo y nunca ha tenido visitas, se utiliza como pivote la `fecha_orden`. Las horas se eliminan (`startOfDay()`) para evitar errores matemáticos.
2. **Fecha de Proyección:** Suma los días de frecuencia (`frecuencia_dias`) a la **Fecha Base** para saber el día exacto en que le corresponde su nuevo ciclo.
3. **Cálculo de Retraso:** Resta la Fecha Proyectada a la fecha de "Hoy". 
   * *Número Positivo:* El paciente ya superó su fecha límite (retrasado).
   * *Número Negativo:* Aún faltan días para que llegue su fecha.
4. **Semáforo (Estado de Alerta):**
   * 🟢 **AGENDADO (Al día):** Tiene visitas futuras programadas (el contador de `visitas_futuras` es > 0).
   * 🔴 **VENCIDO:** No tiene visitas futuras y la fecha proyectada es menor a hoy (Días de retraso > 0).
   * 🟡 **POR VENCER:** No tiene visitas futuras y su fecha proyectada ocurrirá en los próximos 5 días (Días de retraso entre -5 y 0).

## 4. Estructura de Base de Datos y Consulta SQL
El módulo se implementó siguiendo la Arquitectura Modular. En la capa de Infraestructura, el Repositorio (`ContinuidadRepository`) utiliza una consulta SQL nativa hiperoptimizada en vez del ORM Eloquent tradicional. 

El modelo de datos se cruza de la siguiente forma:
`ordenes_servicios` ➡ `ordenes_medicas` ➡ `ingresos` ➡ `pacientes`

### SQL Principal (Motor de Extracción):
```sql
SELECT 
    p.id_paciente,
    p.identificacion,
    p.nombre_completo,
    om.id_orden,
    os.id_orden_servicio,
    os.frecuencia_dias,
    om.fecha_orden,
    s.nombre_servicio as especialidad_nombre,
    -- Subconsulta (A): Extrae la fecha de la última visita ejecutada (histórica)
    (
        SELECT MAX(COALESCE(fecha_realizada, fecha_programada)) 
        FROM visitas_domiciliarias vd 
        WHERE vd.id_orden_servicio = os.id_orden_servicio 
          AND vd.estado IN ('REALIZADA', 'PROGRAMADA')
          AND vd.fecha_programada <= NOW()
    ) as ultima_atencion,
    -- Subconsulta (B): Bandera para saber si ya le programaron el siguiente ciclo
    (
        SELECT COUNT(*) 
        FROM visitas_domiciliarias vd2 
        WHERE vd2.id_orden_servicio = os.id_orden_servicio 
          AND vd2.estado = 'PROGRAMADA'
          AND vd2.fecha_programada > NOW()
    ) as visitas_futuras_count
FROM ordenes_servicios os
JOIN ordenes_medicas om ON os.id_orden = om.id_orden
JOIN ingresos i ON om.id_ingreso = i.id_ingreso
JOIN pacientes p ON i.id_paciente = p.id_paciente
LEFT JOIN servicios s ON os.id_servicio = s.id_servicio
WHERE om.estado = 'VIGENTE'
  AND os.frecuencia_dias > 0
```
