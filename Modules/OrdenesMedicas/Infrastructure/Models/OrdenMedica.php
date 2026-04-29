<?php

namespace Modules\OrdenesMedicas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Ingresos\Infrastructure\Models\Ingreso;
use Modules\OrdenesServicio\Infrastructure\Models\OrdenServicio;

class OrdenMedica extends Model
{
    protected $table = 'ordenes_medicas';

    protected $primaryKey = 'id_orden';

    protected $fillable = [
        'id_ingreso',
        'creado_por',
        'fecha_orden',
        'observacion',
        'estado',
    ];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'id_ingreso', 'id_ingreso');
    }

    public function servicios()
    {
        return $this->hasMany(OrdenServicio::class, 'id_orden', 'id_orden');
    }
}
