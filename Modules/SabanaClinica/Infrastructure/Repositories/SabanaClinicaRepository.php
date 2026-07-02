<?php

namespace Modules\SabanaClinica\Infrastructure\Repositories;

use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class SabanaClinicaRepository implements SabanaClinicaRepositoryInterface
{
    public function obtenerGridPaginado(array $filtros, int $perPage)
    {
        $query = DB::table('pacientes as p')
            ->select([
                'p.id_paciente',
                'u.nombre_completo as asesor', // Madrina
                'a.nombre as aseguradora',
                'p.regimen',
                'p.fecha_ingreso',
                DB::raw('NULL as numero_tutela'),
                DB::raw('NULL as servicio_tutela_autorizado'),
                DB::raw('NULL as fecha_tutela'),
                DB::raw('NULL as duracion_tutela'),
                DB::raw('NULL as aux_enfer'),
                DB::raw('NULL as auxiliar'),
                DB::raw('NULL as nombre_familiar_cuidador'),
                'p.tipo_documento',
                'p.identificacion',
                'p.fecha_nacimiento',
                DB::raw('TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad'),
                'p.sexo',
                'p.nombre_completo as nombre_y_apellido',
                'p.direccion',
                'b.nombre as barrio',
                'p.telefono',
                'p.email as correo',
                DB::raw('NULL as zona_comuna'),
                'com.nombre as comuna',
                DB::raw('NULL as servicios'),
                DB::raw('NULL as tipo_servicio'),
                DB::raw('NULL as remitido'), // Join complex as noted in rules
                DB::raw('NULL as profesional_medico'),
                DB::raw('NULL as medico'),
                DB::raw('NULL as frecuencia_medico'),
                DB::raw('NULL as barthel'),
                DB::raw('NULL as observaciones'),
                'p.url_google_maps as ubicacion_google_maps',
                'p.estado'
            ])
            ->leftJoin('usuarios as u', 'p.id_madrina', '=', 'u.id_usuario')
            ->leftJoin('aseguradoras as a', 'p.id_aseguradora', '=', 'a.id_aseguradora')
            ->leftJoin('barrios as b', 'p.id_barrio', '=', 'b.id')
            ->leftJoin('comunas as com', 'p.id_comuna', '=', 'com.id');

        // Apply dynamic filters if needed
        if (!empty($filtros['search'])) {
            $query->where(function($q) use ($filtros) {
                $q->where('p.nombre_completo', 'LIKE', '%' . $filtros['search'] . '%')
                  ->orWhere('p.identificacion', 'LIKE', '%' . $filtros['search'] . '%');
            });
        }
        
        if (!empty($filtros['estado'])) {
            $query->where('p.estado', $filtros['estado']);
        }

        // Add order by (default id_paciente desc)
        $query->orderBy('p.id_paciente', 'DESC');

        return $query->paginate($perPage);
    }

    public function obtenerRegistroPorId(int $idPaciente)
    {
        return $this->obtenerGridPaginado(['id_paciente' => $idPaciente], 1)->first();
    }

    public function crearRegistro(array $data)
    {
        return DB::transaction(function () use ($data) {
            $id = DB::table('pacientes')->insertGetId([
                'tipo_documento' => $data['tipo_documento'] ?? 'CC',
                'identificacion' => $data['identificacion'],
                'nombre_completo' => $data['nombre_y_apellido'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? Carbon::now()->subYears(30)->toDateString(),
                'sexo' => $data['sexo'] ?? 'N',
                'id_aseguradora' => $data['id_aseguradora'] ?? 1, // Fallback ID
                'direccion' => $data['direccion'] ?? 'Sin direccion',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return $id;
        });
    }

    public function actualizarCampo(int $idPaciente, string $campo, $valor)
    {
        // Mapa de qué campo pertenece a qué tabla
        $mapaCampos = [
            'identificacion' => ['tabla' => 'pacientes', 'columna' => 'identificacion', 'clave' => 'id_paciente'],
            'nombre_y_apellido' => ['tabla' => 'pacientes', 'columna' => 'nombre_completo', 'clave' => 'id_paciente'],
            'direccion' => ['tabla' => 'pacientes', 'columna' => 'direccion', 'clave' => 'id_paciente'],
            'telefono' => ['tabla' => 'pacientes', 'columna' => 'telefono', 'clave' => 'id_paciente'],
            'correo' => ['tabla' => 'pacientes', 'columna' => 'email', 'clave' => 'id_paciente'],
            'fecha_ingreso' => ['tabla' => 'pacientes', 'columna' => 'fecha_ingreso', 'clave' => 'id_paciente'],
            'regimen' => ['tabla' => 'pacientes', 'columna' => 'regimen', 'clave' => 'id_paciente'],
            'ubicacion_google_maps' => ['tabla' => 'pacientes', 'columna' => 'url_google_maps', 'clave' => 'id_paciente'],
        ];

        if (array_key_exists($campo, $mapaCampos)) {
            $info = $mapaCampos[$campo];
            DB::table($info['tabla'])
                ->where($info['clave'], $idPaciente)
                ->update([
                    $info['columna'] => $valor,
                    'updated_at' => Carbon::now()
                ]);
            return true;
        }

        // Si es una observación de orden médica
        if ($campo === 'observaciones') {
            $ultimaOrden = DB::table('ordenes_medicas')
                ->where('id_paciente', $idPaciente)
                ->orderBy('id_orden', 'desc')
                ->first();
            
            if ($ultimaOrden) {
                DB::table('ordenes_medicas')
                    ->where('id_orden', $ultimaOrden->id_orden)
                    ->update(['observacion' => $valor, 'updated_at' => Carbon::now()]);
                return true;
            }
        }
        
        throw new Exception("El campo '{$campo}' no es editable o no está mapeado para edición.");
    }

    public function eliminarRegistro(int $idPaciente)
    {
        // Por seguridad, un borrado lógico en un sistema clínico es más recomendado.
        return DB::table('pacientes')
            ->where('id_paciente', $idPaciente)
            ->update(['estado' => 'INACTIVO', 'updated_at' => Carbon::now()]);
    }
}
