<?php

namespace Modules\Aseguradoras\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Aseguradoras\Domain\Contracts\AseguradoraRepositoryInterface;
use Modules\Aseguradoras\Infrastructure\Repositories\AseguradoraRepository;

class AseguradoraServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            AseguradoraRepositoryInterface::class,
            AseguradoraRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
