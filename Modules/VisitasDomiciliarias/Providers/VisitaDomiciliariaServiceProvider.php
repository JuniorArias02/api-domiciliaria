<?php

namespace Modules\VisitasDomiciliarias\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\VisitasDomiciliarias\Domain\Contracts\VisitaDomiciliariaRepositoryInterface;
use Modules\VisitasDomiciliarias\Infrastructure\Repositories\VisitaDomiciliariaRepository;

class VisitaDomiciliariaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            VisitaDomiciliariaRepositoryInterface::class,
            VisitaDomiciliariaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
