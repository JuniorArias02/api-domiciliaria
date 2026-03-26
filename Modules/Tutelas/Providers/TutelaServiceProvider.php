<?php

namespace Modules\Tutelas\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tutelas\Domain\Contracts\TutelaRepositoryInterface;
use Modules\Tutelas\Infrastructure\Repositories\TutelaRepository;

class TutelaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            TutelaRepositoryInterface::class,
            TutelaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
