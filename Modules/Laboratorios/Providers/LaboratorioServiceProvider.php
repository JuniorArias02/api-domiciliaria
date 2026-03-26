<?php

namespace Modules\Laboratorios\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Laboratorios\Domain\Contracts\LaboratorioRepositoryInterface;
use Modules\Laboratorios\Infrastructure\Repositories\LaboratorioRepository;

class LaboratorioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            LaboratorioRepositoryInterface::class,
            LaboratorioRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
