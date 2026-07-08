# Requerimiento Frontend: Monitor de Continuidad y Alertas de Agendamiento

## 1. Objetivo General
Desarrollar una interfaz visual (Dashboard/Tabla de control) que permita a los coordinadores médicos visualizar rápidamente qué pacientes en atención domiciliaria están pendientes de recibir un nuevo agendamiento para su próximo ciclo de atención, mitigando el riesgo de "pacientes olvidados".

## 2. Contexto
El backend proporcionará un nuevo endpoint (ej. `GET /api/reportes/continuidad`) que evaluará automáticamente las **órdenes médicas vigentes** de los pacientes, sumará la **frecuencia en días** (ej. cada 30 días) a la fecha de la última visita, y proyectará la fecha exacta en la que el paciente debería tener un nuevo ingreso o visita. 

## 3. Especificaciones de la Interfaz (UI/UX)

### 3.1. Vista Principal (Monitor de Continuidad)
Se requiere una vista que liste a los pacientes bajo planes de manejo activos. Debe enfocarse en destacar visualmente el nivel de urgencia de agendamiento.

**Datos a mostrar por fila/tarjeta:**
* **Paciente:** Nombre completo y Documento.
* **Plan/Especialidad:** Tipo de atención que está recibiendo.
* **Última Atención:** Fecha en la que se le prestó el servicio por última vez.
* **Fecha Proyectada (Próximo Ciclo):** El día exacto en que debería iniciar su nuevo ciclo.
* **Días de Retraso:** Número de días que han pasado desde la fecha proyectada (si aplica).
* **Estado Visual (Badge/Semáforo):** Un indicador claro del estado actual (ver sección 3.2).

### 3.2. Sistema de Alertas Visuales (Semáforo)
El frontend debe implementar una paleta de colores semántica basada en el estado que retorne la API:

* 🔴 **Rojo (Vencido / Riesgo Crítico):** La fecha proyectada ya pasó y el paciente NO tiene agendamientos futuros. (Ej. "Vencido hace 5 días").
* 🟡 **Amarillo (Por Vencer / Precaución):** La fecha proyectada está próxima a cumplirse (ej. en los próximos 3-5 días) y aún NO hay agendamientos futuros.
* 🟢 **Verde (Al Día / Agendado):** El paciente ya tiene visitas programadas que cubren el nuevo ciclo.

### 3.3. Funcionalidad de Filtros y Búsqueda
El usuario debe poder filtrar esta lista rápidamente para gestionar el trabajo diario:
* **Filtro por Estado de Alerta:** Ver solo los "Rojos", o solo los "Amarillos".
* **Filtro por Especialidad:** Filtrar por el tipo de terapia o especialidad de la orden.
* **Búsqueda Global:** Por nombre o documento del paciente.

### 3.4. Acciones Directas (Call to Action)
Desde esta misma vista, el coordinador debe poder realizar acciones rápidas sobre el paciente "olvidado":
* **Botón "Generar Ingreso" o "Agendar Visita":** Un acceso directo que abra el modal/formulario de agendamiento estándar pre-cargando los datos del paciente y la orden médica asociada.
* **Botón "Ver Historial":** Para abrir una vista rápida (drawer o modal) con las últimas visitas de ese paciente y validar por qué se atrasó.

## 4. Consideraciones Técnicas (Consumo de API)
* El equipo Backend entregará la data pre-calculada. El Frontend no debe realizar cálculos de fechas complejos, solo renderizar el estado proporcionado y calcular formatos relativos (ej. "hace 2 días") si es necesario para la UI.
* Se espera que esta vista sea reactiva y optimizada, utilizando el sistema de diseño actual del proyecto (ej. Tailwind, Material-UI, etc., según aplique).
