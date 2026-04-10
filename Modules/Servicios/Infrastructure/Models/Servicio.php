<?php

namespace Modules\Servicios\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'codigo_servicio',
        'nombre_servicio',
        'descripcion'
    ];
}
