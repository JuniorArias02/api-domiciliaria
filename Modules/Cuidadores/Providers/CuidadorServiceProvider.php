<?php

namespace Modules\Cuidadores\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Cuidadores\Domain\Contracts\CuidadorRepositoryInterface;
use Modules\Cuidadores\Infrastructure\Repositories\CuidadorRepository;

class CuidadorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            CuidadorRepositoryInterface::class,
            CuidadorRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
