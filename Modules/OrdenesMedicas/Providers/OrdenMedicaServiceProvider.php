<?php

namespace Modules\OrdenesMedicas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\OrdenesMedicas\Domain\Contracts\OrdenMedicaRepositoryInterface;
use Modules\OrdenesMedicas\Infrastructure\Repositories\OrdenMedicaRepository;

class OrdenMedicaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            OrdenMedicaRepositoryInterface::class,
            OrdenMedicaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
