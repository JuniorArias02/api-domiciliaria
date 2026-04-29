<?php

namespace Modules\Comunas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    protected $table = 'comunas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_municipio',
        'nombre'
    ];

    public function municipio()
    {
        return $this->belongsTo(\Modules\Municipios\Infrastructure\Models\Municipio::class, 'id_municipio', 'id');
    }
}
