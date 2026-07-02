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
                'com.nombre as comuna',
                's.nombre_servicio as servicios',
                's.nombre_servicio as tipo_servicio',
                'c.nombre as profesional_medico',
                'per.nombre_completo as medico',
                'os.frecuencia_dias as frecuencia_medico',
                'om.observacion as observaciones',
                'p.url_google_maps as ubicacion_google_maps',
                'p.estado'
            ])
            ->leftJoin('usuarios as u', 'p.id_madrina', '=', 'u.id_usuario')
            ->leftJoin('aseguradoras as a', 'p.id_aseguradora', '=', 'a.id_aseguradora')
            ->leftJoin('barrios as b', 'p.id_barrio', '=', 'b.id')
            ->leftJoin('comunas as com', 'p.id_comuna', '=', 'com.id')
            // 1. Último ingreso del paciente
            ->leftJoin('ingresos as i', function($join) {
                $join->on('i.id_paciente', '=', 'p.id_paciente')
                     ->whereRaw('i.id_ingreso = (SELECT MAX(id_ingreso) FROM ingresos WHERE id_paciente = p.id_paciente)');
            })
            // 2. Última orden médica de ese ingreso
            ->leftJoin('ordenes_medicas as om', function($join) {
                $join->on('om.id_ingreso', '=', 'i.id_ingreso')
                     ->whereRaw('om.id_orden = (SELECT MAX(id_orden) FROM ordenes_medicas WHERE id_ingreso = i.id_ingreso)');
            })
            // 3. Última orden de servicio asociada a la orden médica
            ->leftJoin('ordenes_servicios as os', function($join) {
                $join->on('os.id_orden', '=', 'om.id_orden')
                     ->whereRaw("os.id_orden_servicio = (SELECT MAX(os2.id_orden_servicio) FROM ordenes_servicios os2 INNER JOIN servicios s2 ON os2.id_servicio = s2.id_servicio WHERE os2.id_orden = om.id_orden AND s2.codigo_servicio = '890101')");
            })
            // 4. Servicio prestado
            ->leftJoin('servicios as s', 'os.id_servicio', '=', 's.id_servicio')
            // 5. Personal asignado y su cargo
            ->leftJoin('personal as per', 'os.id_profesional_asignado', '=', 'per.id_personal')
            ->leftJoin('cargos as c', 'per.id_cargo', '=', 'c.id_cargo');

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
