<?php

namespace Modules\RegistroPrograma\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\RegistroPrograma\Domain\Contracts\RegistroProgramaRepositoryInterface;
use Modules\RegistroPrograma\Infrastructure\Repositories\RegistroProgramaRepository;

class RegistroProgramaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            RegistroProgramaRepositoryInterface::class,
            RegistroProgramaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
