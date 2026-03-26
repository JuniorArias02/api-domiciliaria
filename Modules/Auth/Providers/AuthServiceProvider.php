<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Auth\Infrastructure\Repositories\EloquentUsuarioRepository;

/**
 * AuthServiceProvider — Registro de bindings del módulo Auth.
 *
 * Conecta las interfaces (contratos) con sus implementaciones concretas.
 * Principio: Inversión de Dependencias (D de SOLID).
 */
class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Contrato → Implementación
        $this->app->bind(
            UsuarioRepositoryInterface::class,
            EloquentUsuarioRepository::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
