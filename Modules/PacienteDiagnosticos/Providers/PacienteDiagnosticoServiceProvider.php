<?php

namespace Modules\PacienteDiagnosticos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\PacienteDiagnosticos\Domain\Contracts\PacienteDiagnosticoRepositoryInterface;
use Modules\PacienteDiagnosticos\Infrastructure\Repositories\PacienteDiagnosticoRepository;

class PacienteDiagnosticoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            PacienteDiagnosticoRepositoryInterface::class,
            PacienteDiagnosticoRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
