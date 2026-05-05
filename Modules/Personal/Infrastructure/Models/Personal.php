<?php

namespace Modules\Personal\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';
    protected $primaryKey = 'id_personal';

    protected $fillable = [
        'id_cargo',
        'nombre_completo',
        'numero_documento',
        'tipo_documento',
        'tarjeta_profesional',
        'telefono',
        'email',
        'estado'
    ];
}
