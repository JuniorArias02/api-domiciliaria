<?php

namespace Modules\Rutas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';

    protected $fillable = [
        'id_personal',
        'fecha_ruta',
        'estado'
    ];

    public function personal()
    {
        return $this->belongsTo(\Modules\Personal\Infrastructure\Models\Personal::class, 'id_personal', 'id_personal');
    }

    public function visitas()
    {
        return $this->hasMany(\Modules\VisitasDomiciliarias\Infrastructure\Models\VisitaDomiciliaria::class, 'id_ruta', 'id_ruta')
                    ->orderBy('orden_visita', 'asc');
    }
}
