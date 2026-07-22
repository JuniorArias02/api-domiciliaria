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

            ['field' => 'comuna', 'title' => 'Comuna', 'type' => 'string', 'editable' => false],
            ['field' => 'servicios', 'title' => 'Servicios', 'type' => 'string', 'editable' => false],
            ['field' => 'tipo_servicio', 'title' => 'TIPO DE SERVICIO', 'type' => 'string', 'editable' => false],

            ['field' => 'profesional_medico', 'title' => 'Profesional Medico', 'type' => 'string', 'editable' => false],
            ['field' => 'medico', 'title' => 'Medico', 'type' => 'string', 'editable' => false],
            ['field' => 'frecuencia_medico', 'title' => 'Frecuencia Medico', 'type' => 'number', 'editable' => false],

            ['field' => 'observaciones', 'title' => 'Observacriones', 'type' => 'string', 'editable' => true],
            ['field' => 'ubicacion_google_maps', 'title' => 'Ubicacion Google MAPS', 'type' => 'string', 'editable' => true],
        ];

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        $items = collect($paginacion->items());
        $pacienteIds = $items->pluck('id_paciente')->filter()->toArray();

        // Consulta eficiente (un solo query para toda la página)
        $visitas = [];
        if (!empty($pacienteIds)) {
            $visitas = \Illuminate\Support\Facades\DB::table('visitas_domiciliarias as vd')
                ->join('ordenes_servicios as os', 'vd.id_orden_servicio', '=', 'os.id_orden_servicio')
                ->join('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
                ->whereIn('vd.id_paciente', $pacienteIds)
                ->where('s.codigo_servicio', '890101') // Filtrar explícitamente para Medicina General
                ->whereNotNull('vd.fecha_programada') // o fecha_realizada según cómo lo llenen
                ->select('vd.id_paciente', 'vd.fecha_programada as fecha')
                ->get()
                ->groupBy('id_paciente');
        }

        // Obtener todos los años presentes en las visitas de esta página para armar las columnas
        $aniosPresentes = collect($visitas)->flatten()->map(function($v) {
            return date('Y', strtotime($v->fecha));
        })->unique()->sort()->values()->toArray();

        if (empty($aniosPresentes)) {
            $aniosPresentes = [date('Y')]; // Default al año actual si no hay visitas
        }

        // Construir columnas agrupadas por año
        $columnasAtencion = [];
        foreach ($aniosPresentes as $anio) {
            $mesesDelAnio = [];
            foreach ($meses as $num => $nombre) {
                $mesesDelAnio[] = [
                    'field' => 'atenciones.'.$anio.'.'.strtolower($nombre), 
                    'title' => strtoupper($nombre), 
                    'type' => 'string', 
                    'editable' => false
                ];
            }
            
            $columnas[] = [
                'title' => (string) $anio,
                'isGroup' => true, // Bandera para que el frontend sepa que es un grupo colapsable
                'children' => $mesesDelAnio
            ];
        }

        $rowsMapped = $items->map(function($row) use ($visitas, $meses, $aniosPresentes) {
            $visitasPaciente = isset($visitas[$row->id_paciente]) ? collect($visitas[$row->id_paciente]) : collect();
            
            $atenciones = [];

            foreach ($aniosPresentes as $anio) {
                $atenciones[$anio] = [];
                foreach ($meses as $num => $nombre) {
                    // Buscamos todas las fechas que coincidan con el mes y el año
                    $fechasMes = $visitasPaciente->filter(function($v) use ($num, $anio) {
                        return $v->fecha && (int)date('n', strtotime($v->fecha)) === $num && date('Y', strtotime($v->fecha)) == $anio;
                    })->map(function($v) {
                        return date('d/m/Y', strtotime($v->fecha));
                    })->unique()->implode(', ');
                    
                    $campoMes = strtolower($nombre);
                    $atenciones[$anio][$campoMes] = $fechasMes;
                }
            }
            
            // Asignamos el objeto de atenciones completo a la fila
            $row->atenciones = $atenciones;

            // Limpiamos campos viejos si existían en el objeto original (opcional)
            foreach ($meses as $nombre) {
                $oldField = 'fecha_atencion_'.strtolower($nombre);
                if (isset($row->$oldField)) {
                    unset($row->$oldField);
                }
            }

            return $row;
        });

        return [
            'columns' => $columnas,
            'rows' => $rowsMapped,
            'meta' => [
                'current_page' => $paginacion->currentPage(),
                'last_page' => $paginacion->lastPage(),
                'per_page' => $paginacion->perPage(),
                'total' => $paginacion->total(),
            ]
        ];
    }
}
