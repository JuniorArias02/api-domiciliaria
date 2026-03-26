<?php

namespace Modules\Zonas\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';
    protected $primaryKey = 'id_zona';
    
    // La tabla no tiene updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre'
    ];
}
