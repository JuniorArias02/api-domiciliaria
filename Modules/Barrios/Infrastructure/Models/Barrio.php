<?php

namespace Modules\Barrios\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    protected $table = 'barrios';
    protected $primaryKey = 'id_barrio';
    
    // La migración temporal solo tiene created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id_comuna',
        'nombre'
    ];
}
