<?php

namespace Modules\Especialidades\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Especialidades\Domain\Contracts\EspecialidadRepositoryInterface;
use Modules\Especialidades\Infrastructure\Repositories\EspecialidadRepository;

class EspecialidadServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            EspecialidadRepositoryInterface::class,
            EspecialidadRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
