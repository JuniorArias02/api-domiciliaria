<?php

namespace Modules\Auth\Infrastructure\Services;

use Modules\Auth\Infrastructure\Models\LogAcceso;

/**
 * AccessLogService — Servicio de infraestructura para logs de acceso.
 * El error en el log nunca interrumpe el flujo principal.
 */
class AccessLogService
{
    public function registrar(int $idUsuario, string $accion, array $meta = []): void
    {
        try {
            LogAcceso::create([
                'id_usuario'  => $idUsuario,
                'accion'      => $accion,
                'ip_origen'   => $meta['ip']         ?? null,
                'user_agent'  => $meta['user_agent']  ?? null,
                'dispositivo' => $meta['dispositivo'] ?? null,
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // El log es efecte secundario; no rompe el flujo de autenticación
        }
    }
}
