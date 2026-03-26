<?php

namespace Modules\PacienteDiagnosticos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class PacienteDiagnostico extends Model
{
    protected $table = 'paciente_diagnosticos';
    
    // Tabla con llave primaria compuesta
    public $incrementing = false;
    protected $primaryKey = null;

    // La migración no tiene timestamps() (created_at, updated_at)
    public $timestamps = false;

    protected $fillable = [
        'id_paciente',
        'codigo_cie10',
        'tipo_diagnostico',
        'es_principal',
        'fecha_registro',
        'observacion'
    ];
}
