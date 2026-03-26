<?php

namespace Modules\Aseguradoras\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Aseguradora extends Model
{
    protected $table = 'aseguradoras';
    protected $primaryKey = 'id_aseguradora';
    
    // La migración no tiene updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'codigo_habilitacion',
        'activa'
    ];
}
