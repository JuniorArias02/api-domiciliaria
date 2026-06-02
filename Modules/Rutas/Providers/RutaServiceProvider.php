<?php

namespace Modules\Rutas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Rutas\Domain\Contracts\RutaRepositoryInterface;
use Modules\Rutas\Infrastructure\Repositories\RutaRepository;

class RutaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            RutaRepositoryInterface::class,
            RutaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
