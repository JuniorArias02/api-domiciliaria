<?php

namespace Modules\Ingresos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Ingresos\Domain\Contracts\IngresoRepositoryInterface;
use Modules\Ingresos\Infrastructure\Repositories\IngresoRepository;

class IngresoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            IngresoRepositoryInterface::class,
            IngresoRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
