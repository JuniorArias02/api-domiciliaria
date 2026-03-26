<?php

namespace Modules\Personal\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Personal\Domain\Contracts\PersonalRepositoryInterface;
use Modules\Personal\Infrastructure\Repositories\PersonalRepository;

class PersonalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            PersonalRepositoryInterface::class,
            PersonalRepository::class
        );
    }

    public function boot()
    {
        //
    }
}
