<?php

namespace Modules\Ingresos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';
    protected $primaryKey = 'id_ingreso';

    const UPDATED_AT = 'update_at';

    protected $fillable = [
        'ingreso',
        'id_paciente',
        'autorizacion',
        'fecha_ingreso'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'ingreso' => 'integer',
        'id_paciente' => 'integer'
    ];
}
