<?php

namespace Modules\VisitasDomiciliarias\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaDomiciliaria extends Model
{
    protected $table = 'visitas_domiciliarias';
    protected $primaryKey = 'id_visita';

    protected $fillable = [
        'id_orden_asociada',
        'id_paciente',
        'id_personal',
        'id_especialidad',
        'id_usuario_programa',
        'fecha_programada',
        'fecha_realizada',
        'latitud_checkin',
        'longitud_checkin',
        'latitud_checkout',
        'longitud_checkout',
        'estado',
        'motivo_cancelacion',
        'observaciones',
        'codigo_ingreso',
        'tipo_atencion_ext',
        'servicio_tipo',
        'remitido_a',
        'id_servicio'
    ];
}
