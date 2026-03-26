<?php

namespace Modules\Auth\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Modelo Eloquent — Infraestructura.
 * No acceder desde fuera de esta capa; usar UsuarioEntity en su lugar.
 */
class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_rol',
        'nombre_completo',
        'email',
        'password_hash',
        'estado',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'estado'        => 'integer',
        ];
    }

    /** Compatibilidad con Laravel Auth — apunta a password_hash */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // JWT Subject
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'id_usuario'      => $this->id_usuario,
            'email'           => $this->email,
            'nombre_completo' => $this->nombre_completo,
            'id_rol'          => $this->id_rol,
            'estado'          => $this->estado,
        ];
    }

    // Relaciones
    public function rol(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function logsAcceso(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LogAcceso::class, 'id_usuario', 'id_usuario');
    }
}
