<?php

namespace Modules\Especialidades\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';

    // Solo tiene created_at, NO updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'abreviatura'
    ];
}
