<?php

namespace Modules\Municipios\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_departamento',
        'codigo_dane',
        'nombre'
    ];

    public function departamento()
    {
        return $this->belongsTo(\Modules\Departamentos\Infrastructure\Models\Departamento::class, 'id_departamento', 'id');
    }
}
