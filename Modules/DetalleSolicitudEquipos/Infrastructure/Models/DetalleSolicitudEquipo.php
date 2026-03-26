<?php

namespace Modules\DetalleSolicitudEquipos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleSolicitudEquipo extends Model
{
    protected $table = 'detalle_solicitud_equipos';
    protected $primaryKey = 'id_detalle';

    // No tiene generated_at ni updated_at
    public $timestamps = false;

    protected $fillable = [
        'id_solicitud',
        'id_equipo',
        'cantidad',
        'observacion'
    ];
}
