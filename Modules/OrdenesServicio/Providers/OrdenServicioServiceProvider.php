<?php

namespace Modules\OrdenesServicio\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\OrdenesServicio\Domain\Contracts\OrdenServicioRepositoryInterface;
use Modules\OrdenesServicio\Infrastructure\Repositories\OrdenServicioRepository;

class OrdenServicioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            OrdenServicioRepositoryInterface::class,
            OrdenServicioRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
