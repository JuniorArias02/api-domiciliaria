<?php

namespace Modules\Telexperticias\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Telexperticias\Domain\Contracts\TelexperticiaRepositoryInterface;
use Modules\Telexperticias\Infrastructure\Repositories\TelexperticiaRepository;

class TelexperticiaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            TelexperticiaRepositoryInterface::class,
            TelexperticiaRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
