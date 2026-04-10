bd:app_domiciliaria
user:root
pass:root


quiero que me revise la base de datos y me digas sI hace falta crear una tabla nueva O COMO PODRIAsmo integrar los estos datos en la base que tenemos son estos 
Ingreso
Servicio
Descripción del Servicio

Entidad
Número Contrato
Serv
Tipo de Atención
Fecha Atención
Descripción Diagnóstico

Beneficiario
NombreBen
Direccion
Telefono
Fecha Nac.
Sexo
Nombre Profesional
Remitido A


Dx Rel. 1
Diagnóstico Relacionado 1
Dx Rel. 2	
Diagnóstico Relacionado 2
Dx Rel. 3	
Diagnóstico Relacionado 3


esos son los uncios datos relevantes.  
PARTE 1: los datos 
Dx Rel. 1
Diagnóstico Relacionado 1
Dx Rel. 2	
Diagnóstico Relacionado 2
Dx Rel. 3	
Diagnóstico Relacionado 3
van  directo ala tabla 

BASE DE DATOS
TABLA: paciente_diagnosticos
id_paciente
codigo_cie10
tipo_diagnostico
es_principal
fecha_registro
observación


EXCEL

Beneficiario 37244441 => id_paciente

Dx Rel. 1 => codigo_cie10
Diagnóstico Relacionado 1 => tipo_diagnostico

Dx Rel. 2 => codigo_cie10	
Diagnóstico Relacionado 2  => tipo_diagnostico

Dx Rel. 3 => codigo_cie10	
Diagnóstico Relacionado 3  => tipo_diagnostico

LOS DEMAS como podriamos implementarlo dame ideas porfavor,  quiero que me hgas un analisi al excel que es consulta enero a marzo.xlsx

