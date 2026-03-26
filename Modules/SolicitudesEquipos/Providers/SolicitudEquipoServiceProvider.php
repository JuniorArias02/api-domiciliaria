<?php

namespace Modules\SolicitudesEquipos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SolicitudesEquipos\Domain\Contracts\SolicitudEquipoRepositoryInterface;
use Modules\SolicitudesEquipos\Infrastructure\Repositories\SolicitudEquipoRepository;

class SolicitudEquipoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            SolicitudEquipoRepositoryInterface::class,
            SolicitudEquipoRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
