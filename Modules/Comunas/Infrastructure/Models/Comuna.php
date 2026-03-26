<?php

namespace Modules\Comunas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    protected $table = 'comunas';
    protected $primaryKey = 'id_comuna';
    
    // La migración no tiene updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id_zona',
        'nombre'
    ];
}
