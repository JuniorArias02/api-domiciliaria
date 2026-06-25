<?php

namespace Modules\OrdenesServicio\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Personal\Infrastructure\Models\Personal;
use Modules\Servicios\Infrastructure\Models\Servicio;
use Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria;

class OrdenServicio extends Model
{
    protected $table = 'ordenes_servicios';

    protected $primaryKey = 'id_orden_servicio';

    protected $fillable = [
        'id_orden',
        'id_orden_servicio_anterior',
        'id_servicio',
        'id_profesional_asignado',
        'numero_sesiones',
        'frecuencia_dias',
        'fecha_inicio',
        'estado',
    ];

    protected $casts = [
        'id_orden' => 'integer',
        'id_orden_servicio_anterior' => 'integer',
        'id_servicio' => 'integer',
        'id_profesional_asignado' => 'integer',
        'numero_sesiones' => 'integer',
        'frecuencia_dias' => 'integer',
        'fecha_inicio' => 'datetime',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function profesional()
    {
        return $this->belongsTo(Personal::class, 'id_profesional_asignado', 'id_personal');
    }

    public function visitas()
    {
        return $this->hasMany(VisitaDomiciliaria::class, 'id_orden_servicio', 'id_orden_servicio');
    }

    public function servicioAnterior()
    {
        return $this->belongsTo(OrdenServicio::class, 'id_orden_servicio_anterior', 'id_orden_servicio');
    }
}
