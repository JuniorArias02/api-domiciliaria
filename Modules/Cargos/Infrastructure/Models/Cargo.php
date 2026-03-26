<?php

namespace Modules\Cargos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';

    protected $fillable = [
        'nombre',
        'create_at',
    ];
}
