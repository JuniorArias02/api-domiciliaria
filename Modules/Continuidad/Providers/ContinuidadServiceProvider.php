<?php

namespace Modules\Continuidad\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Continuidad\Domain\Contracts\ContinuidadRepositoryInterface;
use Modules\Continuidad\Infrastructure\Repositories\ContinuidadRepository;

class ContinuidadServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            ContinuidadRepositoryInterface::class,
            ContinuidadRepository::class
        );
    }
}
