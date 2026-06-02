<?php

namespace Modules\VisitasDomiciliarias\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaDomiciliaria extends Model
{
    protected $table = 'visitas_domiciliarias';
    protected $primaryKey = 'id_visita';

    protected $fillable = [
        'id_orden_servicio',
        'id_paciente',
        'id_personal',
        'id_ruta',
        'orden_visita',
        'id_usuario_programa',
        'fecha_programada',
        'fecha_realizada',
        'latitud_checkin',
        'longitud_checkin',
        'latitud_checkout',
        'longitud_checkout',
        'estado',
        'motivo_cancelacion',
        'observaciones',
        'codigo_ingreso',
        'tipo_atencion_ext',
        'remitido_a'
    ];

    public function ordenServicio()
    {
        return $this->belongsTo(\Modules\OrdenesServicio\Infrastructure\Models\OrdenServicio::class, 'id_orden_servicio', 'id_orden_servicio');
    }

    public function paciente(){
        return $this->belongsTo(\Modules\Pacientes\Infrastructure\Models\Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function personal()
    {
        return $this->belongsTo(\Modules\Personal\Infrastructure\Models\Personal::class, 'id_personal', 'id_personal');
    }

    public function usuarioPrograma()
    {
        return $this->belongsTo(\Modules\Auth\Infrastructure\Models\Usuario::class, 'id_usuario_programa', 'id_usuario');
    }

    public function ruta()
    {
        return $this->belongsTo(\Modules\Rutas\Infrastructure\Models\Ruta::class, 'id_ruta', 'id_ruta');
    }
}
