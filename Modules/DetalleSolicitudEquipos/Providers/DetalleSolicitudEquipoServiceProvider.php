<?php

namespace Modules\DetalleSolicitudEquipos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DetalleSolicitudEquipos\Domain\Contracts\DetalleSolicitudEquipoRepositoryInterface;
use Modules\DetalleSolicitudEquipos\Infrastructure\Repositories\DetalleSolicitudEquipoRepository;

class DetalleSolicitudEquipoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            DetalleSolicitudEquipoRepositoryInterface::class,
            DetalleSolicitudEquipoRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
