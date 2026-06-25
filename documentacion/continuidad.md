# Ajuste funcional - Continuidad de tratamientos

## Contexto

Actualmente existe un endpoint que permite consultar posibles continuidades de tratamiento para un paciente. Sin embargo, la respuesta retorna un historial demasiado amplio, lo que dificulta la selección por parte del usuario.

Se requiere optimizar la experiencia de usuario manteniendo la lógica actual de continuidad por servicio.

---

## Objetivo

Al momento de crear una nueva autorización y agregar un servicio, el sistema debe sugerir automáticamente las continuidades más recientes del mismo servicio para el paciente seleccionado.

La búsqueda debe realizarse utilizando:

* Paciente
* Servicio seleccionado

---

## Ajuste requerido

### Endpoint actual

Mantener el endpoint existente.

### Nuevo comportamiento

La consulta deberá limitar los resultados retornados a las últimas 3 o 4 continuidades más recientes del mismo servicio.

Ejemplo:

Paciente: Juan Pérez

Servicio seleccionado: Medicina General

Respuesta sugerida:

* Ingreso #2541 - 08/06/2026
* Ingreso #2410 - 02/04/2026
* Ingreso #2298 - 15/01/2026

Máximo 3 o 4 registros ordenados por fecha descendente.

---

## Flujo esperado en Frontend (*esto es para que tenga en cuentas, tu no tienes nada que ver con el frontend*)

1. Usuario crea una nueva autorización.
2. Usuario selecciona un servicio.
3. El sistema consulta automáticamente las últimas continuidades disponibles para ese mismo servicio.
4. Se muestran las sugerencias.
5. El usuario puede:

   * Seleccionar una continuidad existente.
   * Marcar el servicio como un tratamiento nuevo.

---

## Búsqueda avanzada

Agregar una opción:

"Buscar más..."

Esta opción permitirá consultar continuidades históricas que no fueron incluidas en las sugerencias iniciales.

Filtros sugeridos:

* Número de ingreso
* Número de autorización
* Fecha de ingreso

---

## Beneficios

* Reduce ruido visual.
* Facilita la selección de continuidades recientes.
* Mantiene acceso al historial completo cuando sea necesario.
* Evita que el usuario deba revisar ingresos antiguos que normalmente no serán utilizados.
* Conserva la lógica actual de continuidad mediante `id_orden_servicio_anterior`.

---

## Consideraciones técnicas

No se requiere modificar la estructura actual de la base de datos.

La relación mediante:

`id_orden_servicio_anterior`

continúa siendo la fuente oficial para determinar la continuidad de un tratamiento.

El cambio corresponde únicamente a la estrategia de consulta y presentación de resultados.
