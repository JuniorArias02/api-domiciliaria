<?php

namespace Modules\Pacientes\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Usuarios\Infrastructure\Models\Usuario;

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
        'id_comuna',
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
        return $this->belongsTo(Usuario::class, 'id_madrina', 'id_usuario');
    }

    public function barrio()
    {
        return $this->belongsTo(\Modules\Barrios\Infrastructure\Models\Barrio::class, 'id_barrio', 'id');
    }

    public function comuna()
    {
        return $this->belongsTo(\Modules\Comunas\Infrastructure\Models\Comuna::class, 'id_comuna', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(\Modules\Municipios\Infrastructure\Models\Municipio::class, 'id_municipio', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(\Modules\Departamentos\Infrastructure\Models\Departamento::class, 'id_departamento', 'id');
    }

    public function ingresos()
    {
        return $this->hasMany(\Modules\Ingresos\Infrastructure\Models\Ingreso::class, 'id_paciente', 'id_paciente');
    }
}
