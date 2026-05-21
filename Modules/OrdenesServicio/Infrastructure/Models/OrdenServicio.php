<?php

namespace Modules\OrdenesServicio\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenServicio extends Model
{
    protected $table = 'ordenes_servicios';
    protected $primaryKey = 'id_orden_servicio';

    protected $fillable = [
        'id_orden',
        'id_servicio',
        'id_profesional_asignado',
        'numero_sesiones',
        'frecuencia_dias',
        'fecha_inicio',
        'estado'
    ];

    protected $casts = [
        'id_orden' => 'integer',
        'id_servicio' => 'integer',
        'id_profesional_asignado' => 'integer',
        'numero_sesiones' => 'integer',
        'frecuencia_dias' => 'integer',
        'fecha_inicio' => 'datetime'
    ];

    public function servicio()
    {
        return $this->belongsTo(\Modules\Servicios\Infrastructure\Models\Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function profesional()
    {
        return $this->belongsTo(\Modules\Personal\Infrastructure\Models\Personal::class, 'id_profesional_asignado', 'id_personal');
    }

    public function visitas()
    {
        return $this->hasMany(\Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria::class, 'id_orden_servicio', 'id_orden_servicio');
    }
}
