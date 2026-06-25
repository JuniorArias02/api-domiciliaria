# Continuidad de Servicios entre Ingresos

## Objetivo

Permitir que un servicio de una orden médica pueda continuar en un nuevo ingreso, manteniendo la trazabilidad histórica de tratamientos y visitas pendientes.

---

## Tabla afectada

### ordenes_servicios

Se agrega un nuevo campo:

| Campo                      | Tipo | Nulo | Descripción                                                                                     |
| -------------------------- | ---- | ---- | ----------------------------------------------------------------------------------------------- |
| id_orden_servicio_anterior | INT  | Sí   | Referencia a la orden de servicio de origen cuando un tratamiento continúa en un nuevo ingreso. |

---

## Relación

```text
ordenes_servicios
        │
        └── id_orden_servicio_anterior
                    │
                    ▼
          ordenes_servicios.id_orden_servicio
```

---

## Ejemplo de uso

### Ingreso Original

```text
Ingreso 1001
└── Orden Médica 200
    └── Orden Servicio 500
        ├── Visita 1
        ├── Visita 2
        ├── Visita 3
        └── Visita 4
```

### Nuevo Ingreso

```text
Ingreso 1002
└── Orden Médica 250
    └── Orden Servicio 650
        └── id_orden_servicio_anterior = 500
```

En este escenario, la orden de servicio 650 es una continuación de la orden de servicio 500.

---

## Beneficios

* Permite identificar tratamientos continuados entre ingresos.
* Facilita el cálculo de sesiones pendientes.
* Mantiene trazabilidad clínica.
* Evita duplicar información histórica.
* Permite consultar el origen de un tratamiento desde cualquier ingreso.

---

## Migración Laravel

### Up

```php
Schema::table('ordenes_servicios', function (Blueprint $table) {
    $table->unsignedBigInteger('id_orden_servicio_anterior')
        ->nullable()
        ->after('id_orden');

    $table->foreign('id_orden_servicio_anterior')
        ->references('id_orden_servicio')
        ->on('ordenes_servicios')
        ->nullOnDelete();
});
```

### Down

```php
Schema::table('ordenes_servicios', function (Blueprint $table) {
    $table->dropForeign(['id_orden_servicio_anterior']);
    $table->dropColumn('id_orden_servicio_anterior');
});
```

---

## Consultar servicio de origen

```sql
SELECT
    actual.id_orden_servicio,
    anterior.id_orden_servicio AS servicio_origen
FROM ordenes_servicios actual
LEFT JOIN ordenes_servicios anterior
    ON actual.id_orden_servicio_anterior = anterior.id_orden_servicio
WHERE actual.id_orden_servicio = 650;
```

---

## Consideraciones

* El campo es opcional.
* Solo debe asignarse cuando exista continuidad clínica.
* Los servicios nuevos deben almacenar NULL.
* La continuidad se maneja a nivel de servicio y no de ingreso.

```
```
