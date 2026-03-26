<?php

namespace Modules\Telexperticias\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Telexperticia extends Model
{
    protected $table = 'telexperticias';
    protected $primaryKey = 'id_telexperticia';

    protected $fillable = [
        'id_paciente',
        'id_especialidad',
        'id_usuario_solicita',
        'fecha_solicitud',
        'frecuencia_dias',
        'estado',
        'observaciones'
    ];
}
