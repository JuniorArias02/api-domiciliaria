<?php

namespace Modules\OrdenesMedicas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenMedica extends Model
{
    protected $table = 'ordenes_medicas';
    protected $primaryKey = 'id_orden';

    protected $fillable = [
        'id_paciente',
        'id_especialidad',
        'id_personal_ordena',
        'fecha_orden',
        'numero_sesiones',
        'frecuencia_dias',
        'numero_mipres',
        'observacion',
        'estado'
    ];
}
