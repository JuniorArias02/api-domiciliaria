<?php

namespace Modules\Auth\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class LogAcceso extends Model
{
    protected $table      = 'logs_acceso';
    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'ip_origen',
        'dispositivo',
        'user_agent',
        'created_at',
    ];
}
