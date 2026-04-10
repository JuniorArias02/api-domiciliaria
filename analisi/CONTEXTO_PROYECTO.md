# Contexto Técnico del Proyecto: Sistema de Visitas Domiciliarias

Este documento contiene toda la información recolectada y analizada sobre la base de datos `app_domiciliaria` y la integración del archivo de consultas médicas. Debe ser consultado antes de realizar cualquier cambio estructural en el sistema.

---

## 1. Configuración de Entorno
- **Base de Datos**: `app_domiciliaria`
- **Usuario**: `root`
- **Password**: `root`
- **Host**: `localhost`

---

## 2. Análisis de la Base de Datos Existente (`app_domiciliaria`)

Se identificaron las siguientes tablas clave y su propósito:

| Tabla | Propósito |
| :--- | :--- |
| `pacientes` | Almacena datos demográficos (Documento, nombre, dirección, etc.). |
| `visitas_domiciliarias` | Registro de las visitas de profesionales a hogares. |
| `paciente_diagnosticos` | Tabla de relación que asocia códigos CIE10 a los pacientes. |
| `aseguradoras` | Entidades de salud externas a las que pertenecen los pacientes. |
| `personal` | Profesionales de la salud (médicos, enfermeros, etc.). |
| `diagnosticos_cie10` | Catálogo maestro de códigos internacionales de enfermedades. |

### Detalles de Estructura Relevantes:
- **paciente_diagnosticos**: Tiene una llave primaria compuesta por `(id_paciente, codigo_cie10, tipo_diagnostico)`. **Limitación**: No permite repetir el mismo diagnóstico en visitas diferentes si se mantiene este tipo.
- **visitas_domiciliarias**: Usa `id_visita` como autoincremental, pero no tiene un campo para el "ID de ingreso" externo que traen los archivos de facturación/consultas.

---

## 3. Análisis del Archivo Excel (`consulta enero a marzo.xlsx`)

El archivo contiene el historial de consultas externas/domiciliarias con 51 columnas. Los datos más críticos para la operación son:

- **Ingreso**: El ID único de la transacción (ej: "152198"). **Mapear a: `visitas_domiciliarias.codigo_ingreso`**.
- **Beneficiario**: Documento del paciente. **Clave de búsqueda en: `pacientes.identificacion`**.
- **Servicio/Descripción**: Tipificación del acto médico. **Se propone crear tabla `servicios`**.
- **Diagnósticos (Dx Rel. 1, 2, 3)**: Códigos CIE10 diagnósticos. **Mapear a: `paciente_diagnosticos`**.
- **Entidad/Contrato**: Datos de la EPS. **Mapear a: `aseguradoras` (requiere nuevos campos)**.

---

## 4. Plan de Implementación (Propuesta Técnica)

### Modificaciones en Base de Datos (SQL):

```sql
-- 1. Soporte para Contratos en Aseguradoras
ALTER TABLE aseguradoras ADD COLUMN numero_contrato VARCHAR(50);

-- 2. Catálogo de Servicios
CREATE TABLE servicios (
    id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_servicio VARCHAR(50) UNIQUE,
    descripcion TEXT
);

-- 3. Extensión de Visitas Domiciliarias
ALTER TABLE visitas_domiciliarias 
ADD COLUMN codigo_ingreso VARCHAR(20) UNIQUE, -- ID del Excel (Ingreso)
ADD COLUMN tipo_atencion_ext VARCHAR(100),    -- 'Consulta Médica', etc.
ADD COLUMN servicio_tipo VARCHAR(10),        -- 'E', 'C', etc.
ADD COLUMN remitido_a VARCHAR(255),          -- Destino de remisión
ADD COLUMN id_servicio_excel INT UNSIGNED;    -- FK a tabla servicios

-- 4. Mejora de Diagnósticos
ALTER TABLE paciente_diagnosticos ADD COLUMN id_visita INT UNSIGNED;
```

### Lógica de Importación de Datos (Pseudocódigo):
1. **Buscar/Crear Paciente**: Si `Beneficiario` no existe en `pacientes.identificacion`, crearlo con `NombreBen`, `Sexo`, `Fecha Nac.`, etc.
2. **Buscar/Crear Servicio**: Si el código de `Servicio` no existe en la tabla `servicios`, insertarlo.
3. **Buscar Profesional**: Validar `Nombre Profesional` contra `personal.nombre_completo`.
4. **Registrar Visita**: Insertar en `visitas_domiciliarias` usando los datos mapeados anteriores y guardando el número de `Ingreso` para evitar duplicados.
5. **Registrar Diagnósticos**: Para cada `Dx Rel. X` que no sea nulo:
   - Insertar en `paciente_diagnosticos` vinculando al `id_paciente` y al `id_visita` recién creado.

---

## 5. Próximos pasos
1. Crear migraciones en Laravel para los cambios de SQL.
2. Desarrollar el script de importación masiva (usando Maatwebsite Excel o comandos nativos de PHP).
3. Validar la consistencia de códigos CIE10 entre el Excel y la tabla `diagnosticos_cie10`.

**Documento generado por Antigravity AI.**
