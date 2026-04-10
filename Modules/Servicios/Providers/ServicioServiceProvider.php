<?php

namespace Modules\Servicios\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Servicios\Domain\Contracts\ServicioRepositoryInterface;
use Modules\Servicios\Infrastructure\Repositories\ServicioRepository;

class ServicioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            ServicioRepositoryInterface::class,
            ServicioRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
