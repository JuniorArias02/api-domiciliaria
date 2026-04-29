<?php

namespace Modules\Departamentos\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Departamentos\Domain\Contracts\DepartamentoRepositoryInterface;
use Modules\Departamentos\Infrastructure\Repositories\DepartamentoRepository;

class DepartamentoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            DepartamentoRepositoryInterface::class,
            DepartamentoRepository::class
        );
    }
}
