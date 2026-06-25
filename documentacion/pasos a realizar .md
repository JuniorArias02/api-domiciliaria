# Implementación de Continuidad de Tratamientos entre Ingresos

## Objetivo

Permitir que un servicio de un ingreso anterior pueda continuar en un nuevo ingreso, manteniendo la trazabilidad clínica y administrativa del tratamiento.

La continuidad debe manejarse a nivel de **orden de servicio** y no a nivel de ingreso.

---

# Problema Actual

Actualmente el sistema permite crear:

```text
Ingreso
 └── Orden Médica
      └── Orden Servicio
           └── Visitas
```

Sin embargo, cuando un paciente vuelve a ingresar en un mes posterior, no existe una forma de relacionar un nuevo servicio con un servicio anterior que quedó pendiente o que requiere continuidad.

Ejemplo:

```text
Ingreso 1001
└── Medicina
    ├── Visita 1
    ├── Visita 2
    ├── Visita 3
    └── Visita 4
```

Posteriormente:

```text
Ingreso 1002
└── Medicina
```

Actualmente no existe ninguna relación entre ambos tratamientos.

---

# Solución

Agregar una referencia de continuidad en la tabla `ordenes_servicios`.

## Nuevo Campo

Tabla:

```text
ordenes_servicios
```

Campo:

```text
id_orden_servicio_anterior
```

Tipo:

```text
INT NULL
```

Relación:

```text
ordenes_servicios.id_orden_servicio_anterior
    → ordenes_servicios.id_orden_servicio
```

---

# Flujo Funcional

## Paso 1

Usuario crea un nuevo ingreso para un paciente.

Ejemplo:

```text
Paciente: Juan Pérez

Nuevo ingreso:
Ingreso 1002
```

---

## Paso 2

Durante la creación de las órdenes de servicio, el sistema debe consultar el historial del paciente.

Consulta base:

```sql
SELECT
    os.id_orden_servicio,
    s.nombre_servicio,
    i.id_ingreso,
    i.fecha_ingreso
FROM ordenes_servicios os
INNER JOIN ordenes_medicas om
    ON om.id_orden = os.id_orden
INNER JOIN ingresos i
    ON i.id_ingreso = om.id_ingreso
INNER JOIN servicios s
    ON s.id_servicio = os.id_servicio
WHERE i.id_paciente = :idPaciente
ORDER BY i.fecha_ingreso DESC;
```

---

## Paso 3

Mostrar al usuario los posibles tratamientos anteriores.

Ejemplo:

```text
Continuar tratamiento anterior

( ) Ninguno

( ) Medicina General
    Ingreso: 1001
    Servicio: 500

( ) Fisioterapia
    Ingreso: 998
    Servicio: 480
```

---

## Paso 4

Si el usuario selecciona un tratamiento anterior, al crear la nueva orden de servicio debe almacenarse la referencia.

Ejemplo:

```text
Nueva Orden Servicio:
650

Servicio anterior:
500
```

Guardar:

```text
id_orden_servicio = 650

id_orden_servicio_anterior = 500
```

---

# Comportamiento Esperado

## Servicio Nuevo

Si el tratamiento no proviene de ningún servicio anterior:

```text
id_orden_servicio_anterior = NULL
```

---

## Continuidad

Si el tratamiento continúa uno anterior:

```text
id_orden_servicio_anterior = ID_SERVICIO_ANTERIOR
```

---

# Visualización

Cuando se consulte una orden de servicio que tenga continuidad:

Mostrar:

```text
Este tratamiento continúa la orden de servicio #500
del ingreso #1001.
```

---

# Consultar Servicio de Origen

```sql
SELECT
    actual.id_orden_servicio,
    anterior.id_orden_servicio AS servicio_origen
FROM ordenes_servicios actual
LEFT JOIN ordenes_servicios anterior
    ON actual.id_orden_servicio_anterior = anterior.id_orden_servicio
WHERE actual.id_orden_servicio = :idOrdenServicio;
```

---

# Beneficios

* Mantiene trazabilidad clínica.
* Permite conocer el origen de un tratamiento.
* Facilita auditorías.
* Permite identificar tratamientos continuados entre ingresos.
* Permite calcular sesiones pendientes provenientes de ingresos anteriores.
* No modifica la estructura actual de ingresos ni órdenes médicas.
* Mantiene la continuidad en el nivel correcto del modelo de datos (orden de servicio).

---

# Consideración Importante

NO se debe asumir automáticamente que un nuevo ingreso continúa el ingreso inmediatamente anterior.

Ejemplo:

```text
Ingreso 1001
 └── Medicina

Ingreso 1002
 └── Fisioterapia

Ingreso 1003
 └── Medicina
```

En este caso el servicio de Medicina del ingreso 1003 debe poder relacionarse directamente con el servicio de Medicina del ingreso 1001.

Por esta razón la continuidad debe seleccionarse explícitamente al momento de crear la nueva orden de servicio.

```
```






### Mensaje para el equipo de Frontend 🖥️

A continuación, la información necesaria para que el equipo de frontend realice su integración:

**Nuevas Reglas de Negocio (Continuidad de Servicios)**
* **No se debe asumir automáticamente** que un tratamiento continúa otro; la continuidad debe ser seleccionada explícitamente por el usuario a la hora de crear el nuevo servicio.
* La continuidad se maneja exclusivamente al nivel de **Orden de Servicio**, no de ingreso.
* El campo `id_orden_servicio_anterior` es **opcional**. Cuando se trata de un servicio médico completamente nuevo o sin continuidad clínica, se enviará como `NULL` (o simplemente no se enviará).
* En el paso previo a la creación de la orden de servicio, el sistema debe consultar primero el historial del paciente para darle al usuario la opción de continuar algún tratamiento pasado.

**1. Obtener Historial de Tratamientos (Nuevo Endpoint)**
Deben llamar a esta ruta para listar las opciones de continuidad de tratamiento disponibles al usuario:
* **Método**: `GET`
* **Endpoint**: `/api/v1/ordenes-servicio/historial/paciente/{idPaciente}`
* **Headers**: `Authorization: Bearer <token>`
* **Respuesta Esperada (200 OK)**:
```json
{
  "data": [
    {
      "id_orden_servicio": 500,
      "nombre_servicio": "Medicina General",
      "id_ingreso": 1001,
      "fecha_ingreso": "2023-11-15 08:30:00"
    },
    {
      "id_orden_servicio": 480,
      "nombre_servicio": "Fisioterapia",
      "id_ingreso": 998,
      "fecha_ingreso": "2023-09-10 14:00:00"
    }
  ]
}
```

**2. Crear la Nueva Orden de Servicio (Endpoint Actualizado)**
En el endpoint de creación actual, ahora podrán adjuntar de manera opcional el identificador del tratamiento al que darán continuidad:
* **Método**: `POST`
* **Endpoint**: `/api/v1/ordenes-servicio`
* **Headers**: `Authorization: Bearer <token>`
* **Body Request (JSON)**:
```json
{
  "id_orden": 250,
  "id_servicio": 5,
  "id_profesional_asignado": 10,
  "numero_sesiones": 10,
  "frecuencia_dias": 2,
  "estado": "PROGRAMADA",
  "id_orden_servicio_anterior": 500
}
```
*(Nota: Si el tratamiento es nuevo y no tiene historial anterior, se debe omitir la llave `id_orden_servicio_anterior` o mandarla como `null`)*