<?php

namespace Modules\Pacientes\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pacientes\Domain\Contracts\PacienteRepositoryInterface;
use Modules\Pacientes\Infrastructure\Repositories\PacienteRepository;

class PacienteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            PacienteRepositoryInterface::class,
            PacienteRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
