# Análisis Refinado de Integración (v2) - Marzo 2026

Este documento actualiza el plan de integración basado en la nueva estructura del archivo `consulta enero a marzo.xlsx` y la retroalimentación sobre la limpieza del esquema.

## 1. Mapeo de Campos del Nuevo Excel

| Campo Excel | Destino Propuesto | Observaciones |
| :--- | :--- | :--- |
| **Ingreso** | `visitas_domiciliarias.codigo_ingreso` | Identificador único de transacción. Evita duplicados. |
| **Servicio** | `servicios.codigo_servicio` | Código único del procedimiento. |
| **Descripción del Servicio** | `servicios.nombre_servicio` | Nombre legible del procedimiento. |
| **Entidad** | `aseguradoras.nombre` | Se usará para buscar/vincular la EPS. |
| **Tipo de Atención** | `visitas_domiciliarias.tipo_atencion_ext` | Ej: 'URGENCIAS', 'CONSULTA EXTERNA'. |
| **Fecha Atención** | `visitas_domiciliarias.fecha_realizada` | Fecha y hora del acto médico. |
| **Dx sal.** | `paciente_diagnosticos.codigo_cie10` | Diagnóstico de salida (Principal). |
| **Descripción Diagnóstico**| `visitas_domiciliarias.observaciones` | Complemento narrativo del diagnóstico. |
| **TD** | `pacientes.tipo_documento` | Ej: 'CC', 'TI', 'CE'. |
| **Beneficiario** | `pacientes.identificacion` | Cédula del paciente (Clave de búsqueda). |
| **NombreBen** | `pacientes.nombre_completo` | |
| **Direccion** | `pacientes.direccion` | |
| **Telefono** | `pacientes.telefono` | |
| **Fecha Nac.** | `pacientes.fecha_nacimiento` | Formato YYYY-MM-DD. |
| **Sexo** | `pacientes.sexo` | M / F. |
| **Nombre Profesional** | `personal.nombre_completo` | Se busca en tabla personal para obtener `id_personal`. |
| **Remitido A** | `visitas_domiciliarias.remitido_a` | Lugar de remisión posterior. |
| **Dx Rel. 1, 2, 3** | `paciente_diagnosticos.codigo_cie10` | Diagnósticos secundarios relacionados. |

## 2. Cambios Estructurales Requeridos

### A. Renombramiento por "Neutralidad"
Se descarta el sufijo `_excel` propuesto anteriormente. La columna en `visitas_domiciliarias` será simplemente **`id_servicio`**.

### B. Llave Primaria en `paciente_diagnosticos`
Para permitir que un mismo paciente tenga el mismo diagnóstico (CIE10) en múltiples visitas registradas, la tabla `paciente_diagnosticos` **debe incluir `id_visita` en su Primary Key compuesta**.

## 3. Plan de Migración (SQL / Laravel)

1.  **Crear Tabla `servicios`**: Antes de modificar visitas.
2.  **Modificar `visitas_domiciliarias`**:
    *   Añadir `codigo_ingreso` (UNIQUE).
    *   Añadir `id_servicio` (FK a servicios).
    *   Añadir `tipo_atencion_ext`, `remitido_a`.
3.  **Modificar `paciente_diagnosticos`**:
    *   Añadir `id_visita` (FK a visitas).
    *   Actualizar PK de `(id_paciente, codigo_cie10, tipo_diagnostico)` a `(id_paciente, codigo_cie10, tipo_diagnostico, id_visita)`.

---
**Nota:** La migración anterior ha sido marcada como "obsoleta" y será reemplazada por una versión limpia y optimizada.
