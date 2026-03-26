<?php

namespace Modules\Laboratorios\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';
    protected $primaryKey = 'id_laboratorio';

    protected $fillable = [
        'id_paciente',
        'id_orden_asociada',
        'id_personal_toma',
        'id_usuario_solicita',
        'fecha_solicitud',
        'fecha_toma_programada',
        'fecha_toma_real',
        'estado',
        'confirmacion_toma',
        'observaciones'
    ];
}
