<?php

namespace Modules\Barrios\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Barrios\Domain\Contracts\BarrioRepositoryInterface;
use Modules\Barrios\Infrastructure\Repositories\BarrioRepository;

class BarrioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            BarrioRepositoryInterface::class,
            BarrioRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
