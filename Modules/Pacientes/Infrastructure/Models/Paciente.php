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
        'id_departamento',
        'id_municipio',
        'orden_mapa',
        'latitud',
        'longitud',
        'url_google_maps',
        'estado',
    ];

    public function aseguradora()
    {
        return $this->belongsTo(\Modules\Aseguradoras\Infrastructure\Models\Aseguradora::class, 'id_aseguradora', 'id_aseguradora');
    }

    public function madrina()
    {
        return $this->belongsTo(\Modules\Usuarios\Infrastructure\Models\Usuario::class, 'id_madrina', 'id_usuario');
    }

}
