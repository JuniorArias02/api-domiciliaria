<?php

namespace Modules\Municipios\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Municipios\Domain\Contracts\MunicipioRepositoryInterface;
use Modules\Municipios\Infrastructure\Repositories\MunicipioRepository;

class MunicipioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            MunicipioRepositoryInterface::class,
            MunicipioRepository::class
        );
    }
}
