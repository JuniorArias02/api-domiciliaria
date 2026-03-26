<?php

namespace Modules\Usuarios\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use Modules\Usuarios\Infrastructure\Repositories\EloquentUsuarioRepository;

class UsuariosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
