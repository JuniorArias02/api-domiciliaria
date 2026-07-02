<?php

namespace Modules\SabanaClinica\Application\UseCases;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;

class ObtenerSabanaUseCase
{
    private $repository;

    public function __construct(SabanaClinicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filtros, int $perPage = 50)
    {
        $paginacion = $this->repository->obtenerGridPaginado($filtros, $perPage);
        
        $columnas = [
            ['field' => 'asesor', 'title' => 'ASESOR', 'type' => 'string', 'editable' => false],
            ['field' => 'aseguradora', 'title' => 'Aseguradora', 'type' => 'string', 'editable' => false],
            ['field' => 'regimen', 'title' => 'Regimen', 'type' => 'string', 'editable' => true],
            ['field' => 'fecha_ingreso', 'title' => 'Fecha de Ingreso', 'type' => 'date', 'editable' => true],
            ['field' => 'numero_tutela', 'title' => '# Tutela', 'type' => 'string', 'editable' => false],
            ['field' => 'servicio_tutela_autorizado', 'title' => 'Servicio Tutela - Autorizado', 'type' => 'string', 'editable' => false],
            ['field' => 'fecha_tutela', 'title' => 'Fecha Tutela', 'type' => 'date', 'editable' => false],
            ['field' => 'duracion_tutela', 'title' => 'Duracion Tutela', 'type' => 'string', 'editable' => false],
            ['field' => 'aux_enfer', 'title' => 'Aux_ Enfer', 'type' => 'string', 'editable' => false],
            ['field' => 'auxiliar', 'title' => 'Auxiliar', 'type' => 'string', 'editable' => false],
            ['field' => 'nombre_familiar_cuidador', 'title' => 'Nombre del Familiar Cuidador', 'type' => 'string', 'editable' => false],
            ['field' => 'tipo_documento', 'title' => 'Tipo de Documento', 'type' => 'string', 'editable' => false],
            ['field' => 'identificacion', 'title' => 'IDENTIFICACION', 'type' => 'string', 'editable' => true],
            ['field' => 'fecha_nacimiento', 'title' => 'FECHA DE NACIMIENTO', 'type' => 'date', 'editable' => false],
            ['field' => 'edad', 'title' => 'EDAD', 'type' => 'number', 'editable' => false], // Calculado
            ['field' => 'sexo', 'title' => 'Sexo', 'type' => 'string', 'editable' => false],
            ['field' => 'nombre_y_apellido', 'title' => 'Nombre y Apellido', 'type' => 'string', 'editable' => true],
            ['field' => 'direccion', 'title' => 'Direccion', 'type' => 'string', 'editable' => true],
            ['field' => 'barrio', 'title' => 'Barrrio', 'type' => 'string', 'editable' => false],
            ['field' => 'telefono', 'title' => 'Teléfono', 'type' => 'string', 'editable' => true],
            ['field' => 'correo', 'title' => 'Correo', 'type' => 'string', 'editable' => true],
            ['field' => 'zona_comuna', 'title' => 'ZONA * COMUNA', 'type' => 'string', 'editable' => false],
            ['field' => 'comuna', 'title' => 'Comuna', 'type' => 'string', 'editable' => false],
            ['field' => 'servicios', 'title' => 'Servicios', 'type' => 'string', 'editable' => false],
            ['field' => 'tipo_servicio', 'title' => 'TIPO DE SERVICIO', 'type' => 'string', 'editable' => false],
            ['field' => 'remitido', 'title' => 'Remitido', 'type' => 'string', 'editable' => false],
            ['field' => 'profesional_medico', 'title' => 'Profesional Medico', 'type' => 'string', 'editable' => false],
            ['field' => 'medico', 'title' => 'Medico', 'type' => 'string', 'editable' => false],
            ['field' => 'frecuencia_medico', 'title' => 'Frecuencia Medico', 'type' => 'number', 'editable' => false],
            ['field' => 'barthel', 'title' => 'Barthel', 'type' => 'string', 'editable' => false],
            ['field' => 'observaciones', 'title' => 'Observacriones', 'type' => 'string', 'editable' => true],
            ['field' => 'ubicacion_google_maps', 'title' => 'Ubicacion Google MAPS', 'type' => 'string', 'editable' => true],
        ];

        return [
            'columns' => $columnas,
            'rows' => $paginacion->items(),
            'meta' => [
                'current_page' => $paginacion->currentPage(),
                'last_page' => $paginacion->lastPage(),
                'per_page' => $paginacion->perPage(),
                'total' => $paginacion->total(),
            ]
        ];
    }
}
