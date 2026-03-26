<?php

namespace Modules\Tutelas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Tutela extends Model
{
    protected $table = 'tutelas';
    protected $primaryKey = 'id_tutela';

    protected $fillable = [
        'id_paciente',
        'numero_tutela',
        'fecha_tutela',
        'prestacion_autorizada',
        'es_permanente',
        'duracion_dias',
        'observaciones'
    ];
}
