# Análisis de Integración: Excel a Base de Datos

Este documento detalla cómo integrar los datos del archivo `consulta enero a marzo.xlsx` en la base de datos `app_domiciliaria`.

## 1. Análisis del Excel
El archivo contiene registros de consultas médicas con información de pacientes, profesionales, entidades de salud y diagnósticos.

**Campos Clave identificados:**
- **Ingreso**: Identificador único del acto médico (ID de transacción).
- **Entidad/Contrato**: Datos de la aseguradora.
- **Beneficiario**: Datos demográficos del paciente.
- **Servicio**: Detalles del procedimiento realizado.
- **Diagnósticos**: Códigos CIE10 y sus descripciones relacionadas.

---

## 2. Propuesta de Mapeo de Datos

### A. Tabla `pacientes` (Datos del Beneficiario)
La mayoría de estos datos ya existen, pero deben sincronizarse.
| Campo Excel | Columna BD | Observación |
| :--- | :--- | :--- |
| Beneficiario | `identificacion` | Clave única de búsqueda. |
| NombreBen | `nombre_completo` | |
| Direccion | `direccion` | |
| Telefono | `telefono` | |
| Fecha Nac. | `fecha_nacimiento` | Formato YYYY-MM-DD. |
| Sexo | `sexo` | Mapear 'F' -> 'F', 'M' -> 'M'. |

### B. Tabla `visitas_domiciliarias` (Datos del Ingreso)
Aquí es donde ocurre la mayor parte de la integración.
| Campo Excel | Columna Sugerida | Acción |
| :--- | :--- | :--- |
| **Ingreso** | `codigo_ingreso` | **NUEVO**: Para guardar el ID del Excel. |
| Fecha Atención | `fecha_realizada` | |
| Tipo de Atención | `tipo_atencion_ext` | **NUEVO**: Clasificación externa. |
| Serv | `servicio_tipo` | **NUEVO**: 'E', 'C', etc. |
| Descripción Diagnóstico| `observaciones` | |
| Remitido A | `remitido_a` | **NUEVO**: Destino de remisión. |

### C. Nueva Tabla: `servicios`
El Excel trae códigos de servicio y descripciones que actualmente no están tipificadas.
```sql
CREATE TABLE servicios (
    id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_servicio VARCHAR(20) UNIQUE,
    nombre_servicio VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
*En `visitas_domiciliarias` se agregaría `id_servicio` como FK.*

### D. Tabla `paciente_diagnosticos`
Usted ya identificó el mapeo, pero sugiero un cambio estructural:
- Actualmente la tabla no está ligada a una visita específica.
- **Sugerencia**: Agregar `id_visita` a esta tabla para saber en qué ingreso se dio dicho diagnóstico.

---

## 3. Plan de Acción (Estructura SQL)

Para soportar todos los datos del Excel, ejecute estas modificaciones:

```sql
-- 1. Agregar campos faltantes a aseguradoras
ALTER TABLE aseguradoras ADD COLUMN numero_contrato VARCHAR(50);

-- 2. Crear tabla de servicios
CREATE TABLE IF NOT EXISTS servicios (
    id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_servicio VARCHAR(50) UNIQUE,
    descripcion TEXT
);

-- 3. Modificar visitas_domiciliarias para recibir datos del Excel
ALTER TABLE visitas_domiciliarias 
ADD COLUMN codigo_ingreso VARCHAR(20) UNIQUE AFTER id_visita,
ADD COLUMN tipo_atencion_ext VARCHAR(100),
ADD COLUMN servicio_tipo VARCHAR(10),
ADD COLUMN remitido_a VARCHAR(255),
ADD COLUMN id_servicio_excel INT UNSIGNED,
ADD CONSTRAINT fk_visita_servicio FOREIGN KEY (id_servicio_excel) REFERENCES servicios(id_servicio);

-- 4. Mejorar paciente_diagnosticos
-- Nota: Si id_paciente y codigo_cie10 son PK, no podrá tener el mismo diagnóstico en dos visitas.
-- Se recomienda agregar id_visita a la PK.
ALTER TABLE paciente_diagnosticos ADD COLUMN id_visita INT UNSIGNED;
```

## 4. Ideas de Implementación (Tips)

1. **Normalización de Nombres**: Los nombres en el Excel como "GALVIS ROPERO MEYBI KARINA" deben buscarse en la tabla `personal` por nombre o crear un proceso de "vincular profesional" si no coinciden exactamente.
2. **Carga Masiva**: Use un script en Laravel (Excel Maatwebsite) para iterar el archivo.
   - Primero crear/actualizar el `paciente`.
   - Luego crear el `servicio` si no existe.
   - Crear la `visita` con el `codigo_ingreso`.
   - Finalmente insertar los 3 diagnósticos relacionados vinculados al `id_visita`.
3. **Mapeo de Sexo**: Asegúrese de validar que el Excel solo traiga 'M' o 'F' para que coincida con el `ENUM` de la tabla `pacientes`.

¿Desea que le ayude a crear un script de migración en Laravel para estos cambios o prefiere los comandos SQL directos?
