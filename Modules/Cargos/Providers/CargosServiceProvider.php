<?php

namespace Modules\Cargos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Cargos\Domain\Contracts\CargosRepositoryInterface;
use Modules\Cargos\Infrastructure\Repositories\CargosRepository;

class CargosServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            CargosRepositoryInterface::class,
            CargosRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
