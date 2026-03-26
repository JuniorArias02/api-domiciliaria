<?php

namespace Modules\Zonas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Zonas\Domain\Contracts\ZonaRepositoryInterface;
use Modules\Zonas\Infrastructure\Repositories\ZonaRepository;

class ZonaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            ZonaRepositoryInterface::class,
            ZonaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
