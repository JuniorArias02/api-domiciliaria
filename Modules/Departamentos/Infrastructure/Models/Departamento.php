<?php

namespace Modules\Departamentos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'id';
    public $timestamps = false; // El usuario solo mencionó created_at

    protected $fillable = [
        'codigo_dane',
        'nombre'
    ];
}
