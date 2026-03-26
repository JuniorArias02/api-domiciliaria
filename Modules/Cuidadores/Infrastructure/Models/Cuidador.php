<?php

namespace Modules\Cuidadores\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Cuidador extends Model
{
    protected $table = 'cuidadores';
    protected $primaryKey = 'id_cuidador';

    protected $fillable = [
        'id_paciente',
        'nombre_completo',
        'parentesco',
        'telefono',
        'email',
        'es_principal',
        'tipo_auxiliar',
        'horas_diarias'
    ];
}
