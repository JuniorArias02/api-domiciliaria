<?php

namespace Modules\Barrios\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    protected $table = 'barrios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_municipio',
        'id_comuna',
        'nombre'
    ];

    public function municipio()
    {
        return $this->belongsTo(\Modules\Municipios\Infrastructure\Models\Municipio::class, 'id_municipio', 'id');
    }

    public function comuna()
    {
        return $this->belongsTo(\Modules\Comunas\Infrastructure\Models\Comuna::class, 'id_comuna', 'id');
    }
}
