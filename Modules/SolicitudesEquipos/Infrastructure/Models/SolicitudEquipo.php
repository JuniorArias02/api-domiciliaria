<?php

namespace Modules\SolicitudesEquipos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudEquipo extends Model
{
    protected $table = 'solicitudes_equipos';
    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'id_paciente',
        'id_usuario_gestiona',
        'modalidad',
        'tiempo_requerido',
        'estado',
        'fecha_solicitud',
        'fecha_entrega',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'observaciones'
    ];
}
