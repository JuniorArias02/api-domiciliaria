<?php

namespace Modules\Comunas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Comunas\Domain\Contracts\ComunaRepositoryInterface;
use Modules\Comunas\Infrastructure\Repositories\ComunaRepository;

class ComunaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            ComunaRepositoryInterface::class,
            ComunaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
