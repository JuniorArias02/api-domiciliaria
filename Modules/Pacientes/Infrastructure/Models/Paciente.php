<?php

namespace Modules\Pacientes\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';

    protected $fillable = [
        'tipo_documento',
        'identificacion',
        'nombre_completo',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'id_aseguradora',
        'regimen',
        'id_madrina',
        'fecha_ingreso',
        'direccion',
        'id_barrio',
        'latitud',
        'longitud',
        'url_google_maps',
        'estado',
    ];
}
