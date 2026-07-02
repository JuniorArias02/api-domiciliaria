<?php

namespace Modules\SabanaClinica\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SabanaClinica\Domain\Contracts\SabanaClinicaRepositoryInterface;
use Modules\SabanaClinica\Infrastructure\Repositories\SabanaClinicaRepository;

class SabanaClinicaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            SabanaClinicaRepositoryInterface::class,
            SabanaClinicaRepository::class
        );
    }

}
